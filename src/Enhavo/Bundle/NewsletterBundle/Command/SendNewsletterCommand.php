<?php
/**
 * Created by PhpStorm.
 * User: philippsester
 * Date: 26.08.19
 * Time: 18:25
 */

namespace Enhavo\Bundle\NewsletterBundle\Command;

use Doctrine\ORM\EntityManager;

use Enhavo\Bundle\NewsletterBundle\Entity\Newsletter;
use Enhavo\Bundle\NewsletterBundle\Model\NewsletterInterface;
use Enhavo\Bundle\NewsletterBundle\Newsletter\NewsletterManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class SendNewsletterCommand extends Command
{
    use ContainerAwareTrait;

    /**
     * @var NewsletterManager
     */
    private $manager;

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * SendNewsletterCommand constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('enhavo:newsletter:send')
            ->setDescription('sends a newsletters to its connected receiver')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'how many emails should be sent at max?', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('limit');
        $limit = is_numeric($limit) ? intval($limit) : null;

        $this->manager = $this->container->get(NewsletterManager::class);

        $newsletters = $this->em->getRepository(Newsletter::class)->findNotSentNewsletters();

        $mailsSent = 0;

        foreach($newsletters as $newsletter) {
            if($mailsSent === $limit){
                return;
            }
            $output->writeln(sprintf('Start sending newsletter "%s"', $newsletter->getSubject()));
            $mailsSent += $this->manager->send($newsletter, is_numeric($limit) ? $limit - $mailsSent : null);
        }

        $output->writeln('Finish sending');
    }
}
