<?php

namespace Acquia\LightningExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\NodeElement;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for working with vertical tabs.
 */
class VerticalTabsContext extends DrupalSubContextBase {

  /**
   * Asserts that vertical tab set exists.
   *
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element containing the vertical tab set.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The vertical tab set.
   *
   * @Then I should see a set of vertical tabs
   */
  public function assertVerticalTabSet(NodeElement $container = NULL) {
    return $this
      ->assertSession()
      ->elementExists('css', 'ul.vertical-tabs__menu', $container);
  }

  /**
   * Asserts that a specific vertical tab exists.
   *
   * @param string $tab
   *   The text of the tab.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element containing the vertical tab set.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The vertical tab.
   *
   * @Then I should see a :tab vertical tab
   * @Then I should see the :tab vertical tab
   */
  public function assertVerticalTab($tab, NodeElement $container = NULL) {
    return $this
      ->assertSession()
      ->elementExists(
        'named',
        ['link', $tab],
        $this->assertVerticalTabSet($container)
      );
  }

  /**
   * Asserts that a specific vertical tab does not exist.
   *
   * @param string $tab
   *   The text of the tab.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element containing the vertical tab set.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The vertical tab.
   *
   * @Then I should not see a :tab vertical tab
   * @Then I should not see the :tab vertical tab
   */
  public function assertNoVerticalTab($tab, NodeElement $container = NULL) {
    $this
      ->assertSession()
      ->elementNotExists(
        'named',
        ['link', $tab],
        $this->assertVerticalTabSet($container)
      );
  }

  /**
   * Asserts that a set of specific vertical tabs exists.
   *
   * @param \Behat\Gherkin\Node\PyStringNode $tabs
   *   The text of the tabs, one per line.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element containing the vertical tab set.
   *
   * @Then I should see (the )vertical tabs:
   */
  public function assertVerticalTabList(PyStringNode $tabs, NodeElement $container = NULL) {
    foreach ($tabs->getStrings() as $tab) {
      $this->assertVerticalTab($tab, $container);
    }
  }

  /**
   * Asserts that a set of specific vertical tabs exists.
   *
   * @param \Behat\Gherkin\Node\PyStringNode $tabs
   *   The text of the tabs, one per line.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element containing the vertical tab set.
   *
   * @Then I should not see (the )vertical tabs:
   */
  public function assertNoVerticalTabList(PyStringNode $tabs, NodeElement $container = NULL) {
    foreach ($tabs->getStrings() as $tab) {
      $this->assertNoVerticalTab($tab, $container);
    }
  }

}
