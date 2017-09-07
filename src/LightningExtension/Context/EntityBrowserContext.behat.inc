<?php

namespace Acquia\LightningExtension\Context;

use Acquia\LightningExtension\AwaitTrait;
use Acquia\LightningExtension\DetailsTrait;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Drupal\Component\Utility\Html;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for working with entity browsers.
 */
class EntityBrowserContext extends DrupalSubContextBase {

  use AwaitTrait;
  use DetailsTrait;

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
    $this->awaitAjax();
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

    $this
      ->assertDetails($field, $session->getPage())
      ->pressButton('Select Image(s)');

    $this->awaitAjax();
    $session->switchToIFrame('entity_browser_iframe_image_browser');
    // This might be vestigial.
    sleep(10);
  }

  /**
   * Gets all items in an entity browser.
   *
   * @param string $browser_id
   *   (optional) The entity browser ID.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   An array of items in the entity browser.
   */
  protected function getEntityBrowserItems($browser_id = NULL) {
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

    return $items;
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
    $items = $this->getEntityBrowserItems($browser_id);
    $items[$n - 1]->click();
  }

  /**
   * Asserts a certain number of items are visible in the entity browser.
   *
   * @param int $n
   *   The number of media items that should be visible.
   * @param string $browser_id
   *   (optional) The entity browser ID.
   *
   * @throws ExpectationException
   *   If actual number of in the entity browser does not match the expected.
   *
   * @Then I should see :n item(s) in the entity browser
   */
  public function assertNumberVisible($n, $browser_id = NULL) {
    $items = $this->getEntityBrowserItems($browser_id);
    $actual = count($items);
    if ($actual !== (int) $n) {
      throw new ExpectationException(
        'Expected entity browser to contain ' . $n . ' items, ' . $actual . ' items found.',
        $this->getSession()->getDriver()
      );
    }
  }

}
