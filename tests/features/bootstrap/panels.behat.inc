<?php

/**
 * @file
 * Contains \PanelsSubContext.
 */

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for working with Panels and panelized pages.
 *
 * @internal
 * This class is part of Lightning's internal testing code. It is not an API and
 * should not be extended. This class will be marked final, and all protected
 * members will be made private, in Lightning 4.x.
 */
class PanelsSubContext extends DrupalSubContextBase {

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
    $assert = $this->assertSession();
    $assert->addressMatches("/admin\/structure\/types\/manage\/$bundle\/display$/");
    $assert->fieldExists('panelizer[enable]')->check();
    $assert->fieldExists('panelizer[custom]')->check();
    $assert->elementExists('named', ['button', 'Save'])->press();
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
    $assert = $this->assertSession();
    $assert->addressMatches("/admin\/structure\/types\/manage\/$bundle\/display$/");
    $assert->fieldExists('panelizer[enable]')->uncheck();
    $assert->fieldExists('panelizer[custom]')->uncheck();
    $assert->elementExists('named', ['button', 'Save'])->press();
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
    $this->visitPath("/admin/structure/types/manage/$node_type/display");

    $assert = $this->assertSession();
    $assert->fieldExists('display_modes_custom[' . $view_mode . ']')->check();
    $assert->elementExists('named', ['button', 'Save'])->press();
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
    $this->visitPath("/admin/structure/types/manage/$node_type/display");

    $assert = $this->assertSession();
    $assert->fieldExists('display_modes_custom[' . $view_mode . ']')->uncheck();
    $assert->elementExists('named', ['button', 'Save'])->press();
  }

}
