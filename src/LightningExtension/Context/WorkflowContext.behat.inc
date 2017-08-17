<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

class WorkflowContext extends DrupalSubContextBase {

  /**
   * Publishes the page.
   *
   * @When I publish the page
   */
  public function publishContent() {
    $this->getContext(UtilityContext::class)->editContent();

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Moderation state', 'published');
    $page->pressButton('Save');
  }

  /**
   * Creates forward revisions of the current content.
   *
   * @param string $moderation_state
   *   The moderation state that the forward revision(s) should have.
   * @param int $n
   *   How many forward revisions to create. Defaults to 1.
   *
   * @When I create a(n) :moderation_state revision
   * @When I create :n :moderation_state revisions
   */
  public function createForwardRevisions($moderation_state, $n = 1) {
    $page = $this->getSession()->getPage();

    for ($i = 0; $i < $n; $i++) {
      $this->getContext(UtilityContext::class)->editContent();
      $page->selectFieldOption('Moderation state', $moderation_state);
      $page->pressButton('Save');
    }
  }

}
