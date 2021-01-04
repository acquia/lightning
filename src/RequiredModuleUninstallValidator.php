<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\RequiredModuleUninstallValidator as BaseValidator;

final class RequiredModuleUninstallValidator extends BaseValidator {

  /**
   * {@inheritdoc}
   */
  protected function getModuleInfoByModule($module) {
    $info = parent::getModuleInfoByModule($module);
    if ($module === 'lightning') {
      $info['required'] = FALSE;
    }
    return $info;
  }

}
