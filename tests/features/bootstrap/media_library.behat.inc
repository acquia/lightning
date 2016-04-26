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
   * The Lightning subcontext.
   *
   * @var \LightningSubContext
   */
  protected $lightning;

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
    $this->lightning = $this->getContext('LightningSubContext');
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
    $this->minkContext->selectOption('__bundle', $type);
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
    $selector = static::DIALOG_BOX_SELECTOR . ' .library ul.collection-view li:nth-child(' . $n . ')';
    $this->lightning->clickElementBySelector($selector);
  }

  /**
   * Uploads a file to the media library.
   *
   * @param string $file
   *   The file path, relative to ../../files.
   *
   * @When I upload :file to my media library
   */
  public function upload($file) {
    $session = $this->getSession();
    $selector = 'document.getElementById("__dropzone_0")';

    // Force the file upload field to be visible so we can attach the file.
    $session->executeScript($selector . '.style.position = "static"');
    $session->executeScript($selector . '.style.visibility = "visible"');
    $session->executeScript($selector . '.style.width = "auto"');
    $session->executeScript($selector . '.style.height = "auto"');
    $this->minkContext->attachFileToField('__dropzone_0', __DIR__ . '/../../files/' . $file);

    // Give the upload a second to start...
    $session->wait(1000, 'UploadView.xhr > 0');
    // ...and 10 seconds to complete.
    $session->wait(10000, 'UploadView.xhr === 0');

    $this->minkContext->iWaitForAjaxToFinish();
    $this->minkContext->assertCheckBox('Save to my media library');
  }

  /**
   * Inserts an embed code into the media widget.
   *
   * @param string $embed_code
   *   The embed code.
   *
   * @When I enter the embed code :embed_code
   */
  public function useEmbedCode($embed_code) {
    $this->minkContext->fillField('__embed_code', $embed_code);
    $this->minkContext->iWaitForAjaxToFinish();

    /** @var \EntitySubContext $context */
    $context = $this->getContext('EntitySubContext');
    // Track the created entity so that we don't pollute the test site.
    $context->trackNewest('media');
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

  /**
   * Submits the current tab of the media widget.
   *
   * @When I submit the media widget
   */
  public function submit() {
    $this->lightning->takeScreenshot('foo');
    $selector = static::DIALOG_BOX_SELECTOR . ' > .ui-tabs > div[aria-hidden = "false"] footer button';

    $this->lightning->clickElementBySelector($selector);
    $this->minkContext->iWaitForAjaxToFinish();
  }

}
