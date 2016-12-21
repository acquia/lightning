<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * A context with miscellaneous helpers.
 */
class UtilityContext extends DrupalSubContextBase {

  /**
   * Asserts that a form field is not present.
   *
   * @param string $field
   *   The field locator.
   *
   * @Then I should not see a :field field
   */
  public function assertFieldNotExists($field) {
    $this->assertSession()->fieldNotExists($field);
  }

}
