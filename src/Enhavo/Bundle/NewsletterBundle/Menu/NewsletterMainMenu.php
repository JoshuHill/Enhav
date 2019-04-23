<?php
/**
 * NewsletterMenuBuilder.php
 *
 * @since 21/09/16
 * @author gseidel
 */

namespace Enhavo\Bundle\NewsletterBundle\Menu;

use Enhavo\Bundle\AppBundle\Menu\Menu\ListMenu;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterMainMenu extends ListMenu
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'icon' => 'markunread',
            'label' => 'newsletter.label.newsletter',
            'translation_domain' => 'EnhavoNewsletterBundle',
            'children' => [
                'newsletter' => [
                    'type' => 'newsletter_newsletter'
                ],
                'subscriber' => [
                    'type' => 'newsletter_subscriber'
                ],
            ]
        ]);
    }

    public function getType()
    {
        return 'newsletter';
    }
}