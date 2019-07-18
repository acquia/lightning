<?php

namespace Acquia\LightningExtension;

@trigger_error(__NAMESPACE__ . '\DetailsTrait is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Exception\ExpectationException;

/**
 * Contains methods for interacting with <details> elements.
 */
trait DetailsTrait {

  /**
   * Asserts the existence of a details element by its summary text.
   *
   * @param string $summary
   *   The exact summary text.
   * @param \Behat\Mink\Element\ElementInterface $container
   *   The element in which to search for the details element.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The details element.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If the element is not found in the container.
   */
  protected function assertDetails($summary, ElementInterface $container) {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

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
