<?php

/**
 * Implements template_preprocess_html().
 *
 */
//function lightningdemo_preprocess_html(&$variables) {
//  // Add conditional CSS for IE. To use uncomment below and add IE css file
//  drupal_add_css(path_to_theme() . '/css/ie.css', array('weight' => CSS_THEME, 'browsers' => array('!IE' => FALSE), 'preprocess' => FALSE));
//
//  // Need legacy support for IE downgrade to Foundation 2 or use JS file below
//  // drupal_add_js('http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE7.js', 'external');
//}

/**
 * Implements template_preprocess_page
 *
 */
//function lightningdemo_preprocess_page(&$variables) {
//}

/**
 * Implements template_preprocess_node
 *
 */
//function lightningdemo_preprocess_node(&$variables) {
//}

function lightningdemo_menu_local_tasks(&$variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<ul class="stack button-group">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<ul class="stack button-group">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Implements hook_node_view().
 */
function lightningdemo_preprocess_page(&$variables) {
  if (isset($variables['node'])) {
    // Get the processed workbench messages
    $messages = workbench_moderation_set_message();
    // Add a setting for the current node status
    if ($messages && $messages[0]['label'] == 'Revision state') {
      $variables['activestate'] = $messages[0]['message'];
    }
    // Maybe this node doesn't have workbench enabled
    else {
      $variables['activestate'] = $variables['node']->status == NODE_PUBLISHED ? 'Published' : 'Unpublished';
    }
  }
}
