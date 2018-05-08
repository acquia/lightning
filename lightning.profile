<?php

/**
 * @file
 * The Lightning profile.
 */

use Drupal\Core\Cache\CacheCollectorInterface;
use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\node\Entity\NodeType;
use Drupal\user\RoleInterface;

/**
 * Implements hook_cron().
 */
function lightning_cron() {
  $state = Drupal::state();

  // At some point, core started caching state values, both statically and
  // persistently. Unfortunately, the cron service does not explicitly persist
  // the system.cron_last variable, which means that subsequent reads of
  // system.cron_last might return an outdated value, thus breaking any code
  // which is sensitive to the last cron run time (e.g., Lightning Scheduler).
  // This should be fixed in core at some point, but for now we can work around
  // it by ensuring the state cache is cleared during cron, ensuring that all of
  // its values are persisted.
  if ($state instanceof CacheCollectorInterface) {
    $state->resetCache();
  }
}

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

/**
 * Implements hook_ENTITY_TYPE_presave().
 */
function lightning_user_role_presave(RoleInterface $role) {
  if ($role->isNew() && $role->id() === 'layout_manager') {
    foreach (NodeType::loadMultiple() as $node_type) {
      $role->grantPermission('administer panelizer node ' . $node_type->id() . ' defaults');
    }
  }
}
