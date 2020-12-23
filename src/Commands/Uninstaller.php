<?php

namespace Drupal\lightning\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Extension\ModuleUninstallValidatorException;
use Drupal\Core\Extension\ProfileExtensionList;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\lightning\ExtensionLocationValidator;
use Drupal\lightning\SubProfileValidator;
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
   * Lightning's uninstall validator to check extension locations.
   *
   * @var \Drupal\lightning\ExtensionLocationValidator
   */
  private $extensionLocationValidator;

  /**
   * Lightning's uninstall validator to detect sub-profiles.
   *
   * @var \Drupal\lightning\SubProfileValidator
   */
  private $subProfileValidator;

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
   * Uninstaller constructor.
   *
   * @param \Drupal\lightning\ExtensionLocationValidator $extension_location_validator
   *   Lightning's uninstall validator to check extension locations.
   * @param \Drupal\lightning\SubProfileValidator $sub_profile_validator
   *   Lightning's uninstall validator to detect sub-profiles.
   * @param \Drupal\Core\Extension\ProfileExtensionList $profile_list
   *   The profile extension list.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ExtensionLocationValidator $extension_location_validator, SubProfileValidator $sub_profile_validator, ProfileExtensionList $profile_list, FileSystemInterface $file_system) {
    $this->extensionLocationValidator = $extension_location_validator;
    $this->subProfileValidator = $sub_profile_validator;
    $this->profileList = $profile_list;
    $this->fileSystem = $file_system;
  }

  /**
   * @hook validate pm:uninstall
   *
   * @throws \LogicException
   *   Thrown if the user attempts to uninstall any other extension(s) at the
   *   same time as Lightning.
   * @throws \Drupal\Core\Extension\ModuleUninstallValidatorException
   *   Thrown if one of Lightning's uninstall validators fails and no automatic
   *   action can be taken to correct the problem.
   */
  public function validate(CommandData $data) : void {
    $arguments = $data->arguments();

    if (in_array('lightning', $arguments['modules'], TRUE)) {
      if (count($arguments['modules']) > 1) {
        throw new \LogicException('You cannot uninstall Lightning and other extensions at the same time.');
      }

      $reasons = $this->extensionLocationValidator->validate('lightning');
      if ($reasons) {
        $reason = reset($reasons);
        throw new ModuleUninstallValidatorException($reason);
      }

      $profiles = [];
      $reasons = $this->subProfileValidator->validate('lightning', $profiles);
      if ($reasons) {
        $this->io()->warning($reasons);

        $decouple = $this->confirm('These profiles can be automatically decoupled from Lightning. Should I do that now?', TRUE);
        if ($decouple) {
          array_walk($profiles, [$this, 'decoupleProfile']);
        }
        else {
          throw new ModuleUninstallValidatorException('These profiles must be decoupled from Lightning before uninstallation can continue.');
        }
      }
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
        $this->say("Updated $name profile.");
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

    $this->say("Copying Lightning configuration to $destination_dir...");

    foreach ($this->getConfigurationToCopy() as $name => $source) {
      $destination = sprintf('%s/%s.%s', $destination_dir, $name, FileStorage::getFileExtension());
      $this->say($name);

      if ($dry_run) {
        continue;
      }

      try {
        $this->fileSystem->copy($source, $destination, FileSystemInterface::EXISTS_ERROR);
      }
      catch (FileExistsException $e) {
        $this->io()->warning($e->getMessage());
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

}
