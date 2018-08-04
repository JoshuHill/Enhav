<?php
/**
 * Created by PhpStorm.
 * User: gseidel
 * Date: 03.05.18
 * Time: 19:02
 */

namespace Enhavo\Bundle\GridBundle\Item;

use Enhavo\Bundle\AppBundle\DynamicForm\ConfigurationInterface;
use Enhavo\Bundle\AppBundle\Type\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractConfiguration extends AbstractType implements ConfigurationInterface
{
    public function getModel($options)
    {
        return $options['model'];
    }

    public function getForm($options)
    {
        return $options['form'];
    }

    public function getRepository($options)
    {
        return $options['repository'];
    }

    public function getLabel($options)
    {
        return $options['label'];
    }

    public function getTranslationDomain($options)
    {
        return $options['translationDomain'];
    }

    public function getParent($options)
    {
        return $options['parent'];
    }

    public function getFactory($options)
    {
        return $options['factory'];
    }

    public function getTemplate($options)
    {
        return $options['template'];
    }

    public function getContentModel($options)
    {
        return $options['content_model'];
    }

    public function getContentFactory($options)
    {
        return $options['content_factory'];
    }

    public function getContentForm($options)
    {
        return $options['content_form'];
    }

    public function getConfigurationForm($options)
    {
        return $options['configuration_form'];
    }

    public function getConfigurationFactory($options)
    {
        return $options['configuration_factory'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'options' => [],
            'parent' => null,
            'translationDomain' => null,
            'content_model' => null,
            'content_factory' => null,
            'content_form' => null,
            'configuration_form' => null,
            'configuration_factory' => null,
        ]);

        $resolver->setRequired([
            'label',
            'type',
            'factory',
            'model',
            'template',
            'form',
            'repository',
        ]);
    }
}