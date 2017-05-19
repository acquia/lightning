<?php

namespace Drupal\lightning_core\Plugin\views\filter;

use Drupal\lightning_core\YieldToArgumentTrait;
use Drupal\views\Plugin\views\filter\Bundle as BaseBundle;

/**
 * A Bundle filter plugin which supports yielding to an argument.
 */
class Bundle extends BaseBundle {

  use YieldToArgumentTrait;

}
