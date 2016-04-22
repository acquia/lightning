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
   * Content type IDs that have been panelized during the scenario.
   *
   * @var string[]
   */
  protected $panelized = [];

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Post-scenario hook.
   *
   * @AfterScenario
   */
  public function cleanPanelizer() {
    array_walk($this->panelized, [$this, 'unpanelize']);
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

    array_push($this->panelized, $bundle);
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

    $index = array_search($bundle, $this->panelized);
    if ($index !== FALSE) {
      array_splice($this->panelized, $index, 1);
    }
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
    /** @var \LightningSubContext $context */
    $context = $this->getContext('LightningSubContext');

    $context->clickElementBySelector('a[title = "Place Content"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $context->clickElementBySelector('a[data-category = "' . $category . '"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $context->clickElementBySelector('a[data-plugin-id = "' . $plugin_id . '"]');
    $this->minkContext->iWaitForAjaxToFinish();
  }

}
