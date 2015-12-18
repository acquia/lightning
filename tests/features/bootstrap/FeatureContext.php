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
   * Fills in WYSIWYG editor with specified ID.
   *
   * @param string $text
   *   The text (or HTML) to insert into the WYSIWYG.
   * @param string $instance_id
   *   The editor's instance ID in CKEDITOR.instances.
   *
   * @When I fill in :text in WYSIWYG editor :instance_id
   */
  public function iFillInInWYSIWYGEditor($text, $instance_id)
  {
    $this->getSession()
      ->executeScript("CKEDITOR.instances['$instance_id'].insertHtml('$text')");
  }

  /**
   * Asserts that a WYSIWYG editor's data contains a snippet of text.
   *
   * @param string $instance_id
   *   The editor's instance ID in CKEDITOR.instances.
   * @param string $text
   *   The text (or HTML) snippet to look for.
   *
   * @throws \Exception if the editor doesn't contain the specified text.
   *
   * @Then WYSIWYG editor :instance_id should contain :text
   * @Then the WYSIWYG editor :instance_id should contain :text
   * @Then the :instance_id WYSIWYG editor should contain :text
   */
  public function wysiwygEditorShouldContain($instance_id, $text) {
    // The JavaScript return value is passed back by Selenium, but Mink's
    // Selenium2 driver doesn't return it (they're literally missing a return
    // keyword in executeScript() -- that's all!). So, to get around this,
    // we need to directly call the appropriate method on the WebDriver wrapper.
    $html = $this->getSession()
      ->getDriver()
      // This method only exists on Mink's Selenium2 driver. But what kind of
      // loony would try to test a WYSIWYG without Selenium?
      ->getWebDriverSession()
      ->execute([
        'script' => "return CKEDITOR.instances['$instance_id'].getData()",
        'args' => [],
      ]);

    if (strpos($html, $text) === FALSE) {
      throw new \Exception("WYSIWYG editor $instance_id did not contain text $text.");
    }
  }

}
