<?php

/**
 * @file
 * Contains \PanelsSubContext.
 */

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Contains step definitions for working with Panels and panelized pages.
 */
class PanelsSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * The Lightning subcontext.
   *
   * @var \LightningSubContext
   */
  protected $lightning;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
    $this->lightning = $this->getContext('LightningSubContext');
  }

  /**
   * Applies Panelizer to a node type.
   *
   * @param string $bundle
   *   The node type ID.
   *
   * @Given I have applied Panelizer to the :bundle node type
   * @Given I have panelized the :bundle node type
   *
   * @When I apply Panelizer to the :bundle node type
   * @When I panelize the :bundle node type
   */
  public function panelize($bundle) {
    $this->minkContext->assertAtPath("admin/structure/types/manage/$bundle/display");
    $this->minkContext->checkOption('panelizer[enable]');
    $this->minkContext->checkOption('panelizer[custom]');
    $this->minkContext->pressButton('Save');
  }

  /**
   * Removes Panelizer from a node type.
   *
   * @param string $bundle
   *   The node type ID.
   *
   * @Given I have removed Panelizer from the :bundle node type
   * @Given I have unpanelized the :bundle node type
   *
   * @When I remove Panelizer from the :bundle node type
   * @When I unpanelize the :bundle node type
   */
  public function unpanelize($bundle) {
    $this->minkContext->assertAtPath("admin/structure/types/manage/$bundle/display");
    $this->minkContext->uncheckOption('panelizer[enable]');
    $this->minkContext->uncheckOption('panelizer[custom]');
    $this->minkContext->pressButton('Save');
  }

  /**
   * Places a block into a Panels layout.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $category
   *   The block's category.
   *
   * @When I place the :plugin_id block from the :category category
   */
  public function placeBlock($plugin_id, $category) {
    $this->lightning->clickElementBySelector('a[title = "Place Content"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->lightning->clickElementBySelector('a[data-category = "' . $category . '"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->lightning->clickElementBySelector('a[data-plugin-id = "' . $plugin_id . '"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->lightning->clickElementBySelector('.ipe-block-plugin-form .js-form-submit');
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Saves the current Panels layout as a custom layout.
   *
   * @When I save the layout
   */
  public function saveCustomLayout() {
    $this->lightning->clickElementBySelector('a[title = "Save"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->lightning->clickElementBySelector('a.panelizer-ipe-save-custom');
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Asserts that a block with a specific plugin ID is present.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   *
   * @Then I should see a :plugin_id block
   */
  public function assertBlock($plugin_id) {
    $this->assertSession()->elementExists('css', 'div[data-block-plugin-id = "' . $plugin_id . '"]');
  }

  /**
   * Asserts that a block with a specific plugin ID is not present.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   *
   * @Then I should not see a :plugin_id block
   */
  public function assertNotBlock($plugin_id) {
    $this->assertSession()->elementNotExists('css', 'div[data-block-plugin-id = "' . $plugin_id . '"]');
  }

  /**
   * Asserts that a block has contextual links.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $link_class
   *   (optional) The class for a specific contextual link to assert.
   *
   * @Then the :plugin_id block should have contextual links
   * @Then the :plugin_id block should have a :link_class contextual link
   * @Then I should see a :plugin_id block with contextual links
   * @Then I should see a :plugin_id block with a :link_class contextual link
   */
  public function assertBlockContextualLinks($plugin_id, $link_class = NULL) {
    $selector = 'div[data-block-plugin-id = "' . $plugin_id . '"] ul.contextual-links';
    if ($link_class) {
      $selector .= ' li.' . $link_class;
    }
    $this->assertSession()->elementExists('css', $selector);
  }

}
