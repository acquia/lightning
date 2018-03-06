<?php

namespace Acquia\LightningExtension\Context;

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
    $tray = $this->getContext(PanelsIPEContext::class)->assertTray();

    $this->assertSession()
      ->elementExists('named', ['link', 'Revert to default'], $tray)
      ->click();

    /** @var \Acquia\LightningExtension\Context\UtilityContext $utility */
    $utility = $this->getContext(UtilityContext::class);
    $utility->acceptAlerts(function () {
      $this->awaitAjax();
    });
  }

}
