<?php

/**
 * @file
 * The Lightning profile.
 */

use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\node\Entity\NodeType;
use Drupal\user\RoleInterface;

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

    $node_type_storage = \Drupal::entityTypeManager()->getStorage('node_type');

    if (!$node_type_storage->load('article')) {
      $node_type_storage
        ->create([
          'type' => 'article',
          'name' => 'Article',
        ])
        ->save();
    }

    // Temporarily uninstall Pathauto for testing.
    Drupal::service('module_installer')->uninstall(['pathauto']);
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
