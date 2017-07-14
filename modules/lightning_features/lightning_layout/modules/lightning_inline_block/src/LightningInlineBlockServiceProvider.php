<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

class LightningInlineBlockServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    $service_id = 'lightning.quickedit_access';

    if ($container->hasDefinition($service_id)) {
      $container
        ->getDefinition($service_id)
        ->setClass(QuickEditAccess::class)
        ->addArgument(new Reference('database'))
        ->addArgument(new Reference('entity_type.manager'));
    }
  }

}
