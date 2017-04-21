<?php

namespace Drupal\lightning\Command;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for Behat configuration commands.
 */
abstract class BehatCommandBase extends Command {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The path or URI to the aggregated Behat configuration file.
   *
   * @var string
   */
  protected $configPath;

  /**
   * BehatCommandBase constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(FileSystemInterface $file_system) {
    parent::__construct();
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this
      ->addOption(
        'config',
        NULL,
        InputOption::VALUE_REQUIRED,
        'Path to the Behat configuration file.',
        'public://behat.yml'
      )
      ->addOption(
        'merge',
        NULL,
        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'Additional partials to merge into the Behat configuration file.',
        []
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);

    $this->configPath = $input->getOption('config');

    // Ensure that all the files specified by the --merge options exist.
    $input->setOption(
      'merge',
      array_map([$this, 'ensurePath'], $input->getOption('merge'))
    );
  }

  /**
   * Merges partials into the aggregated Behat configuration.
   *
   * @param string[] $merge
   *   Paths to the partials to merge in. In each partial, %paths.base% will
   *   be replaced with the directory in which the partial lives.
   * @param array $config
   *   (optional) The aggregated configuration.
   *
   * @return array
   *   The aggregated configuration with all partials merged in.
   */
  protected function merge(array $merge, array $config = []) {
    foreach ($merge as $path) {
      $merge = file_get_contents($path);
      $merge = str_replace('%paths.base%', dirname($path), $merge);
      $merge = Yaml::decode($merge);

      $config = NestedArray::mergeDeep($config, $merge);
    }
    return $config;
  }

  /**
   * Parses the aggregated Behat configuration.
   *
   * Expects $this->configPath to be set and exist in the file system.
   * Normally ::initialize() will set this from the --config option.
   *
   * @return array
   *   The parsed Behat configuration.
   */
  protected function readConfig() {
    $this->configPath = $this->ensurePath($this->configPath);

    $config = file_get_contents($this->configPath);
    return Yaml::decode($config);
  }

  /**
   * Writes aggregated Behat configuration.
   *
   * Expects $this->configPath to be set and exist in the file system.
   * Normally ::initialize() will set this from the --config option.
   *
   * @param array $config
   *   The Behat configuration to write.
   * @param string[] $merge
   *   (optional) A set of paths of partials to merge into the aggregated
   *   configuration before writing.
   */
  protected function writeConfig(array $config, array $merge = []) {
    if ($merge) {
      $config = $this->merge($merge, $config);
    }

    $success = file_put_contents($this->configPath, Yaml::encode($config));

    if ($success === FALSE) {
      throw new \RuntimeException('Could not write ' . $this->configPath);
    }
  }

  /**
   * Asserts that a file or directory path (or URI) exists.
   *
   * @param string $path
   *   The unresolved file path or URI.
   *
   * @return string
   *   The full, real path.
   *
   * @throws \InvalidArgumentException if the path cannot be resolved.
   */
  protected function ensurePath($path) {
    $real_path = $this->fileSystem->realpath($path);

    if (empty($real_path)) {
      throw new \InvalidArgumentException($path . ' does not exist.');
    }
    return $real_path;
  }

}
