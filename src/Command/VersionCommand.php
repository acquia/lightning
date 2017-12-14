<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Drupal\Console\Annotations\DrupalCommand;

/**
 * Class VersionCommand.
 *
 * @DrupalCommand
 */
class VersionCommand extends Command {

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * VersionCommand constructor.
   *
   * @param string $app_root
   *   The Drupal application root.
   */
  public function __construct($app_root) {
    parent::__construct('lightning:version');
    $this->appRoot = $app_root;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('lightning:version');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $finder = (new Finder())
      ->files()
      ->name('lightning.info.yml')
      ->in($this->appRoot . '/profiles');

    foreach ($finder as $info_file) {
      $info = Yaml::parse($info_file->getContents());
      if (($info['distribution']['name'] == 'Lightning') && isset($info['version'])) {
        // There should only be one file in docroot/profiles named
        // lightning.info.yml, but just to make sure we have the right file in
        // the iterator, break out when we have enough information to be
        // reasonably confident.
        break;
      }
    }

    $io = new DrupalStyle($input, $output);
    $io->info($this->toSemanticVersion($info['version']));
  }

  /**
   * Converts a Lightning release version to a semantic version number according
   * to Lightning's VERSIONS.md file. Examples:
   * - 8.x-1.23 => 1.2.3
   * - 8.x-1.203 => 1.2.3
   * - 8.x-1.230 => 1.2.30
   * - 8.x-1.23-dev => 8.x-1.2.3-dev
   *
   * NOTE: This will break if the minor version number is greater than 9.
   *
   * @param $drupal_version
   *   The version in 8.x-n.nn format.
   *
   * @return string
   *   Semantic version
   */
  public static function toSemanticVersion($drupal_version) {
    preg_match('/^8\.x-(\d+).(\d)(\d+)(-.+)?$/', $drupal_version, $matches);
    $semver = "$matches[1].$matches[2]." . intval($matches[3]);
    if (isset($matches[4])) {
      $semver = $semver .$matches[4];
    }
    return $semver;
  }

}
