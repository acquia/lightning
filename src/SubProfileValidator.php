<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\Extension\ProfileExtensionList;

/**
 * Validates that no install profiles use Lightning as their parent.
 *
 * @internal
 *   This class is a completely internal part of Lightning's uninstall system
 *   and can be changed in any way, or removed outright, at any time without
 *   warning. External code should not use this class in any way.
 */
final class SubProfileValidator implements ModuleUninstallValidatorInterface {

  /**
   * The profile extension list.
   *
   * @var \Drupal\Core\Extension\ProfileExtensionList
   */
  private $profileList;

  /**
   * SubProfileValidator constructor.
   *
   * @param \Drupal\Core\Extension\ProfileExtensionList $profile_list
   *   The profile extension list.
   */
  public function __construct(ProfileExtensionList $profile_list) {
    $this->profileList = $profile_list;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module, array &$list = NULL) {
    $reasons = [];

    if ($module === 'lightning') {
      $list = [];
      foreach ($this->profileList->getAllAvailableInfo() as $name => $info) {
        if (isset($info['base profile']) && $info['base profile'] === 'lightning') {
          $list[] = $name;
        }
      }

      if ($list) {
        $reasons[] = sprintf('The following install profiles use Lightning as a base profile. They must stand alone, or use a different base profile, before Lightning can be uninstalled: %s', implode(', ', $list));
      }
    }
    return $reasons;
  }

}
