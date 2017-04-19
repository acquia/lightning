<?php

/**
 * Force-reloads all modules to avoid Search API collision with Panelizer.
 */
function lightning_layout_post_update_reload_all_modules_001() {
  \Drupal::moduleHandler()->reload();
}
