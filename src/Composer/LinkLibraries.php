<?php

namespace Acquia\Lightning\Composer;

use Composer\Package\PackageInterface;
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

    $package = $composer->getPackage();
    foreach (static::getLibraries($package) as $library) {
      $fs->relativeSymlink(
        realpath($vendor_dir . '/' . $library),
        $libraries_dir . '/' . basename($library)
      );
    }
  }

  /**
   * Returns the list of JavaScript libraries installed by a package.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package.
   *
   * @return string[]
   *   The JavaScript library package names, with vendor prefixes.
   */
  public static function getLibraries(PackageInterface $package) {
    $pattern = 'docroot/libraries/{$name}';
    $extra = $package->getExtra();

    return isset($extra['installer-paths'][$pattern])
      ? $extra['installer-paths'][$pattern]
      : [];
  }

}
