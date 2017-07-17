<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

class ContextualContext extends DrupalSubContextBase {

  /**
   * Shows all contextual links by pressing the 'Edit' button in the toolbar.
   *
   * @When I show all contextual links
   */
  public function activateContextual() {
    $assert = $this->assertSession();

    $button = $assert->elementExists(
      'named',
      ['button', 'Edit'],
      $assert->elementExists('css', '#toolbar-bar .contextual-toolbar-tab')
    );

    if ($button->getAttribute('aria-pressed') == 'false') {
      $button->press();
    }
  }

}
