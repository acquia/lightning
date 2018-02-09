<?php

/**
 * @file
 * Contains \PanelsSubContext.
 */

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for working with Panels and panelized pages.
 */
class PanelsSubContext extends DrupalSubContextBase {

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
   * Customizes a node view mode.
   *
   * @param string $view_mode
   *   The view mode ID.
   * @param string $node_type
   *   The node type ID.
   *
   * @When I customize the :view_mode view mode of the :node_type content type
   *
   * @Given I have customized the :view_mode view mode of the :node_type content type
   */
  public function customizeViewMode($view_mode, $node_type) {
    $this->minkContext->visit('/admin/structure/types/manage/' . $node_type . '/display');
    $this->minkContext->assertCheckBox('display_modes_custom[' . $view_mode . ']');
    $this->minkContext->pressButton('Save');
  }

  /**
   * Uncustomizes a node view mode.
   *
   * @param string $view_mode
   *   The view mode ID.
   * @param string $node_type
   *   The node type ID.
   *
   * @When I uncustomize the :view_mode view mode of the :node_type content type
   *
   * @Given I have uncustomized the :view_mode view mode of the :node_type content type
   */
  public function uncustomizeViewMode($view_mode, $node_type) {
    $this->minkContext->visit('/admin/structure/types/manage/' . $node_type . '/display');
    $this->minkContext->assertUncheckBox('display_modes_custom[' . $view_mode . ']');
    $this->minkContext->pressButton('Save');
  }

}
