<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\RequiredModuleUninstallValidator as BaseValidator;

/**
 * Validates module uninstallation.
 *
 * @internal
 *   This class is a completely internal part of Lightning's uninstall system
 *   and can be changed in any way, or removed outright, at any time without
 *   warning. External code should not use this class in any way.
 */
final class RequiredModuleUninstallValidator extends BaseValidator {

  /**
   * {@inheritdoc}
   */
  protected function getModuleInfoByModule($module) {
    $info = parent::getModuleInfoByModule($module);
    if ($module === 'lightning' || $module === 'headless_lightning') {
      $info['required'] = FALSE;
    }
    return $info;
  }

}
