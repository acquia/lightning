<?php

namespace Acquia\LightningExtension\Context;

@trigger_error(__NAMESPACE__ . '\UtilityContext is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use WebDriver\Exception\NoAlertOpenError;

/**
 * A context with miscellaneous helpers.
 */
class UtilityContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * Accepts any currently open alert box(es), then optionally runs a callback.
   *
   * @param callable $then
   *   (optional) A function to run after accepting the alerts.
   * @param mixed[] $arguments
   *   (optional) Arguments for the callback.
   *
   * @When I accept the alert(s)
   */
  public function acceptAlerts(callable $then = NULL, array $arguments = []) {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    $driver = $this->getSession()->getDriver();

    if ($driver instanceof Selenium2Driver) {
      while (TRUE) {
        try {
          $driver->getWebDriverSession()->accept_alert();
        }
        catch (NoAlertOpenError $e) {
          break;
        }
      }
    }

    if ($then) {
      call_user_func_array($then, $arguments);
    }
  }

  /**
   * Asserts the existence of entities by running a count query.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param int $count
   *   The expected number of entities.
   * @param string $bundle
   *   (optional) The bundle to filter by.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If there aren't as many entities as expected.
   *
   * @Then there should be :count :entity_type entities
   * @Then there should be :count :bundle :entity_type entities
   * @Then there should be :count :entity_type entity
   * @Then there should be :count :bundle :entity_type entity
   */
  public function assertEntityExistence($entity_type, $count, $bundle = NULL) {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    $query = \Drupal::entityQuery($entity_type)->count();

    if ($bundle) {
      $query->condition(
        \Drupal::entityTypeManager()->getDefinition($entity_type)->getKey('bundle'),
        $bundle
      );
    }
    $exists = $query->execute();

    if ((int) $exists !== (int) $count) {
      throw new ExpectationException(
        "Expected {$count} {$entity_type} entities, but found {$exists}.",
        $this->getSession()->getDriver()
      );
    }
  }

  /**
   * Dumps the entire page contents to standard output.
   *
   * @When I dump the page contents
   */
  public function dumpPage() {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    print_r($this->getSession()->getPage()->getContent());
  }

}
