<?php

/**
 * @file
 * Contains \MediaLibrarySubContext.
 */

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Contains step definitions for testing the CKEditor media widget.
 */
class MediaLibrarySubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * The CKEditor subcontext.
   *
   * @var \CkEditorSubContext
   */
  protected $ckContext;

  /**
   * Selector targeting the jQuery UI dialog box.
   */
  const DIALOG_BOX_SELECTOR = 'body > .ui-dialog';

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
    $this->ckContext = $this->getContext('CkEditorSubContext');
  }

  /**
   * Opens the CKEditor media widget.
   *
   * @When I open the CKEditor media widget
   */
  public function openCkEditorMediaWidget() {
    $this->minkContext->iWaitForAjaxToFinish();
    $this->ckContext->execute('media_library');
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Asserts that a jQuery UI dialog box is present.
   *
   * @param string $expected_title
   *   (optional) The expected title of the dialog box.
   *
   * @throws \UnexpectedValueException
   *   If the dialog box's expected title does not match its actual title.
   *
   * @Then I should see a dialog box
   * @Then I should see a dialog box titled :expected_title
   * @Then I should see a dialog box entitled :expected_title
   */
  public function assertDialogBox($expected_title = NULL) {
    $this->assertSession()->elementExists('css', static::DIALOG_BOX_SELECTOR);

    if ($expected_title) {
      $actual_title = $this->getSession()
        ->evaluateScript('jQuery("' . static::DIALOG_BOX_SELECTOR . '").children().eq(1).dialog("option", "title")');

      if ($actual_title != $expected_title) {
        throw new \UnexpectedValueException("Expected dialog box to be titled '$expected_title' but it was '$actual_title'.");
      }
    }
  }

}
