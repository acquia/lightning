<?php

/**
 * @file
 * The Lightning profile.
 */

use Drupal\node\Entity\NodeType;
use Drupal\user\RoleInterface;

/**
 * Implements hook_install_tasks().
 */
function lightning_install_tasks() {
  $tasks = [];

  $tasks['lightning_grant_shortcut_access'] = [];
  $tasks['lightning_set_default_theme'] = [];
  $tasks['lightning_set_logo'] = [];
  $tasks['lightning_alter_frontpage_view'] = [];

  return $tasks;
}

/**
 * Allows authenticated users to use shortcuts.
 */
function lightning_grant_shortcut_access() {
  if (Drupal::moduleHandler()->moduleExists('shortcut')) {
    user_role_grant_permissions(RoleInterface::AUTHENTICATED_ID, ['access shortcuts']);
  }
}

/**
 * Sets the default and administration themes.
 */
function lightning_set_default_theme() {
  // Use the admin theme for creating content.
  if (Drupal::moduleHandler()->moduleExists('node')) {
    Drupal::configFactory()
      ->getEditable('node.settings')
      ->set('use_admin_theme', TRUE)
      ->save(TRUE);
  }
}

/**
 * Sets the path to the logo and favicon.
 */
function lightning_set_logo() {
  $dir = Drupal::service('extension.list.profile')->getPath('lightning');

  Drupal::configFactory()
    ->getEditable('system.theme.global')
    ->set('logo.path', $dir . '/lightning.png')
    ->set('favicon.path', $dir . '/favicon.ico')
    ->save(TRUE);
}

/**
 * Alters the frontpage view, if it exists.
 */
function lightning_alter_frontpage_view() {
  $front_page = Drupal::configFactory()->getEditable('views.view.frontpage');

  if (!$front_page->isNew()) {
    $section = 'display.default.display_options.empty.area_text_custom';
    $front_page
      ->set("$section.tokenize", TRUE)
      ->set("$section.content", '<p>Welcome to [site:name]. No front page content has been created yet.</p><p>Would you like to <a href="/' . drupal_get_path('profile', 'lightning') . '/README.md">view the README</a>?</p>')
      ->save(TRUE);
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
