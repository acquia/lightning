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
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
    $this->ckContext = $this->getContext('CkEditorSubContext');
  }

  /**
   * @When I open the CKEditor media widget
   */
  public function openCkEditorMediaWidget() {
    $this->minkContext->iWaitForAjaxToFinish();
    $this->ckContext->execute('media_library');
    $this->minkContext->iWaitForAjaxToFinish();
  }

}
