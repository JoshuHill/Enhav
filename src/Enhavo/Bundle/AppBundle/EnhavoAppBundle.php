<?php

namespace Enhavo\Bundle\AppBundle;

use Enhavo\Bundle\AppBundle\DependencyInjection\Compiler\FilesystemCompilerPass;
use Enhavo\Bundle\AppBundle\DependencyInjection\Compiler\RouteContentCompilerPass;
use Enhavo\Bundle\AppBundle\DependencyInjection\Compiler\SyliusCompilerPass;
use Enhavo\Bundle\AppBundle\Type\TypeCompilerPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\ORM\Mapping\Driver\YamlDriver;


class EnhavoAppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RouteContentCompilerPass());

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.table_widget_collector', 'enhavo.table_widget')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.block_collector', 'enhavo.block')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.button_collector', 'enhavo.button')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.action_collector', 'enhavo.action')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.viewer_collector', 'enhavo.viewer')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.batch_collector', 'enhavo.batch')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.menu_collector', 'enhavo.menu')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.filter_collector', 'enhavo.filter')
        );

        $container->addCompilerPass(
            new TypeCompilerPass('enhavo_app.chart_provider_collector', 'enhavo.chart_provider')
        );

        $container->addCompilerPass(
            new SyliusCompilerPass()
        );

        $container->addCompilerPass(
            new FilesystemCompilerPass()
        );

        $container->addCompilerPass($this->buildRouteCompilerPass());
    }

    private function buildRouteCompilerPass()
    {
        $arguments = array(array(realpath(__DIR__.'/Resources/config/doctrine-route')), '.orm.yml');
        $locator = new Definition(DefaultFileLocator::class, $arguments);
        $driver = new Definition(YamlDriver::class, array($locator));

        return new DoctrineOrmMappingsPass(
            $driver,
            ['Symfony\Component\Routing'],
            ['doctrine.default_entity_manager']
        );
    }
}