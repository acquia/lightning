<?php

namespace Acquia\LightningExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

class QuickEditContext extends DrupalSubContextBase {

  /**
   * Asserts that Quick Edit is enabled for at least one entity on the page.
   *
   * @Then Quick Edit should be enabled
   */
  public function assertQuickEditEnabled() {
    $session = $this->getSession();

    $victory = $session->wait(10000, 'Drupal.quickedit.collections.entities.length > 0');

    if (empty($victory)) {
      throw new ExpectationException('Expected Quick Edit to be enabled, but it is not.', $session->getDriver());
    }
  }

  /**
   * Asserts that Quick Edit is not enabled for any entities on the page.
   *
   * @Then Quick Edit should be disabled
   */
  public function assertQuickEditDisabled() {
    $session = $this->getSession();

    $victory = $session->wait(10000, 'Drupal.quickedit.collections.entities.length === 0');

    if (empty($victory)) {
      throw new ExpectationException('Expected Quick Edit to be disabled, but it is not.', $session->getDriver());
    }
  }

}
