<?php


namespace Enhavo\Bundle\NewsletterBundle\Newsletter;

use Doctrine\ORM\EntityManagerInterface;
use Enhavo\Bundle\AppBundle\Mailer\MailerManager;
use Enhavo\Bundle\AppBundle\Mailer\Message;
use Enhavo\Bundle\AppBundle\Template\TemplateManager;
use Enhavo\Bundle\AppBundle\Util\TokenGeneratorInterface;
use Enhavo\Bundle\MediaBundle\Model\FileInterface;
use Enhavo\Bundle\NewsletterBundle\Entity\Receiver;
use Enhavo\Bundle\NewsletterBundle\Exception\SendException;
use Enhavo\Bundle\NewsletterBundle\Model\NewsletterInterface;
use Enhavo\Bundle\NewsletterBundle\Subscription\SubscriptionManager;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * NewsletterManager.php
 *
 * @since 05/07/16
 * @author gseidel
 */
class NewsletterManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MailerManager
     */
    private $mailerManager;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var Environment */
    private $twig;

    /** @var TemplateManager */
    private $templateManager;

    /** @var ParameterParserInterface */
    private $parameterParser;

    /** @var string */
    private $from;

    /** @var array */
    private $testReceiver;

    /** @var array */
    private $templates;

    /**
     * NewsletterManager constructor.
     * @param EntityManagerInterface $em
     * @param MailerManager $mailer
     * @param SubscriptionManager $subscriptionManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param LoggerInterface $logger
     * @param Environment $twig
     * @param string $from
     * @param array $testReceiver
     * @param array $templates
     */
    public function __construct(EntityManagerInterface $em,
                                MailerManager $mailer,
                                SubscriptionManager $subscriptionManager,
                                TokenGeneratorInterface $tokenGenerator,
                                LoggerInterface $logger,
                                Environment $twig,
                                TemplateManager $templateManager,
                                ParameterParser $parameterParser,
                                string $from,
                                array $testReceiver,
                                array $templates)
    {
        $this->em = $em;
        $this->mailerManager = $mailer;
        $this->subscriptionManager = $subscriptionManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->logger = $logger;
        $this->templateManager = $templateManager;
        $this->parameterParser = $parameterParser;
        $this->twig = $twig;
        $this->from = $from;
        $this->testReceiver = $testReceiver;
        $this->templates = $templates;
    }


    /**
     * @param NewsletterInterface $newsletter
     * @throws SendException
     */
    public function prepare(NewsletterInterface $newsletter)
    {
        if ($newsletter->isPrepared()) {
            throw new SendException(sprintf('Newsletter with id "%s" already prepared', $newsletter->getId()));
        }
        $subscription = $this->subscriptionManager->getSubscription('default');
        $receivers = $subscription->getStrategy()->getStorage()->getReceivers($newsletter);

        /** @var Receiver $receiver */
        foreach ($receivers as $receiver) {
            $this->em->persist($receiver);

            if (!$receiver->getToken()) {
                $receiver->setToken($this->tokenGenerator->generateToken());
            }

            $receiver->setNewsletter($newsletter);
        }
        $newsletter->setState(NewsletterInterface::STATE_PREPARED);
        $newsletter->setStartAt(new \DateTime());
        $this->em->flush();
    }

    public function send(NewsletterInterface $newsletter, $limit = null)
    {
        if (!$newsletter->isPrepared()) {
            throw new SendException(sprintf(
                'Newsletter with id "%s" is not prepared yet. Prepare the newsletter first before sending',
                $newsletter->getId())
            );
        }

        if ($newsletter->isSent()) {
            throw new SendException(sprintf('Newsletter with id "%s" already sent', $newsletter->getId()));
        }

        $this->logger->info(sprintf('"%s" prepared receiver found', count($newsletter->getReceivers())));

        $newsletter->setState(NewsletterInterface::STATE_SENDING);

        $mailsSent = 0;

        foreach ($newsletter->getReceivers() as $receiver) {
            if ($mailsSent === $limit) {
                break;
            }
            if (!$receiver->isSent()) {
                if ($this->sendNewsletter($receiver)) {
                    $receiver->setSentAt(new \DateTime());
                    $this->em->flush();
                    $mailsSent++;
                }
            }
        }

        $sent = true;
        foreach ($newsletter->getReceivers() as $receiver) {
            if (!$receiver->isSent()) {
                $sent = false;
                break;
            }
        }

        if ($sent) {
            $newsletter->setState(NewsletterInterface::STATE_SENT);
            $newsletter->setFinishAt(new \DateTime());
            $this->em->flush();
        }
        return $mailsSent;
    }

    private function sendNewsletter(Receiver $receiver)
    {
        $newsletter = $receiver->getNewsletter();
        $message = $this->createMessage($this->from, '', $receiver->getEmail(), $newsletter->getSubject(), $this->getTemplate($newsletter->getTemplate()), [
            'resource' => $newsletter,
            'receiver' => $receiver,
        ], 'text/html');

        if (!empty($receiver->getNewsletter()->getAttachments())) {
            $this->addAttachmentsToMessage($receiver->getNewsletter()->getAttachments(), $message);
        }

        return $this->sendMessage($message);
    }

    private function getTestReceiver(NewsletterInterface $newsletter): Receiver
    {
        $receiver = new Receiver();
        $receiver->setToken($this->testReceiver['token']);
        $receiver->setNewsletter($newsletter);
        $receiver->setParameters($this->testReceiver['parameters']);

        return $receiver;
    }

    public function sendTest(NewsletterInterface $newsletter, string $email): bool
    {
        $return = true;
        $receiver = $this->getTestReceiver($newsletter);
        $receiver->setEmail($email);
        $success = $this->sendNewsletter($receiver);
        if (!$success) {
            $return = false;
        }
        return $return;
    }

    private function addAttachmentsToMessage($files, Message $message)
    {
        /** @var FileInterface $file */
        foreach ($files as $file) {
            $message->addAttachment($file);
        }
    }

    public function renderPreview(NewsletterInterface $newsletter)
    {
        $receiver = $this->getTestReceiver($newsletter);

        return $this->render($receiver);
    }

    public function render(Receiver $receiver)
    {
        $template = $this->getTemplate($receiver->getNewsletter()->getTemplate());
        $content = $this->twig->render($this->templateManager->getTemplate($template), [
            'resource' => $receiver->getNewsletter(),
            'receiver' => $receiver,
        ]);

        $parameters = $receiver->getParameters();
        if ($parameters) {
            $content = $this->parameterParser->parse($content, $receiver->getParameters());
        }

        return $content;
    }

    public function createMessage(string $from, string $senderName, string $to, string $subject, string $template, array $context, string $contentType = 'text/html'): Message
    {
        $message = $this->mailerManager->createMessage();
        $message->setSubject($subject);
        $message->setFrom($from);
        $message->setSenderName($senderName);
        $message->setTo($to);
        $message->setTemplate($template);
        $message->setContext($context);
        $message->setContentType($contentType);

        return $message;
    }

    public function sendMessage(Message $message)
    {
        return $this->mailerManager->sendMessage($message);
    }

    public function getTemplate(?string $key): string
    {
        if ($key === null) {
            if (count($this->templates) === 1) {
                $key = array_keys($this->templates)[0];
                return $this->templates[$key]['template'];
            }
            throw new \Exception(sprintf('No template found for key "%s"', $key));
        }
        return $this->templates[$key]['template'];
    }

}
