<?php

namespace Drupal\lightning_inline_block\Plugin\DisplayBuilder;

use Drupal\Core\Layout\LayoutInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels_ipe\Plugin\DisplayBuilder\InPlaceEditorDisplayBuilder as BaseInPlaceEditorDisplayBuilder;

class InPlaceEditorDisplayBuilder extends BaseInPlaceEditorDisplayBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getDrupalSettings(array $regions, LayoutInterface $layout, PanelsDisplayVariant $panels_display, $unsaved, $locked) {
    $settings = parent::getDrupalSettings($regions, $layout, $panels_display, $unsaved, $locked);
    $settings['user_permission']['create_content'] = TRUE;
    return $settings;
  }

}
