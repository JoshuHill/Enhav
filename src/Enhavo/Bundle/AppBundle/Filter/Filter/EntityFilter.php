<?php
/**
 * TextFilter.php
 *
 * @since 19/01/17
 * @author gseidel
 */

namespace Enhavo\Bundle\AppBundle\Filter\Filter;

use Enhavo\Bundle\AppBundle\Filter\AbstractFilter;
use Enhavo\Bundle\AppBundle\Filter\FilterQuery;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityFilter extends AbstractFilter
{
    public function render($options, $name)
    {
        $choices = $this->getChoices($options);

        return $this->renderTemplate($options['template'], [
            'type' => $this->getType(),
            'label' => $options['label'],
            'translationDomain' => $options['translation_domain'],
            'options' => $choices,
            'name' => $name,
        ]);
    }

    public function buildQuery(FilterQuery $query, $options, $value)
    {
        if ($value == '') {
            return;
        }

        $property = $this->getRequiredOption('property', $options);
        $propertyPath = explode('.', $property);
        $query->addWhere('id', FilterQuery::OPERATOR_EQUALS, $value, $propertyPath);
    }

    private function getChoices($options)
    {
        $repository = $this->getRepository($options);

        $method = $options['method'];
        $arguments =  $options['arguments'];

        if(is_array($arguments)) {
            $entities = call_user_func([$repository, $method], $arguments);
        } else {
            $entities = call_user_func([$repository, $method]);
        }


        $path = $this->getOption('path', $options);
        $choices = [];
        foreach ($entities as $entity) {
            if ($path) {
                $choices[$this->getProperty($entity, 'id')] = $this->getProperty($entity, $path);
            } else {
                $choices[$this->getProperty($entity, 'id')] = (string)$entity;
            }
        }
        return $choices;
    }

    /**
     * @param array $options
     * @return EntityRepository
     */
    private function getRepository($options)
    {
        $repository = null;
        if($this->container->has($options['repository'])) {
            $repository =  $this->container->get($options['repository']);
        } else {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $repository = $em->getRepository($options['repository']);
        }

        if(!$repository instanceof EntityRepository) {
            throw new \InvalidArgumentException(sprintf(
                'Repository should to be type of "%s", but got "%s"',
                EntityRepository::class,
                get_class($repository)
            ));
        }
        return $repository;
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefaults([
            'method' => 'findAll',
            'arguments' => null,
            'template' => 'EnhavoAppBundle:Filter:option.html.twig',
            'path' => null
        ]);

        $optionsResolver->setRequired([
            'repository'
        ]);
    }

    public function getType()
    {
        return 'entity';
    }
}