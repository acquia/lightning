<?php

namespace Acquia\LightningExtension;

use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Exception\ExpectationException;

/**
 * Provides helper methods for interacting with <details> elements.
 *
 * @internal
 *   This is an internal part of Lightning's testing system and may be changed
 *   or removed at any time without warning. External code should not extend,
 *   instantiate, or rely on this class in any way! If you'd like to use any of
 *   these step definitions in your project, you should copy them into your own
 *   project.
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
