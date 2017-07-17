<?php

namespace Acquia\LightningExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

class QuickEditContext extends DrupalSubContextBase {

  protected $currentTarget;

  /**
   * @param $field
   *
   * @When I edit the :field field
   */
  public function quickEditField($field) {
    $this->assertSession()
      ->elementExists('css', '[data-quickedit-field-id*="/' . $field . '/"]', $this->currentTarget)
      ->click();

    /** @var MinkContext $mink_context */
    $mink_context = $this->getContext(MinkContext::class);
    if ($mink_context) {
      $mink_context->iWaitForAjaxToFinish();
    }
  }

  /**
   * @param $label
   *
   * @When I quick edit :label
   */
  public function quickEditEntity($label) {
    $elements = $this->getSession()
      ->getPage()
      ->findAll('named_exact', ['content', $label]);

    $this->currentTarget = end($elements)->getParent();

    $assert = $this->assertSession();

    $contextual = $assert->elementExists('css', '.contextual', $this->currentTarget);

    $assert->elementExists('css', 'button', $contextual)->press();

    $assert
      ->elementExists(
        'named',
        ['link', 'Quick edit'],
        $assert->elementExists('css', 'ul.contextual-links', $contextual)
      )
      ->click();
  }

  /**
   * @param $value
   *
   * @Then the :field field should contain :value
   */
  public function assertQuickEditFieldContains($field, $value) {
    $element = $this->assertSession()
      ->elementExists('css', '[data-quickedit-field-id*="/' . $field . '/"]', $this->currentTarget);

    if ($element->getText() != $value) {
      throw new ExpectationException(
        'Expected field ' . $field . ' to contain "' . $value . '", but it does not.',
        $this->getSession()->getDriver()
      );
    }
  }

  /**
   * @When I enter edit mode
   */
  public function enterEditMode() {
    $this->assertSession()
      ->elementExists('css', '#toolbar-bar .contextual-toolbar-tab button')
      ->click();

    sleep(1);
  }

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
