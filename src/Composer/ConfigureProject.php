<?php

namespace Acquia\Lightning\Composer;

use Composer\Json\JsonFile;
use Composer\Package\CompletePackageInterface as Package;
use Composer\Package\Link;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\JsonLoader;
use Composer\Script\Event;

/**
 * Defines a script to configure a composer.json for uninstalling Lightning.
 *
 * This script takes one argument: the path to a composer.json to modify.
 * Normally this will be the root composer.json of the project using Lightning
 * (i.e., it will have a direct dependency on acquia/lightning). It will be
 * modified as follows:
 * - Lightning's direct requirements will be added to the root composer.json.
 *   If any of them are already present, the existing ones are preserved.
 * - The basic configuration for the drupal/core-composer-scaffold plugin will
 *   be added. So will the plugin itself, but only if the project is NOT already
 *   using the older drupal-composer/drupal-scaffold plugin.
 * - The installer-paths configuration will be copied non-destructively from
 *   Lightning, filling in in any missing destination paths that are not already
 *   defined in the root composer.json.
 * - The npm-asset and bower-asset installer package types will be added if
 *   they are not already present.
 * - The patchLevel, patches, patches-ignore, enable-patching, and
 *   composer-exit-on-patch-failure configurations will be copied
 *   non-destructively from Lightning.
 * - The drupal.org and asset packagist repositories will be added to the root
 *   composer.json if needed.
 */
final class ConfigureProject {

  /**
   * Executes the script.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) : void {
    $arguments = $event->getArguments();
    $file = new JsonFile($arguments[0]);

    $source = $event->getComposer()->getPackage();
    assert($source->getName() === 'acquia/lightning');
    $extra = $source->getExtra();

    $loader = new JsonLoader(new ArrayLoader());
    /** @var \Composer\Package\CompletePackageInterface $target */
    $target = $loader->load($file);

    $data = static::mergeCanadian($file->read(), [
      'requirements' => static::getRequirements($target, $source),
      'extra' => [
        'composer-exit-on-patch-failure' => $extra['composer-exit-on-patch-failure'] ?? TRUE,
        'drupal-scaffold' => [
          'locations' => [
            'web-root' => static::getDrupalRoot($target, $source) . '/',
          ],
        ],
        'enable-patching' => $extra['enable-patching'] ?? TRUE,
        'installer-paths' => static::getPaths($target, $source),
        'installer-types' => static::getPackageTypes($target),
        'patchLevel' => $extra['patchLevel'] ?? [],
        'patches' => $extra['patches'] ?? [],
        'patches-ignore' => $extra['patches-ignore'] ?? [],
      ],
      'repositories' => static::getRepositories($target),
    ]);
    $file->write($data);
  }

  /**
   * Returns the combined requirements for the target package.
   *
   * @param \Composer\Package\CompletePackageInterface $target
   *   The target package.
   * @param \Composer\Package\CompletePackageInterface $source
   *   The source package.
   *
   * @return array
   *   The combined requirements to add to the target package. The keys will
   *   be package names and the values will be version constraints.
   */
  private static function getRequirements(Package $target, Package $source) : array {
    $map = function (Link $link) : string {
      return $link->getPrettyConstraint();
    };

    $requirements = [];
    // The target package's existing dependencies should supersede any
    // dependencies defined by the source package (Lightning).
    $requirements += array_map($map, $target->getRequires());
    $requirements += array_map($map, $source->getRequires());

    // If the target package is not using the deprecated scaffold plugin, use
    // the one that ships with Drupal core. On the other hand, if the target
    // package *is* using the deprecated plugin, they are on their own.
    if (empty($requirements['drupal-composer/drupal-scaffold'])) {
      $requirements += [
        'drupal/core-composer-scaffold' => $requirements['drupal/core'],
      ];
    }
    return $requirements;
  }

  /**
   * Returns the package types to expose to the Composer installers extender.
   *
   * @param \Composer\Package\CompletePackageInterface $target
   *   The target package.
   *
   * @return string[]
   *   The package types to expose to the Composer installers extender plugin
   *   (oomphinc/composer-installers-extender), if available.
   */
  private static function getPackageTypes(Package $target) : array {
    $extra = $target->getExtra();
    $installer_types = $extra['installer-types'] ?? [];

    // Ensure that npm-asset and bower-asset are known package types.
    array_push($installer_types, 'npm-asset', 'bower-asset');
    return array_unique($installer_types);
  }

  /**
   * Returns the combined installer paths for the target package.
   *
   * @param \Composer\Package\CompletePackageInterface $target
   *   The target package.
   * @param \Composer\Package\CompletePackageInterface $source
   *   The source package.
   *
   * @return array[]
   *   An array of paths to be used by the composer/installers plugin.
   */
  private static function getPaths(Package $target, Package $source) : array {
    $root_dir = static::getDrupalRoot($target, $source);
    // If we don't know where Drupal core is installed, we cannot possibly
    // determine where modules, themes, etc. should go.
    if (empty($root_dir)) {
      throw new \LogicException("Cannot determine the Drupal root.");
    }

    $path_map = static::getPathMap($target, $source);
    $path_map += [
      'type:drupal-module' => $root_dir . '/modules/contrib/{$name}',
      'type:drupal-custom-module' => $root_dir . '/modules/custom/{$name}',
      'type:drupal-profile' => $root_dir . '/profiles/contrib/{$name}',
      'type:drupal-theme' => $root_dir . '/themes/contrib/{$name}',
      'type:drupal-custom-theme' => $root_dir . '/themes/custom/{$name}',
      'type:drupal-library' => $root_dir . '/libraries/{$name}',
      'type:npm-asset' => $root_dir . '/libraries/{$name}',
      'type:bower-asset' => $root_dir . '/libraries/{$name}',
    ];

    $paths = [];
    foreach ($path_map as $package => $location) {
      $paths[$location][] = $package;
    }
    return $paths;
  }

  /**
   * Returns the combined repositories for the target package.
   *
   * @param \Composer\Package\CompletePackageInterface $target
   *   The target package.
   *
   * @return array[]
   *   An array of Composer repository definitions to add to the target package.
   */
  private static function getRepositories(Package $target) : array {
    $repositories = [];

    $source_repositories = [
      'https://packages.drupal.org/8',
      'https://asset-packagist.org',
    ];

    $target_repositories = [];
    foreach ($target->getRepositories() as $repository) {
      if ($repository['type'] === 'composer') {
        $target_repositories[] = $repository['url'];
      }
    }

    // Ensure that the two repositories listed in $source_repositories are
    // added to the target package's repositories.
    $repositories_to_add = array_diff($source_repositories, $target_repositories);

    foreach ($repositories_to_add as $url) {
      $repositories[] = [
        'type' => 'composer',
        'url' => $url,
      ];
    }
    return $repositories;
  }

  /**
   * Returns a map of locations where packages will be installed.
   *
   * @param \Composer\Package\CompletePackageInterface $target
   *   The target package.
   * @param \Composer\Package\CompletePackageInterface $source
   *   The source package.
   *
   * @return string[]
   *   A map where the keys are the package, or package type, to install (e.g.,
   *   'drupal/dropzonejs' or 'type:drupal-theme') and the values are the
   *   location where that package or package type will be installed, relative
   *   to the target package.
   */
  private static function getPathMap(Package $target, Package $source) : array {
    if (!isset($target->pathMap)) {
      // Try to get the installer-paths configuration from the target package,
      // falling back to the source package (Lightning) in the unlikely event
      // that the target package has not configured this.
      $extra = $target->getExtra();
      if (empty($extra['installer-paths'])) {
        $extra = $source->getExtra();
      }

      $target->pathMap = [];
      foreach ($extra['installer-paths'] as $location => $packages) {
        foreach ($packages as $package) {
          $target->pathMap[$package] = $location;
        }
      }
    }
    return $target->pathMap;
  }

  /**
   * Returns the path to the Drupal root, relative to the target package.
   *
   * @param \Composer\Package\CompletePackageInterface $target
   *   The target package.
   * @param \Composer\Package\CompletePackageInterface $source
   *   The source package (i.e., Lightning).
   *
   * @return string|null
   *   The path to the Drupal root, relative to the target package, e.g.,
   *   'docroot', or NULL if it cannot be determined.
   */
  private static function getDrupalRoot(Package $target, Package $source) : ?string {
    $path_map = static::getPathMap($target, $source);

    // We expect that the path map has an install location for Drupal core. If
    // it doesn't, that's a pretty major error condition; in such a case, it's
    // not clear how their code base could even be working. Maybe it's a bizarre
    // set-up (symlink jungle?) that we don't support.
    $core_location = $path_map['drupal/core'] ?? $path_map['type:drupal-core'];
    return $core_location ? dirname($core_location) : NULL;
  }

  /**
   * Recursively merges two associative arrays, preserving existing items.
   *
   * @param array $a
   *   The array which $b will be merged into.
   * @param array $b
   *   The array to merge into $a.
   *
   * @return array
   *   The merged array.
   */
  private static function mergeCanadian(array $a, array $b) : array {
    $a += $b;
    foreach ($a as $k => $v) {
      if (is_array($v) && isset($b[$k]) && is_array($b[$k])) {
        $a[$k] = static::mergeCanadian($a[$k], $b[$k]);
      }
    }
    return $a;
  }

}
