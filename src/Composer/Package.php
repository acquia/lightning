<?php

namespace Acquia\Lightning\Composer;

use Acquia\Lightning\IniEncoder;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Yaml\Yaml;

/**
 * Generates Drush make files for drupal.org's ancient packaging system.
 */
class Package {

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    $composer = $event->getComposer();
    $encoder = new IniEncoder();

    // Convert the lock file to a make file using Drush's make-convert command.
    $bin_dir = $composer->getConfig()->get('bin-dir');
    $make = NULL;
    $executor = new ProcessExecutor();
    $executor->execute($bin_dir . '/drush make-convert composer.lock', $make);
    $make = Yaml::parse($make);

    // Include any drupal-library packages in the make file.
    $libraries = $composer
      ->getRepositoryManager()
      ->getLocalRepository()
      ->getPackages();

    $libraries = array_filter($libraries, function (PackageInterface $package) {
      return $package->getType() == 'drupal-library';
    });

    // Drop the vendor prefixes.
    foreach ($libraries as $library) {
      $old_key = $library->getName();
      $new_key = basename($old_key);
      $make['libraries'][$new_key] = $make['libraries'][$old_key];
      unset($make['libraries'][$old_key]);
    }

    if (isset($make['projects']['drupal'])) {
      // Always use drupal.org's core repository, or patches will not apply.
      $make['projects']['drupal']['download']['url'] = 'https://git.drupal.org/project/drupal.git';

      $core = [
        'api' => 2,
        'core' => '8.x',
        'projects' => [
          'drupal' => [
            'type' => 'core',
            'version' => $make['projects']['drupal']['download']['tag'],
          ],
        ],
      ];
      if (isset($make['projects']['drupal']['patch'])) {
        $core['projects']['drupal']['patch'] = $make['projects']['drupal']['patch'];
      }
      file_put_contents('drupal-org-core.make', $encoder->encode($core));
      unset($make['projects']['drupal']);
    }

    foreach ($make['projects'] as $key => &$project) {
      if ($project['download']['type'] == 'git') {
        $tag = $project['download']['tag'];
        preg_match('/\d+\.x-\d+\.0/', $tag, $match);
        $tag = str_replace($match, str_replace('x-', NULL, $match), $tag);
        preg_match('/\d+\.\d+\.0/', $tag, $match);
        $tag = str_replace($match, substr($match[0], 0, -2), $tag);
        $project['version'] = $tag;
        unset($project['download']);
      }
    }

    file_put_contents('drupal-org.make', $encoder->encode($make));
  }

}
