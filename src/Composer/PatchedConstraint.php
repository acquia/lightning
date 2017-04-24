<?php

namespace Acquia\Lightning\Composer;

use Composer\Json\JsonFile;
use Composer\Script\Event;

/**
 * Ensures that all patched dependencies are pinned to a specific version.
 */
class PatchedConstraint {
  private $json;
  private $patched_dependencies;

  public static function execute(Event $event) {
    $root_package = $event->getComposer()->getPackage();
    $patched_dependencies = self::getPatchedDependencyConstraints();
  }


  /**
   * Gets a list of required packages that also have patches defined.
   *
   * @param array $ignore
   *   Packages to ignore.
   *
   * @return array
   *   List of required packages which are patched and their version constraint.
   */
  protected function getPatchedDependencyConstraints($ignore = ['drupal/core']) {
    $app_root = \Drupal::root();
    $json_file = new JsonFile($app_root . '/../composer.json');
    $json = $json_file->read();
    $patched = array_keys($json['extra']['patches']);
    $patched_dependencies = array_intersect_key($json['require'], array_flip($patched));
    // @todo strip out ignored dependencies.
    return $patched_dependencies;
  }

  /**
   * Checks to see if a given constraint is pinned to a specific release.
   *
   * @return bool
   */
  protected function isUnpinned($constraint) {
    if ((!is_numeric(substr($constraint, 0, 1))) || (strpos($constraint, '|') !== FALSE) || (strpos($constraint, '*') !== FALSE)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets a list of patched dependencies whose version constraint might cause
   * the defined patch to fail to apply.
   *
   * @return array
   *   An associative array of dependencies and their constraints.
   */
  public function getUnpinnedPatchedDependencies() {
    $unpinned_patched_dependencies = [];
    foreach ($this->patched_dependencies as $dependency => $constraint) {
      if ($this->isUnpinned($constraint)) {
        $unpinned_patched_dependencies[$dependency] = $constraint;
      }
    }
    return $unpinned_patched_dependencies;
  }

}