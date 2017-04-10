<?php

namespace Acquia\Lightning\Composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

/**
 * Symlinks JavaScript libraries into docroot.
 *
 * This script exists only to work around the problem described at:
 * https://github.com/oomphinc/composer-installers-extender/issues/6
 *
 * Once that bug is fixed, we don't need this script anymore.
 */
class LinkLibraries {

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    $fs = new Filesystem();

    $composer = $event->getComposer();

    // Get the configured vendor directory.
    $vendor_dir = $composer->getConfig()->get('vendor-dir');

    // The path to the docroot libraries directory should be passed.
    $arguments = $event->getArguments();
    $libraries_dir = realpath($arguments[0]);

    $extra = $composer->getPackage()->getExtra();
    foreach ($extra['installer-paths']['docroot/libraries/{$name}'] as $library) {
      $fs->relativeSymlink(
        realpath($vendor_dir . '/' . $library),
        $libraries_dir . '/' . basename($library)
      );
    }
  }

}
