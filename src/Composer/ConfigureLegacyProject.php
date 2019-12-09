<?php

namespace Acquia\Lightning\Composer;

use Composer\Json\JsonFile;
use Composer\Script\Event;

/**
 * Configures an instance of drupal/legacy-project to install Lightning.
 */
final class ConfigureLegacyProject {

  /**
   * Executes the script.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    $arguments = $event->getArguments();

    $target = new JsonFile($arguments[0] . '/composer.json');
    $project = $target->read();

    $required = $event->getComposer()->getPackage()->getRequires();
    $components = [
      'api',
      'core',
      'media',
      'layout',
      'workflow',
    ];
    foreach ($components as $component) {
      $project['require']["drupal/lightning_$component"] = $required["drupal/lightning_$component"]->getPrettyConstraint();
    }
    $project['repositories'][] = [
      'type' => 'composer',
      'url' => 'https://asset-packagist.org',
    ];
    $project['extra']['installer-paths']['libraries/{$name}'] = [
      'type:drupal-library',
      'type:bower-asset',
      'type:npm-asset',
    ];
    $project['extra']['installer-types'] = ['bower-asset', 'npm-asset'];
    $project['extra']['patchLevel']['drupal/core'] = '-p2';

    $target->write($project);
  }

}
