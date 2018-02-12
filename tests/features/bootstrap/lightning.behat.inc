<?php

/**
 * @file
 * Contains \LightningSubContext.
 */

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Sub context for Lightning step definitions.
 */
class LightningSubContext extends DrupalSubContextBase {

  /**
   * Start time for each scenario.
   *
   * @var int
   */
  protected $startTime;

  /**
   * Store current time.
   *
   * @BeforeScenario
   */
  public function setStartTime() {
    $this->startTime = time();
  }

  /**
   * Check for errors since the scenario started.
   *
   * @AfterScenario ~@errors
   */
  public function checkWatchdog(AfterScenarioScope $scope) {
    // Bypass the error checking if the scenario has the @errors tag.
    if (in_array('errors', $scope->getScenario()->getTags())) {
      return;
    }

    $db = \Drupal::database();
    if ($db->schema()->tableExists('watchdog')) {
      $log = $db->select('watchdog', 'w')
        ->fields('w')
        ->condition('w.type', 'php', '=')
        ->condition('w.timestamp', $this->startTime, '>=')
        ->execute()
        ->fetchAll();

      if (!empty($log)) {
        // TODO: Move this stuff to its own exception class in Lightning
        // Extension for greater clarity.
        foreach ($log as $error) {
          // Make the substitutions easier to read in the log.
          $error->variables = unserialize($error->variables);
          print_r($error);
        }
        throw new \Exception('PHP errors logged to watchdog in this scenario.');
      }
    }
  }

  /**
   * Asserts that an element, specified by CSS selector, exists.
   *
   * @param string $selector
   *   The CSS selector to search for.
   *
   * @Then the element :selector should exist
   */
  public function theElementShouldExist($selector) {
    $this->assertSession()->elementExists('css', $selector);
  }

  /**
   * Takes a screenshot for debugging purposes.
   *
   * @param string $filename
   *   The name of the screenshot file.
   *
   * @When I take a screenshot named :filename
   */
  public function takeScreenshot($filename) {
    $screenshot = $this->getSession()->getDriver()->getScreenshot();
    // If this file is in tests/features/bootstrap, the screenshot be in tests.
    file_put_contents(__DIR__ . '../../' . $filename . '.png', $screenshot);
  }

  /**
   *  Dumps the HTML directly to stdout and a file.
   *
   * @When I view the HTML
   */
  public function viewTheHtml() {
    $html_data = $this->getSession()->getDriver()->getContent();
    echo $html_data;
    $file_and_path = '/tmp/behat_page.html';
    file_put_contents($file_and_path, $html_data);

    if (PHP_OS === "Darwin" && PHP_SAPI === "cli") {
      exec('open -a "Safari.app" ' . $file_and_path);
    };
  }

  /**
   * Asserts that we are visiting a media entity.
   *
   * @Then I should be visiting a media entity
   */
  public function assertVisitingMediaEntity() {
    $this->assertSession()->addressMatches('/\/media\/[0-9]+$/');

    try {
      $this->assertSession()->statusCodeEquals(200);
    }
    catch (UnsupportedDriverActionException $e) {
      // This isn't a critically important assertion, so don't worry about it.
    }
  }

  /**
   * Sets the absolute path to the directory containing uploadable files.
   *
   * @BeforeScenario
   */
  public function setFilePath() {
    /** @var MinkContext $mink_context */
    $mink_context = $this->getContext(MinkContext::class);

    $existing = $mink_context->getMinkParameter('files_path');
    if (empty($existing)) {
      $mink_context->setMinkParameter('files_path', __DIR__ . '/../../files');
    }
  }

  /**
   * Asserts that a select list does not have a specific option.
   *
   * @param string $field
   *   The select list to check.
   * @param string $option
   *   The option to look for.
   *
   * @Then :field should not have a(n) :option option
   */
  public function assertOptionNotExists($field, $option) {
    $assert = $this->assertSession();
    $field = $assert->fieldExists($field);
    $assert->elementNotExists('named', ['option', $option], $field);
  }

  /**
   * Asserts that a link is not present.
   *
   * @param string $locator
   *   The link title, ID, or text.
   *
   * @Then I should not see a(n) :locator link
   */
  public function assertLinkNotExists($locator) {
    $this->assertSession()
      ->elementNotExists('named', ['link', $locator]);
  }

}
