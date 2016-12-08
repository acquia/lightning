<?php

namespace Acquia\Lightning;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Script\Event;

/**
 * Changes a composer.json to use the official packages.drupal.org repository.
 */
final class UpgradeBuild {

  /**
   * List of packages installed from the Drupal Composer repository.
   *
   * @var string[]
   */
  protected $drupalPackages = [];

  /**
   * The Composer instance.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * The I/O handler.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * A wrapper around the root package's composer.json.
   *
   * @var JsonFile
   */
  protected $rootPackage;

  /**
   * UpgradeBuild constructor.
   *
   * @param \Composer\Composer $composer
   *   The Composer instance.
   * @param \Composer\IO\IOInterface $io
   *   The I/O handler.
   */
  public function __construct(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->rootPackage = new JsonFile('composer.json', NULL, $io);
  }

  /**
   * Executes the script.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    (new static(
      $event->getComposer(),
      $event->getIO()
    ))->run();
  }

  /**
   * Runs the upgrade process.
   */
  protected function run() {
    // Search upwards for a composer.json which depends on drupal/lightning.
    $this->io->write('Searching for root package...');

    $package = $this->findRootPackage();
    if (empty($package)) {
      $this->io->writeError('No root package found; aborting.');
      return;
    }

    // Use the lock file to determine the packages whose version constraints
    // will need rewriting.
    $this->getDrupalPackages();

    // Rewrite all version constraints to packages.drupal.org format.
    if (isset($package['require-dev'])) {
      $package['require-dev'] = $this->convertVersions($package['require-dev']);
    }
    $package['require'] = $this->convertVersions($package['require']);

    // Use the Lightning package from the Acquia vendor space.
    $package['require']['acquia/lightning'] = '^2.0.0';
    unset($package['require']['drupal/lightning']);

    // If using the Drupal Composer repository, ice it.
    if (isset($package['repositories'])) {
      foreach ($package['repositories'] as $key => $repository) {
        if ($repository['type'] == 'composer' && $this->isUnofficial($repository['url'])) {
          unset($package['repositories'][$key]);
        }
      }
    }

    // Add the packages.drupal.org repository.
    $package['repositories']['packages.drupal.org'] = [
      'type' => 'composer',
      'url' => 'https://packages.drupal.org/8',
    ];

    $this->rootPackage->write($package);
  }

  /**
   * Determines if a URL points to the Drupal Composer (unofficial) repository.
   *
   * @param string $url
   *   The URL to check.
   *
   * @return bool
   *   TRUE if the URL points to the unofficial repository.
   */
  protected function isUnofficial($url) {
    return strpos($url, 'https://packagist.drupal-composer.org') === 0;
  }

  /**
   * Rewrites a set of version constraints.
   *
   * Only packages that came from the Drupal Composer repository are modified.
   *
   * @param array $packages
   *   The constraints to rewrite, keyed by package name.
   *
   * @return string[]
   *   The rewritten constraints.
   */
  protected function convertVersions(array $packages) {
    foreach ($packages as $id => $version) {
      if (in_array($id, $this->drupalPackages)) {
        $packages[$id] = $this->convertVersion($version);
      }
    }
    return $packages;
  }

  /**
   * Rewrites a single version constraint.
   *
   * This code was written by @grasmash for BLT. He is a genius.
   *
   * @param string $version_constraint
   *   The version constraint to rewrite.
   *
   * @return string
   *   The rewritten constraint.
   */
  protected function convertVersion($version_constraint) {
    /*
     * 8.* => *
     */
    if (preg_match('/^8\.\*$/', $version_constraint)) {
      return "*";
    }

    /*
     * ~8 => *@stable
     * ^8 => *@stable
     */
    if (preg_match('/^[\^~]8$/', $version_constraint)) {
      return '*@stable';
    }

    /*
     * dev-master => master-dev
     * dev-something_else#123abc => something_else-dev#123abc
     */
    if (preg_match('/^dev-([A-Za-z0-9-_]+)(#[0-9a-f]+)?/', $version_constraint, $matches)) {
      return $matches[1] . '-dev' . $matches[2];
    }

    /*
     * ~8.1.0-alpha1 > ~1.0.0-alpha1
     * ^8.1.0-alpha1 > ^1.0.0-alpha1
     * ^8.1.0 > ^1.0
     * 8.1.0-alpha1 > 1.0.0-alpha1
     * 8.1.0-beta12 > 1.0.0-beta12
     * 8.12.0-rc22 > 12.0.0-rc22
     */
    if (preg_match('/^([\^~]|[><=!]{1,2})?8(\.)?(\d+)?(\.\d+)?(-(alpha|beta|rc)\d+)?(\.\*)?(@(dev|alpha|beta|rc))?/', $version_constraint, $matches)) {
      /*
       * Group 0. `~8.1.0-alpha1.*@alpha`
       * Group 1. `~`
       * Group 2. `.`
       * Group 3. `1`
       * Group 4. `.0`
       * Group 5. `-alpha1`
       * Group 6. `alpha`
       * Group 7. `.*`
       * Group 8. `@alpha`
       * Group 9. `alpha`
       */

      $new_version_constraint = $matches[3];
      if (isset($matches[4])) {
        $new_version_constraint .= $matches[4];
      }
      if (isset($matches[3])) {
        $new_version_constraint .= '.0';
      }
      else {
        $new_version_constraint .= '*';
      }

      if (isset($matches[5])) {
        $new_version_constraint .= $matches[5];
      }
      elseif (isset($matches[7])) {
        $new_version_constraint .= $matches[7];
      }

      if (isset($matches[8])) {
        $new_version_constraint .= $matches[8];
      }

      if (isset($matches[1])) {
        $new_version_constraint = $matches[1] . $new_version_constraint;
      }

      return $new_version_constraint;
    }

    return $version_constraint;
  }

  /**
   * Builds the list of packages installed from the Drupal Composer repository.
   */
  protected function getDrupalPackages() {
    $reader = new JsonFile('composer.lock', NULL, $this->io);
    $lock = $reader->read();

    foreach ($lock['packages'] as $package) {
      if ($this->isUnofficial($package['notification-url'])) {
        $this->drupalPackages[] = $package['name'];
      }
    }
  }

  /**
   * Reads the root package's composer.json.
   *
   * This will be the composer.json closest to the current working directory
   * that contains a dependency on drupal/lightning.
   *
   * @return array|null
   *   The parsed contents of the root package's composer.json, or NULL if none
   *   was found.
   */
  protected function findRootPackage() {
    // Split the current working directory into an array, accounting for leading
    // and trailing directory separators.
    $dir = explode(DIRECTORY_SEPARATOR, trim(getcwd(), DIRECTORY_SEPARATOR));

    do {
      if ($this->rootPackage->exists()) {
        $package = $this->rootPackage->read();

        if (isset($package['require']['drupal/lightning'])) {
          return $package;
        }
      }
      chdir('..');
      array_pop($dir);
    } while ($dir);
  }

}
