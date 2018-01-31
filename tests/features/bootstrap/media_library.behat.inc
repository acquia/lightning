<?php

/**
 * @file
 * Contains \MediaLibrarySubContext.
 */

use Acquia\LightningExtension\AwaitTrait;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for testing the CKEditor media widget.
 */
class MediaLibrarySubContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Waits for a JavaScript condition to become truthy.
   *
   * @param string $expression
   *   The JavaScript expression to wait for.
   */
  protected function awaitExpression($expression) {
    $this->getSession()->wait(10000, $expression);
  }

  /**
   * Waits for an element to exist.
   *
   * @param string $selector
   *   The element's CSS selector.
   * @param int $timeout
   *   How many seconds to wait before timing out.
   */
  protected function awaitElement($selector, $timeout = 10) {
    $this->getSession()
      ->wait($timeout * 1000, 'document.querySelector("' . addslashes($selector) . '")');
  }

  /**
   * Waits for the inline entity form (containing required fields) to exist.
   */
  protected function awaitEntityForm() {
    $this->awaitAjax();
    $this->awaitExpression('jQuery("#entity").children().length');
  }

}
