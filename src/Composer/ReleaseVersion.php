<?php

namespace Acquia\Lightning\Composer;

use Composer\Script\Event;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Updates the version number in Lightning's component info files.
 */
class ReleaseVersion {

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    $arguments = $event->getArguments();

    $finder = (new Finder())->name('*.info.yml')->in('modules');

    /** @var \Symfony\Component\Finder\SplFileInfo $file */
    foreach ($finder as $file) {
      $info = Yaml::parse($file->getContents());
      $info['version'] = reset($arguments);
      file_put_contents($file->getPathname(), Yaml::dump($info, 2, 2));
    }
  }

}
