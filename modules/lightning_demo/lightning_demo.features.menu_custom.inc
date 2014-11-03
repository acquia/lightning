<?php
/**
 * @file
 * lightning_demo.features.menu_custom.inc
 */

/**
 * Implements hook_menu_default_menu_custom().
 */
function lightning_demo_menu_default_menu_custom() {
  $menus = array();

  // Exported menu: user-menu.
  $menus['user-menu'] = array(
    'menu_name' => 'user-menu',
    'title' => 'User menu',
    'description' => 'The <em>User</em> menu contains links related to the user\'s account, as well as the \'Log out\' link.',
  );
  // Translatables
  // Included for use with string extractors like potx.
  t('The <em>User</em> menu contains links related to the user\'s account, as well as the \'Log out\' link.');
  t('User menu');


  return $menus;
}
