<?php

namespace Drupal\lightning\Commands;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Extension\ProfileExtensionList;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;

/**
 * Provides commands to decouple sub-profiles from Lightning.
 *
 * Decoupling a sub-profile consists of two steps:
 * - Modifying the info file to remove any need for Lightning. This includes
 *   removing the 'base profile' key, and rebuilding the 'install' and
 *   'themes' lists, accounting for exclusions.
 * - Copying all config files shipped with Lightning into profile's optional
 *   config. If there are any conflicts, existing config files are preserved.
 */
final class SubProfileDecoupler extends DrushCommands {

  use StringTranslationTrait;

  /**
   * The profile extension list service.
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
   * SubProfileMerger constructor.
   *
   * @param \Drupal\Core\Extension\ProfileExtensionList $profile_list
   *   The profile extension list service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ProfileExtensionList $profile_list, FileSystemInterface $file_system) {
    parent::__construct();
    $this->profileList = $profile_list;
    $this->fileSystem = $file_system;
  }

  /**
   * Uncouples Lightning sub-profiles from Lightning.
   *
   * @command lightning:decouple-profiles
   */
  public function decoupleAll() : void {
    $message = $this->t('Decoupling installation profiles from Lightning...');
    $this->say($message);

    $profiles = $this->profileList->getAllInstalledInfo();
    unset($profiles['lightning']);

    foreach ($profiles as $name => $info) {
      if (isset($info['base profile']) && $info['base profile'] === 'lightning') {
        $this->decouple($name);
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
  public function decouple(string $name, array $options = ['dry-run' => FALSE]) : void {
    $lightning = $this->profileList->getExtensionInfo('lightning');
    $info = $this->profileList->getExtensionInfo($name);

    assert($info['base profile'] === 'lightning');
    unset($info['base profile']);

    $map = function (string $name) : string {
      $name = explode(':', $name, 2);
      return end($name);
    };

    $exclude = array_map($map, $info['exclude'] ?? []);

    $install = array_map($map, $install['install'] ?? []);
    $install = array_merge($install, $lightning['install']);
    $info['install'] = array_diff($install, $exclude);

    $themes = array_merge($info['themes'] ?? [], $lightning['themes']);
    $info['themes'] = array_diff($themes, $exclude);

    $this->write($name, $info, $options['dry-run']);
    $this->copyConfig($name, $options['dry-run']);
  }

  /**
   * Writes modified profile info.
   *
   * @param string $name
   *   The name of the profile to update.
   * @param array $info
   *   The profile info array.
   * @param bool $dry_run
   *   (optional) If TRUE, the profile info will be written out to the console.
   *   Otherwise, the profile's info file will be modified in place. Defaults to
   *   FALSE.
   */
  private function write(string $name, array $info, bool $dry_run = FALSE) : void {
    $info = Yaml::encode($info);

    if ($dry_run) {
      $this->output()->write($info);
    }
    else {
      $variables = [
        '@name' => $name,
      ];
      $bytes_written = file_put_contents($this->profileList->getPathname($name), $info);
      if ($bytes_written) {
        $message = $this->t('Updated @name profile.', $variables);
        $this->say($message);
      }
      else {
        $message = $this->t('Unable to write to @name profile.', $variables);
        $this->io()->error($message);
      }
    }
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
  private function copyConfig(string $name, bool $dry_run = FALSE) : void {
    $destination_dir = $this->profileList->getPath($name) . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY;
    $this->fileSystem->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $message = $this->t('Copying Lightning config to @dir...', [
      '@dir' => $destination_dir,
    ]);
    $this->say($message);

    foreach ($this->listConfig() as $name => $source) {
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
  private function listConfig() : array {
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
