<?php

/**
 * @file
 * Prepares a code base to update to Drupal 9.
 */

use Drupal\Component\Utility\Random;
use Drupal\views\Entity\View;

Drupal::configFactory()
  ->getEditable('core.extension')
  ->clear('module.libraries')
  ->clear('module.openapi_redoc')
  ->save();

Drupal::configFactory()
  ->getEditable('libraries.settings')
  ->delete();

foreach (['default', 'embedded'] as $view_mode) {
  Drupal::configFactory()
    ->getEditable("core.entity_view_display.media.instagram.$view_mode")
    ->set('content.embed_code.settings.hidecaption', NULL)
    ->save();
}

Drupal::keyValue('system.schema')->deleteMultiple([
  'libraries',
  'openapi_redoc',
]);

$view = View::load('media');
$display = &$view->getDisplay('default');
$display['display_options']['fields']['media_bulk_form']['plugin_id'] = 'bulk_form';
$view->save();

// Prevent a "missing file" warning when running drush updatedb.
$resolution = '16x16';
$random = new Random();
$random->image('public://star.png', $resolution, $resolution);
$random->image('public://star_2.png', $resolution, $resolution);
