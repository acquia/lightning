<?php

/**
 * @file
 * The Lightning profile.
 */

use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\node\Entity\NodeType;
use Drupal\user\RoleInterface;

/**
 * Implements hook_install_tasks().
 */
function lightning_install_tasks() {
  $tasks = [];

  $tasks['lightning_prepare_administrator'] = [];
  $tasks['lightning_set_front_page'] = [];
  $tasks['lightning_disallow_free_registration'] = [];
  $tasks['lightning_grant_shortcut_access'] = [];
  $tasks['lightning_set_default_theme'] = [];
  $tasks['lightning_set_logo'] = [];
  $tasks['lightning_alter_frontpage_view'] = [];

  return $tasks;
}

/**
 * Assigns the 'administrator' role to user 1.
 */
function lightning_prepare_administrator() {
  /** @var \Drupal\user\UserInterface $account */
  $account = entity_load('user', 1);
  if ($account) {
    $account->addRole('administrator');
    $account->save();
  }
}

/**
 * Sets the front page path to /node.
 */
function lightning_set_front_page() {
  if (Drupal::moduleHandler()->moduleExists('node')) {
    Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node')
      ->save(TRUE);
  }
}

/**
 * Only allows administrators to create new user accounts.
 */
function lightning_disallow_free_registration() {
  Drupal::configFactory()
    ->getEditable('user.settings')
    ->set('register', USER_REGISTER_ADMINISTRATORS_ONLY)
    ->save(TRUE);
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
  Drupal::configFactory()
    ->getEditable('system.theme')
    ->set('default', 'bartik')
    ->set('admin', 'seven')
    ->save(TRUE);

  // Use the admin theme for creating content.
  if (Drupal::moduleHandler()->moduleExists('node')) {
    Drupal::configFactory()
      ->getEditable('node.settings')
      ->set('use_admin_theme', TRUE)
      ->save(TRUE);
  }
}

/**
 * Set the path to the logo, favicon and README file based on install directory.
 */
function lightning_set_logo() {
  $lightning_path = drupal_get_path('profile', 'lightning');

  Drupal::configFactory()
    ->getEditable('system.theme.global')
    ->set('logo', [
      'path' => $lightning_path . '/lightning.png',
      'url' => '',
      'use_default' => FALSE,
    ])
    ->set('favicon', [
      'mimetype' => 'image/vnd.microsoft.icon',
      'path' => $lightning_path . '/favicon.ico',
      'url' => '',
      'use_default' => FALSE,
    ])
    ->save(TRUE);
}

/**
 * Alters the frontpage view, if it exists.
 */
function lightning_alter_frontpage_view() {
  $front_page = Drupal::configFactory()->getEditable('views.view.frontpage');

  if (! $front_page->isNew()) {
    $front_page
      ->set('langcode', 'en')
      ->set('status', TRUE)
      ->set('dependencies', [
        'config' => [
          'core.entity_view_mode.node.rss',
          'core.entity_view_mode.node.teaser',
        ],
        'module' => [
          'node',
          'user',
        ],
      ])
      ->set('id', 'frontpage')
      ->set('label', 'Frontpage')
      ->set('module', 'node')
      ->set('description', 'All content promoted to frontpage')
      ->set('tag', 'default')
      ->set('base_table', 'node_field_data')
      ->set('base_field', 'nid')
      ->set('core', '8.x')
      ->set('display.default', [
        'display_options' => [
          'access' => [
            'type' => 'perm',
            'options' => [
              'perm' => 'access content',
            ],
          ],
          'cache' => [
            'type' => 'tag',
            'options' => [],
          ],
          'empty' => [
            'area_text_custom' => [
              'id' => 'area_text_custom',
              'table' => 'views',
              'field' => 'area_text_custom',
              'relationship' => 'none',
              'group_type' => 'group',
              'admin_label' => '',
              'empty' => TRUE,
              'tokenize' => TRUE,
              'content' => '<p>Welcome to [site:name]. No front page content has been created yet.</p><p>Would you like to <a href="/' . drupal_get_path('profile', 'lightning') . '/README.md">view the README</a>?</p>',
              'label' => '',
              'plugin_id' => 'text_custom',
            ],
            'node_listing_empty' => [
              'admin_label' => '',
              'empty' => TRUE,
              'field' => 'node_listing_empty',
              'group_type' => 'group',
              'id' => 'node_listing_empty',
              'label' => '',
              'relationship' => 'none',
              'table' => 'node',
              'plugin_id' => 'node_listing_empty',
              'entity_type' => 'node',
            ],
            'title' => [
              'id' => 'title',
              'table' => 'views',
              'field' => 'title',
              'relationship' => 'none',
              'group_type' => 'group',
              'admin_label' => '',
              'label' => '',
              'empty' => TRUE,
              'title' => 'Welcome to [site:name]',
              'plugin_id' => 'title',
            ],
          ],
          'exposed_form' => [
            'type' => 'basic',
            'options' => [
              'submit_button' => 'Apply',
              'reset_button' => FALSE,
              'reset_button_label' => 'Reset',
              'exposed_sorts_label' => 'Sort by',
              'expose_sort_order' => TRUE,
              'sort_asc_label' => 'Asc',
              'sort_desc_label' => 'Desc',
            ],
          ],
          'filters' => [
            'promote' => [
              'admin_label' => '',
              'expose' => [
                'description' => '',
                'identifier' => '',
                'label' => '',
                'multiple' => FALSE,
                'operator' => '',
                'operator_id' => '',
                'remember' => FALSE,
                'remember_roles' => [
                  'authenticated' => 'authenticated',
                ],
                'required' => FALSE,
                'use_operator' => FALSE,
              ],
              'exposed' => FALSE,
              'field' => 'promote',
              'group' => 1,
              'group_info' => [
                'default_group' => 'All',
                'default_group_multiple' => [],
                'description' => '',
                'group_items' => [],
                'identifier' => '',
                'label' => '',
                'multiple' => FALSE,
                'optional' => TRUE,
                'remember' => FALSE,
                'widget' => 'select',
              ],
              'group_type' => 'group',
              'id' => 'promote',
              'is_grouped' => FALSE,
              'operator' => '=',
              'relationship' => 'none',
              'table' => 'node_field_data',
              'value' => '1',
              'plugin_id' => 'boolean',
              'entity_type' => 'node',
              'entity_field' => 'promote',
            ],
            'status' => [
              'expose' => [
                'operator' => '',
              ],
              'field' => 'status',
              'group' => 1,
              'id' => 'status',
              'table' => 'node_field_data',
              'value' => 'true',
              'plugin_id' => 'boolean',
              'entity_type' => 'node',
              'entity_field' => 'status',
            ],
            'langcode' => [
              'id' => 'langcode',
              'table' => 'node_field_data',
              'field' => 'langcode',
              'relationship' => 'none',
              'group_type' => 'group',
              'admin_label' => '',
              'operator' => 'in',
              'value' => [
                '***LANGUAGE_language_content***' => '***LANGUAGE_language_content***',
              ],
              'group' => 1,
              'exposed' => FALSE,
              'expose' => [
                'operator_id' => '',
                'label' => '',
                'description' => '',
                'use_operator' => FALSE,
                'operator' => '',
                'identifier' => '',
                'required' => FALSE,
                'remember' => FALSE,
                'multiple' => FALSE,
                'remember_roles' => [
                  'authenticated' => 'authenticated',
                ],
                'reduce' => FALSE,
              ],
              'is_grouped' => FALSE,
              'group_info' => [
                'label' => '',
                'description' => '',
                'identifier' => '',
                'optional' => TRUE,
                'widget' => 'select',
                'multiple' => FALSE,
                'remember' => FALSE,
                'default_group' => 'All',
                'default_group_multiple' => [],
                'group_items' => [],
              ],
              'plugin_id' => 'language',
              'entity_type' => 'node',
              'entity_field' => 'langcode',
            ],
          ],
          'pager' => [
            'type' => 'full',
            'options' => [
              'items_per_page' => 10,
              'offset' => 0,
              'id' => 0,
              'total_pages' => 0,
              'expose' => [
                'items_per_page' => FALSE,
                'items_per_page_label' => 'Items per page',
                'items_per_page_options' => '5, 10, 25, 50',
                'items_per_page_options_all' => FALSE,
                'items_per_page_options_all_label' => '- All -',
                'offset' => FALSE,
                'offset_label' => 'Offset',
              ],
              'tags' => [
                'previous' => '‹ Previous',
                'next' => 'Next ›',
                'first' => '« First',
                'last' => 'Last »',
              ],
              'quantity' => 9,
            ],
          ],
          'query' => [
            'type' => 'views_query',
            'options' => [
              'disable_sql_rewrite' => FALSE,
              'distinct' => FALSE,
              'replica' => FALSE,
              'query_comment' => '',
              'query_tags' => [],
            ],
          ],
          'row' => [
            'type' => 'entity:node',
            'options' => [
              'view_mode' => 'teaser',
            ],
          ],
          'sorts' => [
            'sticky' => [
              'admin_label' => '',
              'expose' => [
                'label' => '',
              ],
              'exposed' => FALSE,
              'field' => 'sticky',
              'group_type' => 'group',
              'id' => 'sticky',
              'order' => 'DESC',
              'relationship' => 'none',
              'table' => 'node_field_data',
              'plugin_id' => 'boolean',
              'entity_type' => 'node',
              'entity_field' => 'sticky',
            ],
            'created' => [
              'field' => 'created',
              'id' => 'created',
              'order' => 'DESC',
              'table' => 'node_field_data',
              'plugin_id' => 'date',
              'relationship' => 'none',
              'group_type' => 'group',
              'admin_label' => '',
              'exposed' => FALSE,
              'expose' => [
                'label' => '',
              ],
              'granularity' => 'second',
              'entity_type' => 'node',
              'entity_field' => 'created',
            ],
          ],
          'style' => [
            'type' => 'default',
            'options' => [
              'grouping' => [],
              'row_class' => '',
              'default_row_class' => TRUE,
              'uses_fields' => FALSE,
            ],
          ],
          'title' => '',
          'header' => [],
          'footer' => [],
          'relationships' => [],
          'fields' => [],
          'arguments' => [],
          'display_extenders' => [],
        ],
        'display_plugin' => 'default',
        'display_title' => 'Master',
        'id' => 'default',
        'position' => 0,
        'cache_metadata' => [
          'contexts' => [
            'languages:language_interface',
            'url.query_args',
            'user.node_grants:view',
            'user.permissions',
          ],
          'max-age' => -1,
          'tags' => [],
        ],
      ])
      ->set('display.feed_1', [
        'display_plugin' => 'feed',
        'id' => 'feed_1',
        'display_title' => 'Feed',
        'position' => 2,
        'display_options' => [
          'sitename_title' => TRUE,
          'path' => 'rss.xml',
          'displays' => [
            'page_1' => 'page_1',
            'default' => '',
          ],
          'pager' => [
            'type' => 'some',
            'options' => [
              'items_per_page' => 10,
              'offset' => 0,
            ],
          ],
          'style' => [
            'type' => 'rss',
            'options' => [
              'description' => '',
              'grouping' => [],
              'uses_fields' => FALSE,
            ],
          ],
          'row' => [
            'type' => 'node_rss',
            'options' => [
              'relationship' => 'none',
              'view_mode' => 'rss',
            ],
          ],
          'display_extenders' => [],
        ],
        'cache_metadata' => [
          'contexts' => [
            'languages:language_interface',
            'user.node_grants:view',
            'user.permissions',
          ],
          'max-age' => -1,
          'tags' => [],
        ],
      ])
      ->set('display.page_1', [
        'display_options' => [
          'path' => 'node',
          'display_extenders' => [],
        ],
        'display_plugin' => 'page',
        'display_title' => 'Page',
        'id' => 'page_1',
        'position' => 1,
        'cache_metadata' => [
          'contexts' => [
            'languages:language_interface',
            'url.query_args',
            'user.node_grants:view',
            'user.permissions',
          ],
          'max-age' => -1,
          'tags' => [],
        ],
      ])
      ->save(TRUE);
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
