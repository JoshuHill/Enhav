<?php
/**
 * Created by PhpStorm.
 * User: gseidel
 * Date: 19.09.17
 * Time: 10:59
 */

namespace Enhavo\Bundle\MediaBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Enhavo\Bundle\MediaBundle\Model\FileInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MediaType extends AbstractType
{
    protected $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $manager = $this->manager;
        $collection = new ArrayCollection();

        //convert view data into concrete file objects
        //save it to collection var because the normalization
        //will overwrite the model data immediately
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($manager, &$collection, $options) {
                $data = $event->getData();
                if($data) {
                    if ($options['multiple']) {
                        foreach($data as $formFile) {
                            $file = $manager->getRepository('EnhavoMediaBundle:File')->find($formFile['id']);
                            $file->setFilename($formFile['filename']);
                            $file->setOrder($formFile['order']);
                            if(isset($formFile['parameters'])) {
                                $file->setParameters($formFile['parameters']);
                            }
                            $file->setGarbage(false);
                            $collection->add($file);
                        }
                    } else {
                        $file = $manager->getRepository('EnhavoMediaBundle:File')->find($data['id']);
                        $file->setFilename($data['filename']);
                        $file->setOrder($data['order']);
                        if(isset($data['parameters'])) {
                            $file->setParameters($data['parameters']);
                        }
                        $file->setGarbage(false);
                        $collection->add($file);
                    }
                }

                //set data to null to keep form synchronized
                $event->setData(null);
            }
        );

        //after normalization write back to model data
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($collection, $options) {
                if ($options['multiple']) {
                    $event->setData($collection);
                } else {
                    $event->setData($collection->get(0));
                }
            }
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if(array_key_exists('information', $options) && is_array($options['information'])) {
            $view->information = $options['information'];
        } else {
            $view->information = array();
        }

        $fields = $options['fields'];
        foreach ($fields as $index => $field) {
            if (!isset($field['translationDomain'])) {
                $fields[$index]['translationDomain'] = '';
            }
            if (!isset($field['type'])) {
                $fields[$index]['type'] = 'text';
            }
            if ($fields[$index]['type'] == 'choices') {
                if (!isset($field['choices'])) {
                    $fields[$index]['choices'] = array();
                }
            }
        }

        $data = $form->getData();
        $view->vars['serializeData'] = $this->serialize($data);
        $view->vars['fields'] = $fields;
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['sortable'] = $options['sortable'];
        $view->vars['mediaConfig'] = [
            'multiple' => $options['multiple'],
            'sortable' => $options['sortable'],
            'extensions' => []
        ];
    }

    private function serialize($data)
    {
        $serializeData = [];
        /** @var $file FileInterface */
        foreach($data as $file) {
            $serializeData[] = [
                'id' => $file->getId(),
                'mimeType' => $file->getMimeType(),
                'extension' => $file->getExtension(),
                'order' => $file->getOrder(),
                'filename' => $file->getFilename(),
                'parameters' => $file->getParameters(),
                'token' => $file->getToken(),
                'md5Checksum' => $file->getMd5Checksum(),
            ];
        }
        return $serializeData;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'multiple'      => true,
            'sortable'      => true,
            'fields'        => array(
                'title'         => array(
                    'label'             => 'media.form.label.title',
                    'translationDomain' => 'EnhavoMediaBundle'
                ),
                'alt_tag'       => array(
                    'label'             => 'media.form.label.alt_tag',
                    'translationDomain' => 'EnhavoMediaBundle'
                )
            ),
            'information' => ''
        ));
    }

    public function getBlockPrefix()
    {
        return 'enhavo_media';
    }

    public function getName()
    {
        return 'enhavo_media';
    }

    public function getParent()
    {
        return 'form';
    }
}