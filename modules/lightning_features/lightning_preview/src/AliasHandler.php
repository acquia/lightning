<?php

namespace Drupal\lightning_preview;

/**
 * A service for dealing with path aliases across workspaces.
 */
class AliasHandler {

  /**
   * Returns the machine name of the active workspace.
   *
   * @return string
   *   The machine name of the active workspace.
   */
  protected static function defaultPrefix() {
    return \Drupal::service('workspace.manager')
      ->getActiveWorkspace()
      ->getMachineName();
  }

  /**
   * Strips a workspace name name out of a path.
   *
   * @param string $path
   *   The prefixed path.
   * @param string $prefix
   *   (optional) The workspace machine name. Defaults to the active workspace.
   *
   * @return string
   *   The unprefixed path.
   */
  public static function stripPrefix($path, $prefix = NULL) {
    $prefix = $prefix ?: static::defaultPrefix();

    // The live workspace never uses prefixing.
    if ($prefix == 'live') {
      return $path;
    }
    else {
      return preg_replace('/^\/' . $prefix . '\//', '/', $path);
    }
  }

  /**
   * Prepends a workspace machine name to a path.
   *
   * @param string $path
   *   The unprefixed path.
   * @param string $prefix
   *   (optional) The workspace machine name. Defaults to the active workspace.
   *
   * @return string
   *   The prefixed path.
   */
  public static function addPrefix($path, $prefix = NULL) {
    $prefix = $prefix ?: static::defaultPrefix();

    // The live workspace never uses prefixing.
    if ($prefix == 'live') {
      return $path;
    }

    $path = explode('/', ltrim($path, '/'));

    if ($path[0] != $prefix) {
      array_unshift($path, $prefix);
    }

    return '/' . implode('/', $path);
  }

}
