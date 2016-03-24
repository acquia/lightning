<?php

/**
 * @file
 * Contains \Acquia\Lightning\Bower.
 */

namespace Acquia\Lightning;

/**
 * A helper class for interacting with Bower and its installed packages.
 */
class Bower implements \IteratorAggregate {

  /**
   * The directory where Bower packages are installed.
   *
   * @var string
   *
   * @see getDirectory()
   */
  protected $directory;

  /**
   * Loops over lock info for all installed dependencies.
   */
  public function getIterator() {
    if (file_exists('bower.json')) {
      $info = file_get_contents('bower.json');
      $info = json_decode($info, TRUE);

      foreach (array_keys($info['dependencies']) as $package) {
        yield $this->getLockInfo($package);
      }
    }
    else {
      yield [];
    }
  }

  /**
   * Returns Bower lock info for a specific package.
   *
   * @param string $package
   *   The name of the package.
   *
   * @return array
   *   The package info.
   *
   * @throws \RuntimeException
   *   If the package is not installed.
   */
  public function getLockInfo($package) {
    $info = $this->getDirectory() . "/$package/.bower.json";
    if (file_exists($info)) {
      $info = file_get_contents($info);
      return json_decode($info, TRUE);
    }
    else {
      throw new \RuntimeException('Package ' . $package . ' is not installed.');
    }
  }

  /**
   * Returns the contents of .bowerrc, if it exists.
   *
   * @return array
   *   The parsed contents of .bowerrc.
   */
  protected function getConfig() {
    if (file_exists('.bowerrc')) {
      $config = file_get_contents('.bowerrc');
      return json_decode($config, TRUE);
    }
    else {
      return [];
    }
  }

  /**
   * Returns the directory where Bower dependencies are installed.
   *
   * @return string
   *   The directory where Bower dependencies are installed; defaults to
   *   bower_components if not set.
   */
  public function getDirectory() {
    if (empty($this->directory)) {
      $config = $this->getConfig();
      $this->directory = @($config['directory'] ?: 'bower_components');
    }
    return $this->directory;
  }

}
