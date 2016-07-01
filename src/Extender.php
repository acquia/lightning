<?php

namespace Drupal\lightning;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\UrlHelper;

/**
 * Helper class to get information from a site's lightning.extend.yml file.
 */
class Extender {

  /**
   * The path to the site's configuration (e.g. sites/default)
   *
   * @var string
   */
  protected $sitePath;

  /**
   * Extender constructor.
   *
   * @param string $site_path
   *   The path to the site's configuration (e.g. sites/default).
   */
  public function __construct($site_path) {
    $this->sitePath = (string) $site_path;
  }

  /**
   * Returns the contents of the extender configuration file.
   *
   * @return array
   *   The parsed extender configuration.
   */
  public function getInfo() {
    $path = $this->sitePath . '/lightning.extend.yml';

    if (file_exists($path)) {
      $info = file_get_contents($path);
      return Yaml::decode($info);
    }
    else {
      return [];
    }
  }

  /**
   * Returns the list of additional modules to enable.
   *
   * @return string[]
   *   The modules to enable.
   */
  public function getModules() {
    $info = $this->getInfo();
    return isset($info['modules']) ? $info['modules'] : [];
  }

  /**
   * Returns the URL to redirect to after installation.
   *
   * @return string
   *   The redirect URL, in the form /redirect/path?query=string.
   */
  public function getRedirect() {
    $info = $this->getInfo();

    if ($info['redirect']) {
      $redirect = $info['redirect']['path'];

      if ($info['redirect']['query']) {
        $redirect .= '?' . UrlHelper::buildQuery($info['redirect']['query']);
      }
      return $redirect;
    }
  }

}
