<?php

namespace Drupal\lightning_preview\Plugin\Field\FieldWidget;

use Drupal\pathauto\PathautoWidget as BasePathautoWidget;

/**
 * A path widget that transparently prepends the active workspace machine name.
 */
class PathautoWidget extends BasePathautoWidget {

  use PathWidgetTrait;

}
