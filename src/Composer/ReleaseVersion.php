<?php

namespace Acquia\Lightning\Composer;

use Acquia\Lightning\ComponentDiscovery;
use Composer\Script\Event;
use Drupal\Component\Serialization\Yaml;

/**
 * A script to update the version number in Lightning's component info files.
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

    $core_package = $event->getComposer()
      ->getRepositoryManager()
      ->getLocalRepository()
      ->findPackage('drupal/core', '*');

    $install_path = $event->getComposer()
      ->getInstallationManager()
      ->getInstallPath($core_package);

    $app_root = realpath($install_path . DIRECTORY_SEPARATOR . '..');

    $discovery = new ComponentDiscovery($app_root);

    foreach ($discovery->getAll() as $component) {
      $path = $app_root . DIRECTORY_SEPARATOR . $component->getPathname();

      $info = file_get_contents($path);
      $info = Yaml::decode($info);

      $info['version'] = reset($arguments);
      $info = Yaml::encode($info);
      file_put_contents($path, $info);
    }
  }

}
