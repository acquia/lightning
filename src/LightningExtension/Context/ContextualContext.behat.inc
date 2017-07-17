<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

class ContextualContext extends DrupalSubContextBase {

  /**
   * Shows all contextual links by pressing the 'Edit' button in the toolbar.
   *
   * @When I show all contextual links
   */
  public function activate() {
    $assert = $this->assertSession();

    $button = $assert->elementExists(
      'named',
      ['button', 'Edit'],
      $assert->elementExists('css', '#toolbar-bar .contextual-toolbar-tab')
    );

    if ($button->getAttribute('aria-pressed') == 'false') {
      $button->press();
    }
  }

  /**
   * Asserts that an element has a contextual area.
   *
   * @param \Behat\Mink\Element\NodeElement|string $element
   *   The element which contains the contextual area, or its CSS selector.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The contextual area element.
   */
  public function assertArea($element) {
    $assert = $this->assertSession();

    if (is_string($element)) {
      $element = $assert->elementExists('css', $element);
    }
    return $assert->elementExists('css', '[data-contextual-id]', $element);
  }

  /**
   * Activates the contextual links for an element.
   *
   * @param \Behat\Mink\Element\NodeElement|string $element
   *   The element which contains the contextual area, or its CSS selector.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The list of contextual links.
   */
  public function assertLinks($element) {
    $contextual = $this->assertArea($element);

    $assert = $this->assertSession();

    $button = $assert->elementExists('css', 'button.trigger', $contextual);

    if ($button->getAttribute('aria-pressed') == 'false') {
      $button->press();
    }
    return $assert->elementExists('css', '.contextual-links', $contextual);
  }

  /**
   * Asserts the presence of a contextual link.
   *
   * @param \Behat\Mink\Element\NodeElement|string $element
   *   The element which contains the contextual area, or its CSS selector.
   * @param string $link
   *   The contextual link text.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The contextual link element.
   */
  public function assertLink($element, $link) {
    return $this->assertSession()
      ->elementExists(
        'named',
        ['link', $link],
        $this->assertLinks($element)
      );
  }

}
