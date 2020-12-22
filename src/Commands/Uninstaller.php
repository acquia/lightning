<?php

namespace Drupal\lightning\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drush\Commands\DrushCommands;

final class Uninstaller extends DrushCommands {

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
    }
  }

}
