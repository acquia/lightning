<?php
/**
 * @file
 * lightning_demo.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function lightning_demo_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "page_manager" && $api == "pages_default") {
    return array("version" => "1");
  }
}
