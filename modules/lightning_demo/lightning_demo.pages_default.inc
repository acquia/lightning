<?php
/**
 * @file
 * lightning_demo.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_pages().
 */
function lightning_demo_default_page_manager_pages() {
  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'lightning_home';
  $page->task = 'page';
  $page->admin_title = 'Lightning Home';
  $page->admin_description = '';
  $page->path = 'home';
  $page->access = array();
  $page->menu = array();
  $page->arguments = array();
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_lightning_home_panel_context';
  $handler->task = 'page';
  $handler->subtask = 'lightning_home';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Panel',
    'no_blocks' => 0,
    'pipeline' => 'ipe',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
  );
  $display = new panels_display();
  $display->layout = 'demonstratie_layout_landing';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'middle' => NULL,
      'primary_features' => NULL,
      'secondary_features' => NULL,
      'tertiary_features' => NULL,
      'quaternary_features' => NULL,
      'primary_supplements' => NULL,
      'secondary_supplements' => NULL,
      'tertiary_supplements' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = 'f32b8e42-3f1b-4a3a-8944-35eb56bead2f';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-df16443f-30be-497b-aa4c-4d5298cefc2b';
    $pane->panel = 'primary_features';
    $pane->type = 'fieldable_panels_pane';
    $fpid = db_select("fieldable_panels_panes", "id")->fields("id", array("fpid"))->condition("uuid", "488b6c63-7818-4759-bbc0-66fab0d0552c")->execute()->fetchField();
    $pane->subtype = 'fpid:' . $fpid;
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'view_mode' => 'full',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'title' => 'hidden',
        'hero' => 'default',
        'layouts' => 'default',
        'styles' => 'default',
        'color_schemes' => 'default',
        'button_styles' => 'default',
      ),
      'style' => 'demonstratie_panels_styles',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'df16443f-30be-497b-aa4c-4d5298cefc2b';
    $display->content['new-df16443f-30be-497b-aa4c-4d5298cefc2b'] = $pane;
    $display->panels['primary_features'][0] = 'new-df16443f-30be-497b-aa4c-4d5298cefc2b';
    $pane = new stdClass();
    $pane->pid = 'new-431ff984-2bd9-4678-bf11-ebfa61b70d3e';
    $pane->panel = 'primary_supplements';
    $pane->type = 'fieldable_panels_pane';
    $fpid = db_select("fieldable_panels_panes", "id")->fields("id", array("fpid"))->condition("uuid", "2fcfb76d-b1bc-44f4-aae9-cc747c5c2310")->execute()->fetchField();
    $pane->subtype = 'fpid:' . $fpid;
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'view_mode' => 'full',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'title' => 'hidden',
        'hero' => 'default',
        'layouts' => 'default',
        'styles' => 'default',
        'color_schemes' => 'default',
        'button_styles' => 'default',
      ),
      'style' => 'demonstratie_panels_styles',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '431ff984-2bd9-4678-bf11-ebfa61b70d3e';
    $display->content['new-431ff984-2bd9-4678-bf11-ebfa61b70d3e'] = $pane;
    $display->panels['primary_supplements'][0] = 'new-431ff984-2bd9-4678-bf11-ebfa61b70d3e';
    $pane = new stdClass();
    $pane->pid = 'new-5055dd4c-61d6-47b2-a1d3-083196d011e2';
    $pane->panel = 'quaternary_features';
    $pane->type = 'fieldable_panels_pane';
    $fpid = db_select("fieldable_panels_panes", "id")->fields("id", array("fpid"))->condition("uuid", "fa7f808f-8821-4661-8742-962d94673b90")->execute()->fetchField();
    $pane->subtype = 'fpid:' . $fpid;
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'view_mode' => 'full',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'title' => 'hidden',
        'hero' => 'default',
        'layouts' => 'default',
        'styles' => 'default',
        'color_schemes' => 'default',
        'button_styles' => 'default',
      ),
      'style' => 'demonstratie_panels_styles',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '5055dd4c-61d6-47b2-a1d3-083196d011e2';
    $display->content['new-5055dd4c-61d6-47b2-a1d3-083196d011e2'] = $pane;
    $display->panels['quaternary_features'][0] = 'new-5055dd4c-61d6-47b2-a1d3-083196d011e2';
    $pane = new stdClass();
    $pane->pid = 'new-5d585a57-825a-4a94-a67e-f0e10885298d';
    $pane->panel = 'secondary_supplements';
    $pane->type = 'fieldable_panels_pane';
    $fpid = db_select("fieldable_panels_panes", "id")->fields("id", array("fpid"))->condition("uuid", "aa75e692-20a4-4ffd-b8b0-f6b8544e76d7")->execute()->fetchField();
    $pane->subtype = 'fpid:' . $fpid;
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'view_mode' => 'full',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => array(
        'title' => 'hidden',
        'hero' => 'default',
        'layouts' => 'default',
        'styles' => 'default',
        'color_schemes' => 'default',
        'button_styles' => 'default',
      ),
      'style' => 'demonstratie_panels_styles',
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '5d585a57-825a-4a94-a67e-f0e10885298d';
    $display->content['new-5d585a57-825a-4a94-a67e-f0e10885298d'] = $pane;
    $display->panels['secondary_supplements'][0] = 'new-5d585a57-825a-4a94-a67e-f0e10885298d';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['lightning_home'] = $page;

  return $pages;

}
