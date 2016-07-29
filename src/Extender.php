<?php

namespace Drupal\lightning;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Url;

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
   * Returns the list of Lightning Extensions to enable.
   *
   * @return string[]
   *   The modules to enable.
   */
  public function getLightningExtensions() {
    $info = $this->getInfo();
    // Return FALSE instead of empty array because empty array means "don't
    // enable _any_ extensions" in this case.
    return isset($info['lightning_extensions']) ? $info['lightning_extensions'] : FALSE;
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

    if (!empty($info['redirect']['path'])) {
      $path = ltrim($info['redirect']['path'], '/');
    }
    else {
      // Redirect to the front page by default.
      $path = '<front>';
    }
    $redirect = Url::fromUri('internal:/' . $path);

    if (isset($info['redirect']['options'])) {
      $redirect->setOptions($info['redirect']['options']);
    }

    // Explicitly set the base URL, if not previously set, to prevent weird
    // redirection snafus.
    $base_url = $redirect->getOption('base_url');
    if (empty($base_url)) {
      $redirect->setOption('base_url', $GLOBALS['base_url']);
    }

    return $redirect->setOption('absolute', TRUE)->toString();
  }

}
