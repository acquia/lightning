<?php

namespace Drupal\lightning_workflow\Plugin\Update;

use Drupal\lightning\ModuleInstallerTrait;
use Drupal\lightning\UpdateBase;

/**
 * Executes interactive update steps for Lightning Workflow 2.0.2.
 *
 * @Update("2.0.2")
 */
class Update202 extends UpdateBase {

  use ModuleInstallerTrait;

  /**
   * @update
   *
   * @ask Do you want to install Diff?
   */
  public function installDiff() {
    $this->moduleInstaller()->install(['diff']);
  }

}
