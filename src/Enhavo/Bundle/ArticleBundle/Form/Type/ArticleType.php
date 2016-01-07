<?php

namespace Enhavo\Bundle\ArticleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Routing\RouterInterface;

class ArticleType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var bool
     */
    protected $dynamicRouting;

    public function __construct($dataClass, $dynamicRouting, $route, RouterInterface $router)
    {
        $this->route = $route;
        $this->dataClass = $dataClass;
        $this->router = $router;
        $this->dynamicRouting = $dynamicRouting;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $router = $this->router;
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($router) {
            $article = $event->getData();
            $form = $event->getForm();

            if (!empty($article) && $article->getId() && !empty($route)) {
                $url = $router->generate($this->route, array(
                    'id' => $article->getId(),
                    'slug' => $article->getSlug(),
                ), true);

                $form->add('link', 'text', array(
                    'mapped' => false,
                    'data' => $url,
                    'disabled' => true
                ));
            }
        });

        if($this->dynamicRouting) {
            $builder->add('route', 'enhavo_route');
        }

        $builder->add('title', 'text', array(
            'label' => 'form.label.title.h1'
        ));

        $builder->add('meta_description', 'textarea', array(
            'label' => 'form.label.meta_description'
        ));

        $builder->add('page_title', 'text', array(
            'label' => 'form.label.page_title'
        ));

        $builder->add('slug', 'text', array(
            'label' => 'form.label.slug'
        ));

        $builder->add('public', 'enhavo_boolean', array(
            'label' => 'form.label.public'
        ));

        $builder->add('social_media', 'enhavo_boolean');

        $builder->add('priority', 'choice', array(
            'label' => 'form.label.priority',
            'choices'   => array(
                '0.1' => '1',
                '0.2' => '2',
                '0.3' => '3',
                '0.4' => '4',
                '0.5' => '5',
                '0.6' => '6',
                '0.7' => '7',
                '0.8' => '8',
                '0.9' => '9',
                '1' => '10'
            ),
            'expanded' => false,
            'multiple' => false
        ));

        $builder->add('change_frequency', 'choice', array(
            'label' => 'form.label.change_frequency',
            'choices'   => array(
                'always' => 'Immer',
                'hourly' => 'Stündlich',
                'daily' => 'Täglich',
                'weekly' => 'Wöchentlich',
                'monthly' => 'Monatlich',
                'yearly' => 'Jährlich',
                'never' => 'Nie',
            ),
            'expanded' => false,
            'multiple' => false
        ));

        $builder->add('teaser', 'textarea', array(
            'label' => 'form.label.teaser'
        ));

        $builder->add('publication_date', 'datetime', array(
            'label' => 'form.label.publication_date',
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy HH:mm',
        ));

        $builder->add('picture', 'enhavo_files', array(
            'label' => 'form.label.picture'
        ));

        $builder->add('content', 'enhavo_grid');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( array(
            'data_class' => $this->dataClass
        ));
    }

    public function getName()
    {
        return 'enhavo_article_article';
    }
}