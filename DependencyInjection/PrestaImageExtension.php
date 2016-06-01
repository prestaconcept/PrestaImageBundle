<?php

namespace Presta\ImageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class PrestaImageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(sprintf('%s/../Resources/config/services', __DIR__)));
        $loader->load('form.xml');
    }
}
