<?php

namespace Acquia\LightningExtension\Context;

@trigger_error(__NAMESPACE__ . '\PanelsInPlaceContext is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

use Behat\Mink\Driver\Selenium2Driver;
use DMore\ChromeDriver\ChromeDriver;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * A context for working with the Panels in-place editor.
 */
class PanelsInPlaceContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * Returns the active Panels IPE tab's contents.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The active tab's contents.
   */
  protected function getActiveTab() {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    return $this->assertSession()
      ->elementExists('css', '.ipe-tabs-content', $this->assertTray());
  }

  /**
   * Asserts that a particular block plugin is not available.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $category
   *   (optional) The category to open.
   *
   * @Then I should not see the :plugin_id plugin
   * @Then I should not see the :plugin_id plugin in the :category category
   */
  public function assertBlockPluginNotExists($plugin_id, $category = NULL) {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    $this->assertSession()
      ->elementNotExists(
        'css',
        '.ipe-block-plugin a[data-plugin-id="' . $plugin_id . '"]',
        $category ? $this->openCategory($category) : $this->getActiveTab()
      );
  }

  /**
   * Reverts a panelized layout to its default state.
   *
   * @When I revert the layout
   */
  public function revertLayout() {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    $tray = $this->getContext(PanelsIPEContext::class)->assertTray();

    $link = $this->assertSession()
      ->elementExists('named', ['link', 'Revert to default'], $tray);

    $session = $this->getSession();
    $driver = $session->getDriver();

    if ($driver instanceof ChromeDriver) {
      $link = addslashes($link->getXpath());
      $session->executeScript("document.evaluate('$link', document, null).iterateNext().click();");
      $driver->acceptAlert();
    }
    else {
      $link->click();

      if ($driver instanceof Selenium2Driver) {
        $driver->getWebDriverSession()->accept_alert();
      }
    }

    $this->awaitAjax();
  }

}
