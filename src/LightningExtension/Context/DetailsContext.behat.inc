<?php

namespace Acquia\LightningExtension\Context;

use Acquia\LightningExtension\DetailsTrait;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for working with collapsible <details> elements.
 */
class DetailsContext extends DrupalSubContextBase {

  use DetailsTrait {
    assertDetails as doAssertDetails;
  }

  /**
   * Asserts the existence of a details element by its summary text.
   *
   * @param string $summary
   *   The exact summary text.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element in which to search for the details element.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The details element.
   *
   * @throws \Behat\Mink\Exception\ExpectationException if the element is not
   * found in the container.
   *
   * @Then I should see a(n) :summary details element
   */
  public function assertDetails($summary, NodeElement $container = NULL) {
    $container = $container ?: $this->getSession()->getPage();
    return $this->doAssertDetails($summary, $container);
  }

  /**
   * Asserts the existence of an open details element by its summary text.
   *
   * @param string $summary
   *   The exact summary text.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element in which to search for the details element.
   *
   * @throws \Behat\Mink\Exception\ExpectationException if the details element
   * is closed.
   *
   * @Then I should see an open :summary details element
   */
  public function assertOpenDetails($summary, NodeElement $container = NULL) {
    $attr = $this->assertDetails($summary, $container)->hasAttribute('open');

    if ($attr == FALSE) {
      throw new ExpectationException(
        "Expected '$summary' details to be open, but it is closed.",
        $this->getSession()->getDriver()
      );
    }
  }

  /**
   * Asserts the existence of a closed details element by its summary text.
   *
   * @param string $summary
   *   The exact summary text.
   * @param \Behat\Mink\Element\NodeElement $container
   *   (optional) The element in which to search for the details element.
   *
   * @throws \Behat\Mink\Exception\ExpectationException if the details element
   * is open.
   *
   * @Then I should see a closed :summary details element
   */
  public function assertClosedDetails($summary, NodeElement $container = NULL) {
    $attr = $this->assertDetails($summary, $container)->hasAttribute('open');

    if ($attr) {
      throw new ExpectationException(
        "Expected '$summary' details to be closed, but it is open.",
        $this->getSession()->getDriver()
      );
    }
  }

}
