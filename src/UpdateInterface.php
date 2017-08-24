<?php

namespace Drupal\lightning;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Executable\ExecutableInterface;

/**
 * Interface implemented by interactive update plugins.
 */
interface UpdateInterface extends ExecutableInterface, PluginInspectionInterface {
}
