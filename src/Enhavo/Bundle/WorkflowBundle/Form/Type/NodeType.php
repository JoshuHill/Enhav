<?php

namespace Enhavo\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NodeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('node_name', 'text', array(
            'label' => 'node.form.label.nodeName',
            'translation_domain' => 'EnhavoWorkflowBundle'
        ) );

        $builder->add('start_node', 'enhavo_boolean', array(
            'label' => 'node.form.label.startNode',
            'translation_domain' => 'EnhavoWorkflowBundle'
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Enhavo\Bundle\WorkflowBundle\Entity\Node'
        ));
    }

    public function getName()
    {
        return 'enhavo_workflow_node';
    }
}