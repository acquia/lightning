<?php

namespace Acquia\LightningScaffold;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates necessary scaffold directories used by tests.
 */
class Plugin {

  /**
   * Callback for composer script to add the themes and modules directory.
   */
  public static function scaffold() {
    $symfony_filesystem = new Filesystem();
    $dirs = ['docroot/themes', 'docroot/modules'];
    $symfony_filesystem->mkdir($dirs);
  }

}
