<?php

namespace Acquia\LightningExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * A context for working with the Panels in-place editor.
 */
class PanelsInPlaceContext extends DrupalSubContextBase {

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
   * Returns the active Panels IPE tab's contents.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The active tab's contents.
   */
  protected function getActiveTab() {
    return $this->assertSession()
      ->elementExists('css', '.ipe-tabs-content', $this->assertTray());
  }

  /**
   * Asserts the presence of the Panels IPE tray.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The Panels IPE tray element.
   *
   * @Then I should see the Panels IPE tray
   */
  public function assertTray() {
    return $this->assertSession()->elementExists('css', '#panels-ipe-tray');
  }

  /**
   * Opens a tab of the Panels IPE tray.
   *
   * @param string $tab
   *   The title of the tab to activate.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The tab contents.
   *
   * @When I open the :tab tab
   */
  public function openTab($tab) {
    $assert = $this->assertSession();

    // Assert that the tab exists...
    $link = $assert->elementExists(
      'named',
      ['link', $tab],
      $assert->elementExists('css', '.ipe-tabs', $this->assertTray())
    );

    // ...but only open it if not already active.
    if ($link->getParent()->hasClass('active') == FALSE) {
      $link->click();
      $this->minkContext->iWaitForAjaxToFinish();
    }

    return $this->getActiveTab();
  }

  /**
   * Opens a particular block category of the "Manage Content" tab.
   *
   * @param string $category
   *   The category to open.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The tab contents.
   *
   * @When I open the :category category
   */
  public function openCategory($category) {
    $tab = $this->openTab('Manage Content');

    // Assert that the category exists...
    $link = $this->assertSession()
      ->elementExists('css',
        '.ipe-category[data-category="' . $category . '"]',
        $tab
      );

    // ...but only open it if not already active.
    if ($link->hasClass('active') == FALSE) {
      $link->click();
      $this->minkContext->iWaitForAjaxToFinish();
    }

    return $tab;
  }

  /**
   * Asserts that a particular block plugin is available.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $category
   *   (optional) The category to open.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The link to instantiate the block plugin.
   *
   * @Then I should see the :plugin_id plugin
   * @Then I should see the :plugin_id plugin in the :category category
   */
  public function assertBlockPluginExists($plugin_id, $category = NULL) {
    return $this->assertSession()
      ->elementExists(
        'css',
        '.ipe-block-plugin a[data-plugin-id="' . $plugin_id . '"]',
        $category ? $this->openCategory($category) : $this->getActiveTab()
      );
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
    $this->assertSession()
      ->elementNotExists(
        'css',
        '.ipe-block-plugin a[data-plugin-id="' . $plugin_id . '"]',
        $category ? $this->openCategory($category) : $this->getActiveTab()
      );
  }

  /**
   * Instantiates a block plugin.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $category
   *   (optional) The category in which the block plugin resides.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The block plugin configuration form.
   *
   * @When I instantiate the :plugin_id block
   * @When I instantiate the :plugin_id block from the :category category
   */
  public function instantiateBlock($plugin_id, $category = NULL) {
    $this->assertBlockPluginExists($plugin_id, $category)
      ->click();

    $this->minkContext->iWaitForAjaxToFinish();

    return $this->assertSession()
      ->elementExists('css', '.panels-ipe-block-plugin-form', $this->getActiveTab());
  }

  /**
   * Saves the current IPE layout as a custom layout.
   *
   * @When I save the layout
   */
  public function saveLayout() {
    $this->assertSession()
      ->elementExists(
        'named',
        ['link', 'Save'],
        $this->assertTray()
      )
      ->click();

    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Places a block into a Panels IPE layout.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $category
   *   (optional) The category in which the block plugin resides.
   *
   * @When I place the :plugin_id block from the :category category
   * @When I place the :plugin_id block
   */
  public function placeBlock($plugin_id, $category = NULL) {
    $this->instantiateBlock($plugin_id, $category)
      ->pressButton('Add');

    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Reverts a panelized layout to its default state.
   *
   * @When I revert the layout
   */
  public function revertLayout() {
    $this->assertSession()
      ->elementExists('named', ['link', 'Revert to default'], $this->assertTray())
      ->click();

    /** @var \Acquia\LightningExtension\Context\UtilityContext $utility */
    $utility = $this->getContext(UtilityContext::class);
    $utility->acceptAlerts([$this->minkContext, 'iWaitForAjaxToFinish']);
  }

}
