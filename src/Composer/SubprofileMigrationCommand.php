<?php

namespace Acquia\Lightning\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Drupal\Component\Serialization\Yaml;

/**
 * Migrates Lightning Extender configuration to a sub-profile.
 */
class SubprofileMigrationCommand {

  /**
   * The IO handler.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * The Composer instance.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * The path to the lightning.extend.yml file, if known.
   *
   * @var string|false
   */
  protected $path;

  /**
   * SubprofileMigrationCommand constructor.
   *
   * @param \Composer\IO\IOInterface $io
   *   The IO handler.
   * @param \Composer\Composer $composer
   *   The Composer instance.
   * @param array $arguments
   *   Arguments passed to the script.
   */
  public function __construct(IOInterface $io, Composer $composer, array $arguments = []) {
    $this->io = $io;
    $this->composer = $composer;
    $this->path = reset($arguments);
  }

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The event that triggered the script.
   */
  public static function execute(Event $event) {
    $command = new static(
      $event->getIO(),
      $event->getComposer(),
      $event->getArguments()
    );
    $command->run();
  }

  /**
   * Executes the command.
   */
  protected function run() {
    $extender = realpath($this->locateExtender());

    if ($extender) {
      $extender = file_get_contents($extender);
      $extender = Yaml::decode($extender);

      // Build the Drupal Console command line.
      $command = 'lightning:subprofile --no-interaction';
      $command .= ' --name="Lightning Extender"';
      $command .= ' --machine-name=lightning_extender';

      if ($extender['modules']) {
        $command .= ' --include=' . implode(',', $extender['modules']);
      }

      $exclude = [];
      if ($extender['lightning_extensions']) {
        // Sadly, ComponentDiscovery doesn't work without Drupal (because of
        // reasons...to do with the autoloader). So we'll just hard-code the
        // list of main components.
        $exclude = array_merge($exclude, array_diff(
          [
            'lightning_layout',
            'lightning_media',
            'lightning_preview',
            'lightning_workflow',
          ],
          $extender['lightning_extensions']
        ));
      }
      if ($extender['exclude_components']) {
        $exclude = array_merge($exclude, $extender['exclude_components']);
      }
      if ($exclude) {
        $command .= ' --exclude=' . implode(',', $exclude);
      }

      // If Drupal Console is installed, go ahead and run the command
      // non-interactively. Otherwise, echo the command and tell the user to run
      // it themselves.
      $package = $this->composer
        ->getRepositoryManager()
        ->getLocalRepository()
        ->findPackage('drupal/console', '*');

      if ($package) {
        $command = $this->composer->getConfig()->get('bin-dir') . '/drupal ' . $command;
        (new ProcessExecutor($this->io))->execute($command);
      }
      else {
        $this->io->write(<<<END
Drupal Console does not appear to be installed. Install it and run the following command to generate a Lightning subprofile:

/path/to/drupal-console $command
END
        );
      }
    }
    else {
      throw new \RuntimeException('Could not locate lightning.extend.yml.');
    }
  }

  /**
   * Attempts to locate the lightning.extend.yml file.
   *
   * @return string
   *   The relative, unverified path a lightning.extend.yml file.
   */
  protected function locateExtender() {
    $file = 'lightning.extend.yml';

    // If the extender path (either the containing directory or the full path)
    // was passed, use that.
    if ($this->path) {
      $file = is_dir($this->path) ? $this->path . '/' . $file : $this->path;
    }

    if (file_exists($file)) {
      return $file;
    }
    else {
      return $this->getCorePath() . '/../sites/default/lightning.extend.yml';
    }
  }

  /**
   * Returns the path to Drupal core.
   *
   * @return string
   *   The installed path to Drupal core.
   */
  protected function getCorePath() {
    // Locate Drupal core so we can find the sites directory.
    $package = $this->composer
      ->getRepositoryManager()
      ->getLocalRepository()
      ->findPackage('drupal/core', '*');

    return $this->composer->getInstallationManager()->getInstallPath($package);
  }

}
