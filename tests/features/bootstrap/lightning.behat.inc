<?php

/**
 * Contains \LightningSubContext.
 */

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Sub context for Lightning step definitions.
 */
class LightningSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }


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

  /**
   * Executes a CKEditor command.
   *
   * @param string $command
   *   The command ID, as known to CKEditor's API.
   * @param string $id
   *   The editor's instance ID in CKEDITOR.instances.
   *
   * @When I execute the :command command in CKEditor :id
   */
  public function iExecuteTheCommandInCkeditor($command, $id)
  {
    $this->getSession()
      ->evaluateScript("CKEDITOR.instances['$id'].execCommand('$command');");
  }

  /**
   * @When /^wait (\d+) seconds?$/
   */
  public function waitSeconds($seconds)
  {
    $this->getSession()->wait(1000*$seconds);
  }

  /**
   * @Then the element :selector should exist
   */
  public function theElementShouldExist($selector) {
    $this->assertSession()->elementExists('css', $selector);
  }

}
