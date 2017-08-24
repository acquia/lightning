<?php

namespace Acquia\LightningExtension;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;

trait DetailsTrait {

  /**
   * Asserts the existence of a details element by its summary text.
   *
   * @param string $summary
   *   The exact summary text.
   * @param \Behat\Mink\Element\NodeElement $container
   *   The element in which to search for the details element.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The details element.
   *
   * @throws \Behat\Mink\Exception\ExpectationException if the element is not
   * found in the container.
   *
   * @return \Behat\Mink\Element\NodeElement
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function assertDetails($summary, NodeElement $container) {
    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($container->findAll('css', 'details > summary') as $element) {
      if ($element->getText() == $summary) {
        return $element->getParent();
      }
    }

    throw new ExpectationException(
      "Could not find a details element with summary '$summary'.",
      $this->getSession()->getDriver()
    );
  }

}
