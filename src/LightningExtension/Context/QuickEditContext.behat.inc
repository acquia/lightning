<?php

namespace Acquia\LightningExtension\Context;

use Acquia\LightningExtension\AwaitTrait;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

class QuickEditContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * The entity currently being quick edited.
   *
   * @var \Behat\Mink\Element\NodeElement
   */
  protected $target;

  /**
   * Activates Quick Edit on a specific field of the current target.
   *
   * @param string $field
   *   The machine name of the field to edit.
   *
   * @When I edit the :field field
   */
  public function quickEditField($field) {
    $this->assertQuickEditField($field)->click();
    $this->awaitAjax();
  }

  /**
   * Asserts the presence of a quick editable field.
   *
   * @param string $field
   *   The machine name of the field.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The quick editable field element.
   */
  protected function assertQuickEditField($field) {
    $selector = sprintf('[data-quickedit-field-id *= "/%s/"]', $field);

    return $this->assertSession()
      ->elementExists('css', $selector, $this->target);
  }

  /**
   * Activates Quick Edit for an entity.
   *
   * @param string $label
   *   The entity label, which must be present on the page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If the entity (identified by the presence of a data-quickedit-entity-id
   *   attribute) is not found on the page.
   *
   * @When I quick edit :label
   */
  public function startQuickEdit($label) {
    /** @var ContextualContext $contextual */
    $contextual = $this->getContext(ContextualContext::class);

    $elements = $this->getSession()
      ->getPage()
      ->findAll('named_exact', ['content', $label]);

    $elements = array_filter($elements, function (NodeElement $element) {
      return $element->getParent()->hasAttribute('data-quickedit-entity-id');
    });
    if ($elements) {
      $this->target = reset($elements)->getParent();
      $contextual->activate();
      $contextual->assertLink($this->target, 'Quick edit')->click();
    }
    else {
      throw new ExpectationException(
        'Expected to a quick editable entity called "' . $label . '" to exist.',
        $this->getSession()->getDriver()
      );
    }
  }

  /**
   * Asserts that a Quick Edit field contains text.
   *
   * @param string $field
   *   The machine name of the field.
   * @param string $expected_text
   *   The text that the field is expected to contain.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If the field does not contain the expected text.
   *
   * @Then the editable :field field should contain :expected_text
   */
  public function assertQuickEditFieldContains($field, $expected_text) {
    $actual_text = $this->assertQuickEditField($field)->getText();

    if (strpos($actual_text, $expected_text) === FALSE) {
      throw new ExpectationException(
        "Expected $field field to contain \"$expected_text\", but it does not.",
        $this->getSession()->getDriver()
      );
    }
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
