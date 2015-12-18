<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Component\Utility\Random;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Given;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Asserts that a CKEditor instance exists.
   *
   * @param string $id
   *   The editor's instance ID in CKEDITOR.instances.
   *
   * @throws \Exception if the editor does not exist.
   *
   * @Given CKEditor :id exists
   * @Then CKEditor :id should exist
   */
  public function ckeditorShouldExist($id) {
    $exists = $this->getSession()
      ->evaluateScript("CKEDITOR.instances.hasOwnProperty('$id');");

    if (! $exists) {
      throw new \Exception("CKEditor '$id' does not exist.");
    }
  }

  /**
   * Puts text or HTML into a CKEditor instance.
   *
   * @param string $text
   *   The text (or HTML) to insert into the editor.
   * @param string $id
   *   The editor's instance ID in CKEDITOR.instances.
   *
   * @When I put :text into CKEditor :id
   */
  public function iPutTextIntoCkeditor($text, $id)
  {
    $this->getSession()
      ->executeScript("CKEDITOR.instances['$id'].insertHtml('$text');");
  }

  /**
   * Asserts that a CKEditor instances's data contains a snippet of text.
   *
   * @param string $id
   *   The editor's instance ID in CKEDITOR.instances.
   * @param string $text
   *   The text (or HTML) snippet to look for.
   *
   * @throws \Exception if the editor doesn't contain the specified text.
   *
   * @Then CKEditor :id should contain :text
   */
  public function ckeditorShouldContain($id, $text) {
    $html = $this->getSession()
      ->evaluateScript("CKEDITOR.instances['$id'].getData();");

    if (strpos($html, $text) === FALSE) {
      throw new \Exception("CKEditor $id did not contain '$text''.");
    }
  }

}
