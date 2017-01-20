<?php

/**
 * @file
 * Contains \PanelsSubContext.
 */

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
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
   * Changes the layout of an IPE controlled entity via the IPE.
   *
   * @param string $category
   *   The layout's category.
   * @param string $layout_id
   *   The layout's data-layout-id value.
   *
   * @When I change the layout to :layout_id from the :category category
   */
  public function changeLayout($category, $layout_id) {
    $this->lightning->clickElementBySelector('a[title = "Change Layout"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->lightning->clickElementBySelector('a[data-category = "' . $category . '"]');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->lightning->clickElementBySelector('a[data-layout-id = "' . $layout_id . '"]');
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Places a block into a panelizer layout via Wizard.
   *
   * @param string $label
   *   The text name of the block.
   * @param string $region
   *   The name of the region in which to place the block.
   *
   * @When I place the :label block into the :region panelizer region
   */
  public function placePanelizerBlock($label, $region) {
    $this->minkContext->clickLink('Add new block');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->minkContext->clickLink($label);
    $this->minkContext->iWaitForAjaxToFinish();

    $this->minkContext->selectOption('region', $region);
    $this->minkContext->iWaitForAjaxToFinish();

    $this->minkContext->pressButton('Add block');
    $this->minkContext->iWaitForAjaxToFinish();

    $this->assertPanelizerBlock($label, $region);
  }

  /**
   * Asserts that a block is present in a specific region of a Panelizer layout.
   *
   * @param string $label
   *   The block label.
   * @param string $region
   *   The machine name of the region in which the block is expected to be.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The block's row in the table.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If the block is not present as expected.
   *
   * @Then the :label block should be in the :region region
   */
  public function assertPanelizerBlock($label, $region) {
    $row = $this->getBlockRow($label, $region);
    if ($row) {
      return $row;
    }
    else {
      throw new ExpectationException("Expected block '{$label}' to be present in '{$region}' region.", $this->getSession()->getDriver());
    }
  }

  /**
   * Removes a block from the panelizer layout via the Wizard.
   *
   * Assumes that exactly one block with the given name exists in the given
   * region.
   *
   * @param string $label
   *   The label of the block to remove.
   * @param string $region
   *   The machine name of the region in which the block is currently placed.
   *
   * @When I remove the :label block from the :region panelizer region
   */
  public function removePanelizerBlock($label, $region) {
    $row = $this->assertPanelizerBlock($label, $region);

    $drop_button = $row->find('css', 'ul.dropbutton');
    $drop_button->find('css', 'li.dropbutton-toggle')->click();
    $drop_button->findLink('Delete')->click();
  }

  /**
   * Returns the table row for a specific block in a specific region.
   *
   * @param string $block_label
   *   The label of the block to locate.
   * @param string $region
   *   The machine name of the region in which the block is expected to be.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The row element, or null if one was not found.
   */
  protected function getBlockRow($block_label, $region) {
    $page = $this->getSession()->getPage();

    // array_map() callback. Traverses from a region select list to the table
    // row that contains it.
    $row_map = function (NodeElement $select) {
      return $select
        // The containing DIV.
        ->getParent()
        // The table cell.
        ->getParent()
        // The table row.
        ->getParent();
    };

    $elements = $page->findAll('css', 'table#blocks tr > td > div > select.block-region-select');
    $elements = array_filter($elements, function (NodeElement $element) use ($region) {
      return $element->getValue() == $region;
    });

    /** @var NodeElement $row */
    foreach (array_map($row_map, $elements) as $row) {
      // The first cell is the one with the label; find() will return the first
      // matched element, which should be the first cell.
      $row_label = $row->find('css', 'td')->getText();
      if (trim($row_label) == $block_label) {
        return $row;
      }
    }
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
   * Asserts that a block with a specific plugin ID is NOT present.
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
