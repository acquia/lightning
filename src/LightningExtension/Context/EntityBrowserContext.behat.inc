<?php

namespace Acquia\LightningExtension\Context;

use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\Component\Utility\Html;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Contains step definitions for working with entity browsers.
 */
class EntityBrowserContext extends DrupalSubContextBase {

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
   * Submits the entity browser.
   *
   * @When I submit the entity browser
   */
  public function submit() {
    $session = $this->getSession();

    // The entity browser frame will be destroyed, so we need to switch into
    // the main window and reach into the frame to submit the form. Ugh.
    $frame = $session->evaluateScript('frameElement.name');
    $session->switchToWindow();
    // @TODO: Make this smarter, because we can't be sure that #edit-submit
    // exists at all, or that it's the correct submit button.
    $session->executeScript('frames["' . $frame . '"].document.forms[0].querySelector("#edit-submit").click()');
    sleep(10);
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Opens an image browser for a particular field.
   *
   * @param string $field
   *   The field label.
   *
   * @throws ElementNotFoundException
   *   If the collapsible field element does not exist on the page.
   *
   * @When I open the :field image browser
   */
  public function openImageBrowser($field) {
    $session = $this->getSession();

    /** @var UtilityContext $context */
    $context = $this->getContext(UtilityContext::class);

    $details = $context->findCollapsible($field);
    if ($details) {
      $details->pressButton('Select Image(s)');
      $this->minkContext->iWaitForAjaxToFinish();
      $session->switchToIFrame('entity_browser_iframe_image_browser');
      // This might be vestigial.
      sleep(10);
    }
    else {
      throw new ElementNotFoundException($session->getDriver(), 'collapsible element');
    }
  }

  /**
   * Selects an item in an entity browser view.
   *
   * @param int $n
   *   The one-based index of the item to select.
   * @param string $browser_id
   *   (optional) The entity browser ID.
   *
   * @When I select item :n
   * @When I select item :n from the entity browser
   * @When I select item :n from the :browser_id entity browser
   */
  public function selectItem($n, $browser_id = NULL) {
    if ($browser_id) {
      $selector = 'form#entity-browser-' . Html::cleanCssIdentifier($browser_id) . '-form';
    }
    else {
      $selector = 'form[data-entity-browser-uuid]';
    }

    /** @var \Behat\Mink\Element\NodeElement[] $items */
    $items = $this
      ->assertSession()
      ->elementExists('css', $selector)
      ->findAll('css', '[data-selectable]');

    $items[$n - 1]->click();
  }

}
