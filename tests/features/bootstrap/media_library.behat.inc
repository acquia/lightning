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

  /**
   * Filters the media library to a specific media type.
   *
   * @param string $type
   *   The media type to filter.
   *
   * @When I filter the media library by type :type
   */
  public function filterLibraryByType($type) {
    $this->minkContext->selectOption('lightning-media-bundle', $type);
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Selects an asset from the media library.
   *
   * @param int $n
   *   The one-based index of the asset.
   *
   * @When /^I select the (\d+)(?:st|nd|rd|th) asset in the media library$/
   */
  public function selectMediaLibraryAsset($n) {
    /** @var \LightningSubContext $context */
    $context = $this->getContext('LightningSubContext');
    $context->clickElementBySelector('.media-library .library ul.collection-view li:nth-child(' . $n . ')');
  }

  /**
   * Asserts the presence of the customized Entity Embed dialog box.
   *
   * @Then I should see the Entity Embed form
   */
  public function assertEntityEmbedDialog() {
    $assert = $this->assertSession();
    $assert->elementExists('css', 'form.entity-embed-dialog');
    // The Back button and display plugin selection should be hidden.
    $assert->elementNotExists('css', 'form.entity-embed-dialog input[type = "submit"][value = "Back"]');
    $assert->elementNotExists('css', 'form.entity-embed-dialog select[name = "attributes[data-entity-embed-display]"]');
  }

}
