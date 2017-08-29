<?php

namespace Drupal\lightning;

trait ModuleInstallerTrait {

  protected $moduleInstaller;

  protected function moduleInstaller() {
    return $this->moduleInstaller ?: \Drupal::service('module_installer');
  }

}
