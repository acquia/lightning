<?php

namespace Drupal\lightning;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

final class LightningServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->getDefinition('required_module_uninstall_validator')
      ->setClass(RequiredModuleUninstallValidator::class);
  }

}
