<?php

namespace Drupal\lightning\Plugin\Update;

use Drupal\lightning\ModuleInstallerTrait;
use Drupal\lightning\UpdateBase;

/**
 * Executes interactive update steps for Lightning 2.1.8.
 *
 * @Update("2.1.8")
 */
class Update218 extends UpdateBase {

  use ModuleInstallerTrait;

  /**
   * @update
   *
   * @ask Do you want to install contact form functionality?
   */
  public function installContactForm() {
    $this->moduleInstaller()->install(['lightning_contact_form']);
  }

}
