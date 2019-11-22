<?php

declare(strict_types=1);

/**
 * Behat Guzzle Extension
 *
 * PHP version 5
 *
 * @package Behat\GuzzleExtension
 * @author  Dave Nash <dave.nash@teaandcode.com>
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version GIT: $Id$
 * @link    https://github.com/teaandcode/behat-guzzle-extension GuzzleExtension
 */

namespace Behat\GuzzleExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\GuzzleExtension\Context\Initializer\GuzzleAwareInitializer;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Guzzle\Service\Client;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Guzzle extension for Behat class
 *
 * @package Behat\GuzzleExtension\ServiceContainer
 * @author  Dave Nash <dave.nash@teaandcode.com>
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version Release: @package_version@
 * @link    https://github.com/teaandcode/behat-guzzle-extension GuzzleExtension
 */
class GuzzleExtension implements ExtensionInterface
{
    public const GUZZLE_CLIENT_ID = 'guzzle.client';

    /**
     * {@inheritDoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('base_url')->defaultNull()->end()
                ->scalarNode('service_descriptions')->defaultNull()->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigKey()
    {
        return 'guzzle';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $baseUrl = rtrim($config['base_url'], '/');

        $container->setParameter('guzzle.base_url', $baseUrl);
        $container->setParameter('guzzle.parameters', $config);

        $this->loadClient($container);
        $this->loadContextInitializer($container);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Load Client
     *
     * @param ContainerBuilder $container DI Container
     *
     * @access private
     * @return void
     */
    private function loadClient(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::GUZZLE_CLIENT_ID,
            new Definition(
                Client::class,
                [
                    new Parameter('guzzle.base_url'),
                    new Parameter('guzzle.parameters'),
                ]
            )
        );
    }

    /**
     * Load Context Initializer
     *
     * @param ContainerBuilder $container DI Container
     *
     * @access private
     * @return void
     */
    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition(
            GuzzleAwareInitializer::class,
            [
                new Reference(self::GUZZLE_CLIENT_ID),
                new Parameter('guzzle.parameters'),
            ]
        );
        $definition->addTag(
            ContextExtension::INITIALIZER_TAG,
            ['priority' => 0]
        );

        $container->setDefinition('guzzle.context_initializer', $definition);
    }
}
