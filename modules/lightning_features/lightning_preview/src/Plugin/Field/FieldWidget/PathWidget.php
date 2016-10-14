<?php

namespace Drupal\lightning_preview\Plugin\Field\FieldWidget;

use Drupal\path\Plugin\Field\FieldWidget\PathWidget as BasePathWidget;

/**
 * Path widget that transparently prepends the workspace machine name.
 */
class PathWidget extends BasePathWidget {

  use PathWidgetTrait;

}
