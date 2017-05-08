<?php

namespace Acquia\Lightning\Composer;

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;

/**
 * Ensures that all patched dependencies are pinned to a specific version.
 */
class PatchedConstraint {

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   *
   * @return bool
   *   False if there are unpinned patched dependencies.
   */
  public static function execute(Event $event) {
    $root_package = $event->getComposer()->getPackage();
    $patched_dependencies = self::getPatchedDependencyConstraints($root_package);
    $error = [];

    /** @var \Composer\Package\Link $package */
    foreach ($patched_dependencies as $package) {
      if (self::packageIsUnpinned($package)) {
        $error[] = $package->getTarget() . ': ' . $package->getPrettyConstraint();
      }
    }
    if (!empty($error)) {
      array_unshift($error, 'The following dependencies are patched but don\'t have pinned dependency constraints:');
      $event->getIO()->writeError($error);
      return false;
    }
    else {
      $event->getIO()->write('Patched dependencies have constraints that are properly pinned.');
    }
  }

  /**
   * Filters the requires section of the RootPackage down to packages that also
   * have patches defined in the extra section.
   *
   * @param \Composer\Package\RootPackageInterface $root_package
   *   The root composer.json package.
   *
   * @return \Composer\Package\Link array
   *   List of required packages that are patched.
   */
  protected static function getPatchedDependencyConstraints(RootPackageInterface $root_package) {
    $required = $root_package->getRequires();
    $extra = $root_package->getExtra();
    $patched = $extra['patches'];
    return array_intersect_key($required, $patched);
  }

  /**
   * @param \Composer\Package\Link $package
   *   The package to check.
   *
   * @return bool
   *   True if the constraint appears to be unpinned.
   */
  protected static function packageIsUnpinned(\Composer\Package\Link $package) {
    if ($package->getTarget() == 'drupal/core') {
      // Bail out if the patched package is drupal/core since we release with
      // each version of core and always ensure core patches still apply.
      return false;
    }
    $constraint = $package->getPrettyConstraint();
    if ((!is_numeric(substr($constraint, 0, 1))) || (strpos($constraint, '|') !== FALSE) || (strpos($constraint, '*') !== FALSE)) {
      // An unpinned constraint is a constraint where any of the following are
      // true:
      //   - The first character is NOT an integer (e.g. ~)
      //   - Contains the OR operator
      //   - Contains a wildcard
      return true;
    }
  }

}