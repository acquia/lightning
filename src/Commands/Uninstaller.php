<?php

namespace Drupal\lightning\Commands;

use Composer\Json\JsonFile;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ProfileExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\FileSystemInterface;
use DrupalFinder\DrupalFinder;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Hooks into Drush to assist with uninstalling Lightning.
 *
 * @internal
 *   This class is a completely internal part of Lightning's uninstall system
 *   and can be changed in any way, or removed outright, at any time without
 *   warning. External code should not use this class in any way.
 */
final class Uninstaller extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

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
   * The Drupal root.
   *
   * @var string
   */
  private $appRoot;

  /**
   * The current installation profile.
   *
   * @var string
   */
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
   * @param string $app_root
   *   The Drupal root.
   * @param string $install_profile
   *   The current installation profile.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, ProfileExtensionList $profile_list, FileSystemInterface $file_system, $app_root, $install_profile) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->profileList = $profile_list;
    $this->fileSystem = $file_system;
    $this->appRoot = $app_root;
    $this->installProfile = $install_profile;
  }

  /**
   * Defines dynamic options if uninstalling Lightning.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   The command object.
   *
   * @hook option pm:uninstall
   */
  public function options(Command $command) : void {
    if ($this->getUninstall()) {
      $command->addOption(
        'profile',
        NULL,
        InputOption::VALUE_REQUIRED,
        'The profile to switch to.',
        'minimal'
      );
      $command->addOption(
        'composer',
        NULL,
        InputOption::VALUE_REQUIRED,
        'The path of the project-level composer.json.',
        $this->locateProjectFile()
      );
    }
  }

  /**
   * Runs a Drush command, with the --yes option.
   *
   * @param string $command
   *   The command name (e.g., 'status').
   * @param array $arguments
   *   (optional) Arguments to pass to the command.
   */
  private function drush(string $command, array $arguments = []) : void {
    $alias = $this->siteAliasManager()->getSelf();

    $this->processManager()
      ->drush($alias, $command, $arguments, ['yes' => NULL])
      ->mustRun();
  }

  /**
   * Returns the profile being uninstalled.
   *
   * @return string|null
   *   If an uninstall of Lightning or Headless Lightning is being attempted,
   *   the machine name of the profile being uninstalled. NULL otherwise.
   */
  private function getUninstall() : ?string {
    $modules = array_intersect($this->input()->getArgument('modules'), [
      'lightning',
      'headless_lightning',
    ]);
    return reset($modules) ?: NULL;
  }

  /**
   * Validates the pm:uninstall command if Lightning is being uninstalled.
   *
   * @param \Consolidation\AnnotatedCommand\CommandData $data
   *   The current command data.
   *
   * @hook validate pm:uninstall
   *
   * @throws \RuntimeException
   *   Thrown if:
   *   - The user is trying to uninstall Lightning (or Headless Lightning) at
   *     the same time as other extension(s).
   *   - Any installed extensions are physically located inside the Lightning
   *     profile directory.
   *   - Any profiles (installed or not) are using Lightning or Headless
   *     Lightning as their immediate parent, and the user declines to modify
   *     them automatically.
   */
  public function validate(CommandData $data) : void {
    $profile = $this->getUninstall();
    if ($profile) {
      $info = $this->profileList->getExtensionInfo($profile);
      $this->io()->title('Welcome to the ' . $info['name'] . ' uninstaller!');

      if (count($data->input()->getArgument('modules')) > 1) {
        throw new \RuntimeException('You cannot uninstall ' . $info['name'] . ' and other extensions at the same time.');
      }

      // Ensure that there are no installed modules or themes in the Lightning
      // profile directory.
      $extensions = $this->getExtensionsInProfileDirectory();
      if ($extensions) {
        $error = sprintf('The following modules and/or themes are located inside the Lightning profile directory. They must be moved elsewhere before Lightning can be uninstalled: %s', implode(', ', $extensions));
        throw new \RuntimeException($error);
      }

      // Ensure that there are no other profiles available that use the profile
      // as a parent. If there are, offer to automatically fix them, and error
      // out if the user declines.
      $children = $this->getChildren($profile);
      if ($children) {
        $warning = sprintf('The following install profiles use %s as a base profile. They must stand alone, or use a different base profile, before Lightning can be uninstalled: %s', $info['name'], implode(', ', $children));
        $this->io()->warning($warning);

        $fix_it = $this->confirm('These profiles can be automatically decoupled from ' . $info['name'] . '. Should I do that now?', TRUE);
        if ($fix_it) {
          array_walk($children, [$this, 'decoupleProfile']);
        }
        else {
          throw new \RuntimeException('These profiles must be decoupled from ' . $info['name'] . ' before uninstallation can continue.');
        }
      }
    }
  }

  /**
   * Performs required actions before Lightning is uninstalled.
   *
   * @hook pre-command pm:uninstall
   */
  public function preCommand() : void {
    if ($this->getUninstall()) {
      if ($this->installProfile === 'lightning' || $this->installProfile === 'headless_lightning') {
        // The lightning_install module was created to prevent broken builds of
        // Lightning (created by drupal.org's legacy packaging system) from
        // being installed.
        $this->drush('pm:uninstall', ['lightning_install']);

        $profile = $this->input()->getOption('profile');
        $this->boldlySay("Switching to $profile profile...");
        $this->drush('pm:enable', ['profile_switcher']);
        $this->drush('switch:profile', [$profile]);
      }
      $this->alterProject();
    }
  }

  /**
   * Performs required actions after Lightning is uninstalled.
   *
   * @hook post-command pm:uninstall
   */
  public function postCommand() : void {
    $profile = $this->getUninstall();
    if ($profile) {
      $this->drush('pm:uninstall', ['profile_switcher']);

      $info = $this->profileList->getExtensionInfo($profile);
      $this->io()->success([
        "Congrats, " . $info['name'] . " has been uninstalled!",
        "You should now commit code and configuration changes, and deploy them to your hosting environment.",
      ]);
    }
  }

  /**
   * Returns installed extensions in the Lightning profile directory.
   *
   * @return string[]
   *   The names of installed extensions in the Lightning profile directory.
   *   If there are any, Lightning cannot be uninstalled. There shouldn't
   *   normally be any -- Lightning doesn't ship with any runtime modules --
   *   but it's wise to check anyway in case the current site has an exotic
   *   set-up.
   */
  private function getExtensionsInProfileDirectory() : array {
    $extensions = array_merge(
      $this->moduleHandler->getModuleList(),
      $this->themeHandler->listInfo()
    );

    $profile_path = $extensions['lightning']->getPath();
    unset($extensions['lightning']);
    // The lightning_install module is a special module that was created to
    // prevent installation of broken builds of Lightning created by the legacy
    // drupal.org packaging system. This utility uninstalls it along with the
    // profile.
    unset($extensions['lightning_install']);

    $filter = function (Extension $extension) use ($profile_path) : bool {
      return strpos($extension->getPath(), $profile_path) !== FALSE;
    };
    $extensions = array_filter($extensions, $filter);
    return array_keys($extensions);
  }

  /**
   * Lists all profiles that have a specific profile as their parent.
   *
   * @param string $parent
   *   The name of the parent profile.
   *
   * @return string[]
   *   The machine names of all profiles, installed or not, that have the given
   *   parent.
   */
  private function getChildren(string $parent) : array {
    $children = [];
    foreach ($this->profileList->getAllAvailableInfo() as $name => $info) {
      if (isset($info['base profile']) && $info['base profile'] === $parent) {
        $children[] = $name;
      }
    }
    return $children;
  }

  /**
   * Uncouples a profile from its parent.
   *
   * This will modify the profile's info file to remove the dependency on
   * the parent, then copy all of the parent's default configuration into the
   * profile's optional config directory. Existing config is preserved, as are
   * any info file customizations.
   *
   * @param string $name
   *   The machine name of the sub-profile.
   */
  private function decoupleProfile(string $name) : void {
    $target = $this->readInfo($name);
    $parent_key = $target['base profile'];
    $parent = $this->readInfo($parent_key);

    $io = $this->io();
    $io->section("Decoupling $name from " . $parent['name']);
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
    // Add all of the parent's dependencies, except for excluded ones.
    $install = array_merge($install, $parent['install']);
    $target['install'] = $this->arrayDiff($install, $exclude);

    // Add all of the parent's themes, except for excluded ones.
    $themes = array_merge($target['themes'] ?? [], $parent['themes']);
    $target['themes'] = $this->arrayDiff($themes, $exclude);

    // If the parent is listed as an explicit dependency, remove that.
    if (isset($target['dependencies'])) {
      $target['dependencies'] = $this->arrayDiff($target['dependencies'], [$parent_key]);
    }

    $destination = $this->profileList->getPathname($name);
    $success = file_put_contents($destination, Yaml::encode($target));
    if ($success) {
      $this->say("Updated $destination.");
    }
    else {
      throw new IOException("Unable to write to $destination.");
    }

    $this->copyConfiguration($parent_key, $name);
    $io->success("$name has been decoupled from " . $parent['name'] . ".");
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
   * Copies all config from one profile into another.
   *
   * @param string $source
   *   The profile from which the config should be copied.
   * @param string $target
   *   The profile into which the config should be copied.
   */
  private function copyConfiguration(string $source, string $target) : void {
    $destination_dir = $this->profileList->getPath($target) . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY;
    $this->fileSystem->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $info = $this->profileList->getExtensionInfo($source);
    $this->boldlySay("Copying " . $info['name'] . " configuration to $target...");

    foreach ($this->getConfigurationToCopy($source) as $name => $path) {
      $destination = sprintf('%s/%s.%s', $destination_dir, $name, FileStorage::getFileExtension());
      $this->say($destination);

      try {
        $this->fileSystem->copy($path, $destination, FileSystemInterface::EXISTS_ERROR);
      }
      catch (FileExistsException $e) {
        $this->io()->note($e->getMessage());
      }
    }
  }

  /**
   * Lists all config that ships with the a profile.
   *
   * @param string $profile
   *   The name of the profile.
   *
   * @return string[]
   *   An array of config that ships with the given profile. The keys will be
   *   the config names, and the values will be the paths of the config files,
   *   relative to the Drupal root.
   */
  private function getConfigurationToCopy(string $profile) : array {
    $base_dir = $this->profileList->getPath($profile);

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
    return $list;
  }

  /**
   * Returns the location of the project-level composer.json.
   *
   * @return string
   *   The path of the project-level composer.json.
   */
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
   */
  private function alterProject() : void {
    $target = $this->input()->getOption('composer');

    $this->boldlySay("Modifying $target...");
    $this->io()->listing([
      'Ensuring direct Lightning dependencies are required',
      'Ensuring required repositories are present',
      'Adding required patches',
      'Checking patcher configuration',
      'Checking installer configuration',
      'Checking scaffold configuration',
    ]);

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

    // If the project requires Headless Lightning, we need to switch it to
    // Headless Lightning 2 to ensure that the modules which are part of
    // Headless Lightning will continue to be present after uninstall.
    if (isset($data['require']['acquia/headless_lightning'])) {
      $data['require']['acquia/headless_lightning'] = 'dev-eol';
    }

    // Delete any empty arrays, since they will be encoded as empty arrays and
    // may therefore break the composer.json schema.
    // @todo Handle this recursively.
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
    // Lightning requires composer/composer in order for this uninstaller to
    // alter composer.json correctly. When that's done, we don't need it
    // anymore.
    unset($source['require']['composer/composer']);
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
    // If the target package uses Headless Lightning, it should be treated as
    // a normal module.
    if (isset($target['require']['acquia/headless_lightning'])) {
      $path_map += [
        'acquia/headless_lightning' => $path_map['type:drupal-module'],
      ];
    }

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

  /**
   * Wrapper around ::say() which displays the text in bold.
   *
   * @param string $text
   *   The text to display.
   */
  private function boldlySay(string $text) : void {
    $this->writeln("<options=bold>$text</options=bold>");
  }

}
