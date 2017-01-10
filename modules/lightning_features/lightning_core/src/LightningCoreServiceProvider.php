<?php

namespace Drupal\lightning_core;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers services dynamically.
 */
class LightningCoreServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    // We cannot define the lightning.search_helper service statically because
    // it depends on Search API, which might not exist when the static service
    // definitions are registered (which, in turn, will blow things up).
    if ($container->hasDefinition('plugin.manager.search_api.datasource')) {
      $container->register('lightning.search_helper', SearchHelper::class)
        ->addArgument(new Reference('entity_type.manager'))
        ->addArgument(new Reference('plugin.manager.search_api.datasource'))
        ->addArgument('content');
    }
  }

}
