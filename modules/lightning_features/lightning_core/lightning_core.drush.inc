<?php

/**
 * @file
 * Drush integration for Lightning.
 */

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_lightning_core_pm_updatecode_validate() {
  $lightning_path = \Drupal::moduleHandler()->getModule('lightning')->getPath();

  $question = dt('Updating code with Drush can potentially break your Lightning site. (See @path for safe update instructions.) Are you SURE you want to continue?', [
    '@path' => \Drupal::root() . "/$lightning_path/UPDATE.md",
  ]);
  return drush_confirm($question);
}
