<?php

namespace Drupal\lightning\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Core\Extension\ModuleUninstallValidatorException;
use Drupal\lightning\ExtensionLocationValidator;
use Drupal\lightning\SubProfileValidator;
use Drush\Commands\DrushCommands;

/**
 * Hooks into Drush to assist with uninstalling Lightning.
 *
 * @internal
 *   This class is a completely internal part of Lightning's uninstall system
 *   and can be changed in any way, or removed outright, at any time without
 *   warning. External code should not use this class in any way.
 */
final class Uninstaller extends DrushCommands {

  /**
   * Lightning's uninstall validator to check extension locations.
   *
   * @var \Drupal\lightning\ExtensionLocationValidator
   */
  private $extensionLocationValidator;

  /**
   * Lightning's uninstall validator to detect sub-profiles.
   *
   * @var \Drupal\lightning\SubProfileValidator
   */
  private $subProfileValidator;

  /**
   * Uninstaller constructor.
   *
   * @param \Drupal\lightning\ExtensionLocationValidator $extension_location_validator
   *   Lightning's uninstall validator to check extension locations.
   * @param \Drupal\lightning\SubProfileValidator $sub_profile_validator
   *   Lightning's uninstall validator to detect sub-profiles.
   */
  public function __construct(ExtensionLocationValidator $extension_location_validator, SubProfileValidator $sub_profile_validator) {
    $this->extensionLocationValidator = $extension_location_validator;
    $this->subProfileValidator = $sub_profile_validator;
  }

  /**
   * @hook validate pm:uninstall
   *
   * @throws \LogicException
   *   Thrown if the user attempts to uninstall any other extension(s) at the
   *   same time as Lightning.
   */
  public function validate(CommandData $data) : void {
    $arguments = $data->arguments();

    if (in_array('lightning', $arguments['modules'], TRUE)) {
      if (count($arguments['modules']) > 1) {
        throw new \LogicException('You cannot uninstall Lightning and other extensions at the same time.');
      }

      $reasons = $this->extensionLocationValidator->validate('lightning');
      if ($reasons) {
        $reason = reset($reasons);
        throw new ModuleUninstallValidatorException($reason);
      }

      $reasons = $this->subProfileValidator->validate('lightning');
      if ($reasons) {
        $this->io()->warning($reasons);

        $decouple = $this->confirm('These profiles can be automatically decoupled from Lightning. Should I do that now?', TRUE);
        if ($decouple) {
          $this->subProfileDecoupler->decoupleAll();
        }
        else {
          throw new ModuleUninstallValidatorException('These profiles must be decoupled from Lightning before uninstallation can continue.');
        }
      }
    }
  }

}
