<?php

namespace Drupal\lightning\Commands;

use Composer\Json\JsonFile;
use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorException;
use Drupal\Core\Extension\ProfileExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\profile_switcher\ProfileSwitcher;
use DrupalFinder\DrupalFinder;
use Drush\Commands\DrushCommands;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Hooks into Drush to assist with uninstalling Lightning.
 *
 * @internal
 *   This class is a completely internal part of Lightning's uninstall system
 *   and can be changed in any way, or removed outright, at any time without
 *   warning. External code should not use this class in any way.
 */
final class Uninstaller extends DrushCommands {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  private $themeHandler;

  /**
   * The profile extension list.
   *
   * @var \Drupal\Core\Extension\ProfileExtensionList
   */
  private $profileList;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The profile switcher service.
   *
   * @var \Drupal\profile_switcher\ProfileSwitcher
   */
  private $profileSwitcher;

  /**
   * The Drupal root.
   *
   * @var string
   */
  private $appRoot;

  private $installProfile;

  /**
   * Uninstaller constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   * @param \Drupal\Core\Extension\ProfileExtensionList $profile_list
   *   The profile extension list.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, ProfileExtensionList $profile_list, FileSystemInterface $file_system, ?ProfileSwitcher $profile_switcher, $app_root, $install_profile) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->profileList = $profile_list;
    $this->fileSystem = $file_system;
    $this->profileSwitcher = $profile_switcher;
    $this->appRoot = $app_root;
    $this->installProfile = $install_profile;
  }

  private function isActive() {
    return in_array('lightning', $this->input()->getArgument('modules'), TRUE);
  }

  /**
   * Validates the pm:uninstall command if Lightning is being uninstalled.
   *
   * @hook validate pm:uninstall
   *
   * @throws \RuntimeException
   *   Thrown if the profile_switcher module is not installed.
   * @throws \LogicException
   *   Thrown if the user attempts to uninstall any other extension(s) at the
   *   same time as Lightning.
   * @throws \Drupal\Core\Extension\ModuleUninstallValidatorException
   *   Thrown if the Lightning directory contains any installed extensions.
   * @throws \Drupal\Core\Extension\ModuleUninstallValidatorException
   *   Thrown if any install profiles exist that are direct children of
   *   Lightning, and the user decides not to automatically rebuild them.
   */
  public function validate(CommandData $data) : void {
    if ($this->isActive()) {
      $this->io()->title('Welcome to the Lightning uninstaller!');

      if (empty($this->profileSwitcher)) {
        throw new \RuntimeException('The profile_switcher module must be installed in order to uninstall Lightning.');
      }

      if (count($data->input()->getArgument('modules')) > 1) {
        throw new \LogicException('You cannot uninstall Lightning and other extensions at the same time.');
      }

      // Ensure that there are no installed modules or themes in the Lightning
      // profile directory.
      $extensions = $this->getExtensionsInProfileDirectory();
      if ($extensions) {
        $error = sprintf('The following modules and/or themes are located inside the Lightning profile directory. They must be moved elsewhere before Lightning can be uninstalled: %s', implode(', ', $extensions));
        throw new ModuleUninstallValidatorException($error);
      }

      if ($this->decoupleProfiles() == FALSE) {
        throw new ModuleUninstallValidatorException('These profiles must be decoupled from Lightning before uninstallation can continue.');
      }
    }
  }

  /**
   * Performs required actions before the Lightning is uninstalled.
   *
   * @hook pre-command pm:uninstall
   */
  public function preCommand() : void {
    if ($this->isActive()) {
      $target = $this->locateProjectFile();
      $this->say("Modifying $target...");
      $this->alterProject($target);

      if ($this->installProfile === 'lightning') {
        $profile = 'minimal';
        $this->say("Switching to $profile profile...");
        $this->profileSwitcher->switchProfile($profile);
      }
    }
  }

  private function getExtensionsInProfileDirectory() : array {
    $extensions = array_merge(
      $this->moduleHandler->getModuleList(),
      $this->themeHandler->listInfo()
    );

    $profile_path = $extensions['lightning']->getPath();
    unset($extensions['lightning']);

    $filter = function (Extension $extension) use ($profile_path) : bool {
      return strpos($extension->getPath(), $profile_path) !== FALSE;
    };
    $extensions = array_filter($extensions, $filter);
    return array_keys($extensions);
  }

  /**
   * Uncouples all Lightning sub-profile from Lightning.
   *
   * @param array $options
   *   (optional) An array of command options.
   *
   * @command lightning:decouple-profiles
   *
   * @option dry-run
   *   If passed, the modified sub-profile info will be outputted directly,
   *   not written to the profile info file.
   *
   * @return bool
   *   TRUE if all sub-profiles are, or have been, decoupled from Lightning.
   *   FALSE otherwise.
   */
  public function decoupleProfiles(array $options = ['dry-run' => FALSE]) : bool {
    // Find all profiles, installed or not, that have Lightning as their
    // immediate parent. All of them need to be modified before Lightning can
    // be uninstalled.
    $children = [];
    foreach ($this->profileList->getAllAvailableInfo() as $name => $info) {
      if (isset($info['base profile']) && $info['base profile'] === 'lightning') {
        $children[] = $name;
      }
    }

    if ($children) {
      $warning = sprintf('The following install profiles use Lightning as a base profile. They must stand alone, or use a different base profile, before Lightning can be uninstalled: %s', implode(', ', $children));
      $this->io()->warning($warning);

      $fix_it = $this->confirm('These profiles can be automatically decoupled from Lightning. Should I do that now?', TRUE);
      if ($fix_it) {
        foreach ($children as $name) {
          $this->decoupleProfile($name, $options);
        }
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    else {
      return TRUE;
    }
  }

  /**
   * Uncouples a Lightning sub-profile from Lightning.
   *
   * @param string $name
   *   The machine name of the sub-profile.
   * @param array $options
   *   (optional) An array of command options.
   *
   * @command lightning:decouple-profile
   *
   * @option dry-run
   *   If passed, the modified sub-profile info will be outputted directly,
   *   not written to the profile info file.
   */
  public function decoupleProfile(string $name, array $options = ['dry-run' => FALSE]) : void {
    $parent = $this->readInfo('lightning');
    $target = $this->readInfo($name);

    assert($target['base profile'] === 'lightning');
    unset($target['base profile']);

    // This strips out the project prefix from a dependency. For example, this
    // will convert 'drupal:views' to just 'views'.
    $map = function (string $name) : string {
      $name = explode(':', $name, 2);
      return end($name);
    };

    $exclude = array_map($map, $target['exclude'] ?? []);
    unset($target['exclude']);

    $install = array_map($map, $target['install'] ?? []);
    // Add all of Lightning's dependencies, except for excluded ones.
    $install = array_merge($install, $parent['install']);
    $target['install'] = $this->arrayDiff($install, $exclude);

    // Add all of Lightning's themes, except for excluded ones.
    $themes = array_merge($target['themes'] ?? [], $parent['themes']);
    $target['themes'] = $this->arrayDiff($themes, $exclude);

    // If Lightning is listed as an explicit dependency, remove that.
    if (isset($target['dependencies'])) {
      $target['dependencies'] = $this->arrayDiff($target['dependencies'], ['lightning']);
    }

    $target = Yaml::encode($target);

    if ($options['dry-run']) {
      $this->output()->write($target);
    }
    else {
      $destination = $this->profileList->getPathname($name);
      $success = file_put_contents($destination, $target);
      if ($success) {
        $this->io()->success("Updated $destination.");
      }
      else {
        throw new IOException("Unable to write to $destination.");
      }
    }

    $this->copyConfiguration($name, $options['dry-run']);
  }

  /**
   * Returns the difference between two arrays.
   *
   * @param array $a
   *   An array of values.
   * @param array $b
   *   Another array of values.
   *
   * @return array
   *   The items which are in $a but not $b, numerically re-indexed. All
   *   duplicate values will be removed.
   */
  private function arrayDiff(array $a, array $b) : array {
    $c = array_diff($a, $b);
    $c = array_unique($c);
    return array_values($c);
  }

  /**
   * Reads the info file of a profile.
   *
   * @param string $name
   *   The machine name of the profile.
   *
   * @return array
   *   The parsed profile info.
   */
  private function readInfo(string $name) : array {
    $info = $this->profileList->getPathname($name);
    $info = file_get_contents($info);
    return Yaml::decode($info);
  }

  /**
   * Copies all Lightning config into another profile.
   *
   * @param string $name
   *   The profile into which the config should be copied.
   * @param bool $dry_run
   *   (optional) If TRUE, the files to be copied will be written to the
   *   console, but not actually copied. Defaults to FALSE.
   */
  private function copyConfiguration(string $name, bool $dry_run = FALSE) : void {
    $destination_dir = $this->profileList->getPath($name) . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY;
    $this->fileSystem->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $this->io()->note("Copying Lightning configuration to $destination_dir...");

    foreach ($this->getConfigurationToCopy() as $name => $source) {
      $destination = sprintf('%s/%s.%s', $destination_dir, $name, FileStorage::getFileExtension());

      if ($this->output()->isVerbose()) {
        $this->say("$name --> $destination");
      }

      if ($dry_run) {
        continue;
      }

      try {
        $this->fileSystem->copy($source, $destination, FileSystemInterface::EXISTS_ERROR);
      }
      catch (FileExistsException $e) {
        $this->io()->note($e->getMessage());
      }
    }
  }

  /**
   * Lists all config that ships with the Lightning profile.
   *
   * @return string[]
   *   An array of config that ships with the Lightning profile. The keys will
   *   be the config names, and the values will be the paths of the config
   *   files, relative to the Drupal root.
   */
  private function getConfigurationToCopy() : array {
    static $list;

    if ($list === NULL) {
      $base_dir = $this->profileList->getPath('lightning');

      $directories = array_filter([
        $base_dir . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY,
        $base_dir . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY,
      ], 'is_dir');

      $list = [];
      foreach ($directories as $dir) {
        $storage = new FileStorage($dir);
        foreach ($storage->listAll() as $name) {
          $list[$name] = $storage->getFilePath($name);
        }
      }
    }
    return $list;
  }

  private function locateProjectFile() : string {
    $finder = new DrupalFinder();
    if ($finder->locateRoot($this->appRoot) == FALSE) {
      throw new \LogicException("Could not locate the Drupal root.");
    }

    $target = $finder->getComposerRoot() . DIRECTORY_SEPARATOR . 'composer.json';
    assert(file_exists($target), "Expected $target to exist, but it does not.");

    return $target;
  }

  /**
   * Alters the project-level composer.json to uninstall Lightning.
   *
   * @param string $target
   *   The path of the project-level composer.json.
   */
  private function alterProject(string $target) : void {
    $file = new JsonFile($target);
    $target = $file->read();

    // Read Lightning's composer.json, since we will need to merge in many
    // default values from it.
    $source = new JsonFile(__DIR__ . '/../../composer.json');
    $source = $source->read();
    $source += [
      'extra' => [],
    ];
    // Sanity check that we have the correct composer.json.
    assert($source['name'] === 'acquia/lightning');

    $data = $this->mergeCanadian($target, [
      'require' => $this->getRequirements($target, $source),
      'extra' => [
        'composer-exit-on-patch-failure' => $extra['composer-exit-on-patch-failure'] ?? TRUE,
        'drupal-scaffold' => [
          'locations' => [
            'web-root' => $this->getDrupalRoot($target, $source) . '/',
          ],
        ],
        'enable-patching' => $source['extra']['enable-patching'] ?? TRUE,
        'installer-paths' => $this->getPaths($target, $source),
        'installer-types' => $this->getPackageTypes($target),
        'patchLevel' => $source['extra']['patchLevel'] ?? [],
        'patches' => $source['extra']['patches'] ?? [],
        'patches-ignore' => $source['extra']['patches-ignore'] ?? [],
      ],
      'repositories' => $this->getRepositories($target),
    ]);

    // Delete any empty arrays, since they will be encoded as empty arrays and
    // may therefore break the composer.json schema.
    $data = array_filter($data, function ($item) {
      return is_array($item) ? (bool) $item : TRUE;
    });

    $file->write($data);
  }

  /**
   * Returns the combined requirements for the target package.
   *
   * @param array $target
   *   The target package's configuration.
   * @param array $source
   *   The source package's configuration.
   *
   * @return array
   *   The combined requirements to add to the target package. The keys will
   *   be package names and the values will be version constraints.
   */
  private function getRequirements(array $target, array $source) : array {
    $requirements = [];
    // The target package's existing dependencies should supersede any
    // dependencies defined by the source package (Lightning).
    $requirements += ($target['require'] ?? []);
    $requirements += ($source['require'] ?? []);

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
   * @param array $target
   *   The target package's configuration.
   *
   * @return string[]
   *   The package types to expose to the Composer installers extender plugin
   *   (oomphinc/composer-installers-extender), if available.
   */
  private function getPackageTypes(array $target) : array {
    $target += [
      'extra' => [],
    ];
    $installer_types = $target['extra']['installer-types'] ?? [];

    // Ensure that npm-asset and bower-asset are known package types.
    array_push($installer_types, 'npm-asset', 'bower-asset');
    return array_unique($installer_types);
  }

  /**
   * Returns the combined installer paths for the target package.
   *
   * @param array $target
   *   The target package's configuration.
   * @param array $source
   *   The source package's configuration.
   *
   * @return array[]
   *   An array of paths to be used by the composer/installers plugin.
   */
  private function getPaths(array $target, array $source) : array {
    $root_dir = $this->getDrupalRoot($target, $source);
    // If we don't know where Drupal core is installed, we cannot possibly
    // determine where modules, themes, etc. should go.
    if (empty($root_dir)) {
      throw new \LogicException("Cannot determine the Drupal root.");
    }

    $path_map = $this->getPathMap($target, $source);
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
   * @param array $target
   *   The target package's configuration.
   *
   * @return array[]
   *   An array of Composer repository definitions to add to the target package.
   */
  private function getRepositories(array $target) : array {
    $repositories = [];

    $source_repositories = [
      'https://packages.drupal.org/8',
      'https://asset-packagist.org',
    ];

    $target_repositories = [];
    foreach (($target['repositories'] ?? []) as $repository) {
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
   * @param array $target
   *   The target package's configuration.
   * @param array $source
   *   The source package's configuration.
   *
   * @return string[]
   *   A map where the keys are the package, or package type, to install (e.g.,
   *   'drupal/dropzonejs' or 'type:drupal-theme') and the values are the
   *   location where that package or package type will be installed, relative
   *   to the target package.
   */
  private function getPathMap(array $target, array $source) : array {
    // Try to get the installer-paths configuration from the target package,
    // falling back to the source package (Lightning) in the unlikely event
    // that the target package has not configured this.
    $extra = isset($target['extra']['installer-paths'])
      ? $target['extra']
      : $source['extra'];

    $path_map = [];
    foreach ($extra['installer-paths'] as $location => $packages) {
      foreach ($packages as $package) {
        $path_map[$package] = $location;
      }
    }
    return $path_map;
  }

  /**
   * Returns the path to the Drupal root, relative to the target package.
   *
   * @param array $target
   *   The target package's configuration.
   * @param array $source
   *   The source package (i.e., Lightning)'s configuration.
   *
   * @return string|null
   *   The path to the Drupal root, relative to the target package, e.g.,
   *   'docroot', or NULL if it cannot be determined.
   */
  private function getDrupalRoot(array $target, array $source) : ?string {
    $path_map = $this->getPathMap($target, $source);

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
  private function mergeCanadian(array $a, array $b) : array {
    $a += $b;
    foreach ($a as $k => $v) {
      if (is_array($v) && isset($b[$k]) && is_array($b[$k])) {
        $a[$k] = $this->mergeCanadian($a[$k], $b[$k]);
      }
    }
    return $a;
  }

}
