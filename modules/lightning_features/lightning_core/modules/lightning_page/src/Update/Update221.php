<?php

namespace Drupal\lightning_page\Update;

/**
 * Executes interactive update steps for Lightning Page 2.2.1.
 *
 * @Update("2.2.1")
 */
final class Update221 {

  /**
   * @update
   *
   * @ask Would you like to hide the "Publishing status" checkbox on the Page
   * content type form?
   */
  public function hidePublishingStatus() {
    entity_get_form_display('node', 'page', 'default')
      ->removeComponent('status')
      ->save();
  }

}
