<?php

/**
 * @file
 * The Lightning profile.
 */

use Drupal\lightning_core\ConfigHelper as Config;

/**
 * Implements hook_modules_installed().
 */
function lightning_modules_installed(array $modules) {
  if (\Drupal::isConfigSyncing()) {
    return;
  }

  if (in_array('lightning_dev', $modules, TRUE)) {
    Config::forModule('lightning_media')
      ->optional()
      ->getEntity('user_role', 'media_creator')
      ->grantPermission('use editorial transition create_new_draft')
      ->save();

    entity_create('node_type', [
      'type' => 'article',
      'name' => 'Article',
    ])->save();
  }
}
