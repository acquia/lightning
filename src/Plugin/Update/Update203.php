<?php

namespace Drupal\lightning\Plugin\Update;

use Drupal\lightning\ModuleInstallerTrait;
use Drupal\lightning\UpdateBase;

/**
 * Executes interactive update steps for Lightning 2.0.3.
 *
 * @Update("2.0.3")
 */
class Update203 extends UpdateBase {

  use ModuleInstallerTrait;

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $this->confirm('installSearch');
  }

  /**
   * @ask Do you want to add search functionality?
   */
  protected function installSearch() {
    $this->moduleInstaller()->install(['lightning_search']);
  }

}
