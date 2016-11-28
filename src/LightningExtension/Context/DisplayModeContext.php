<?php

namespace Acquia\LightningExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * A context for working with entity display modes.
 */
class DisplayModeContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var MinkContext
   */
  protected $minkContext;

  /**
   * Gathers required contexts.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext(MinkContext::class);
  }

  /**
   * Sets a description on an entity view mode.
   *
   * @param string $id
   *   The view mode ID.
   * @param \Behat\Gherkin\Node\PyStringNode $description
   *   The view mode description.
   *
   * @When I describe the :id view mode:
   */
  public function describeViewMode($id, PyStringNode $description) {
    $this->visitPath('/admin/structure/display-modes/view/manage/' . $id);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $original = new PyStringNode(
        (array) $this->assertSession()->fieldExists('Description')->getValue(),
        0
      );
      $undo->push([$this, __FUNCTION__], [$id, $original]);
    }

    $this->minkContext->fillField('Description', (string) $description);
    $this->minkContext->pressButton('Save');
  }

}
