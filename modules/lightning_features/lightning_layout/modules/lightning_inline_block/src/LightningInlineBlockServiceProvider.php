<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class LightningInlineBlockServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    if ($container->hasDefinition('lightning.quickedit_access')) {
      $container
        ->getDefinition('lightning.quickedit_access')
        ->setClass(QuickEditAccess::class);
    }
  }

}
