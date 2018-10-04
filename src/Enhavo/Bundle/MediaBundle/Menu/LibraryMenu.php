<?php
/**
 * LibraryMenu.php
 *
 * @since 02/10/18
 * @author gseidel
 */

namespace Enhavo\Bundle\MediaBundle\Menu;

use Enhavo\Bundle\AppBundle\Menu\Menu\BaseMenu;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryMenu extends BaseMenu
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'icon' => 'docs',
            'label' => 'media.label.library',
            'translationDomain' => 'EnhavoMediaBundle',
            'route' => 'enhavo_media_library_index',
            'role' => 'ROLE_ENHAVO_MEDIA_LIBRARY_INDEX',
        ]);
    }

    public function getType()
    {
        return 'media_library';
    }
}