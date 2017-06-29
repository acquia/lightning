<?php

namespace Acquia\LightningExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Exception\ExpectationException;
use Drupal\Component\Utility\Random;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\file\Entity\File;
use Drupal\media_entity\Entity\Media;
use WebDriver\Exception\NoAlertOpenError;

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
   * @Then I should not see a(n) :field field
   */
  public function assertFieldNotExists($field) {
    $this->assertSession()->fieldNotExists($field);
  }

  /**
   * Asserts that a minimum number of elements match a CSS selector.
   *
   * @param string $selector
   *   The selector.
   * @param int $count
   *   The number of elements expected to match the selector.
   *
   * @throws ExpectationException
   *   If the number of elements that match the selector is less than expected.
   *
   * @Then at least :count element(s) should match :selector
   */
  public function assertSelectorMatchAtLeast($selector, $count) {
    $session = $this->getSession();

    $result = count($session->getPage()->findAll('css', $selector));

    if ($result < $count) {
      throw new ExpectationException(
        '"' . $selector . '" matched ' . $result . ' element(s), expected at least ' . $count . '.',
        $session->getDriver()
      );
    }
  }

  /**
   * Asserts than an element is empty.
   *
   * @param string $selector
   *   The element's CSS selector.
   *
   * @throws ExpectationException
   *   If the element has any HTML content.
   *
   * @Then the :selector element(s) should be empty
   */
  public function assertElementIsEmpty($selector) {
    $content = $this->assertSession()->elementExists('css', $selector)->getHtml();

    if (trim($content)) {
      throw new ExpectationException(
        'Expected ' . $selector . ' to be empty but it is not.',
        $this->getSession()->getDriver()
      );
    }
  }

  /**
   * Clears a field.
   *
   * @param string $field
   *   The field to clear.
   * @param ElementInterface $container
   *   (optional) The containing element.
   *
   * @When I clear :field
   */
  public function clearField($field, ElementInterface $container = NULL) {
    $this->assertSession()->fieldExists($field, $container)->setValue(FALSE);
  }

  /**
   * Finds a collapsible details element by its summary text.
   *
   * @param string $summary_text
   *   The summary text.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The details element, or NULL if it was not found.
   */
  public function findCollapsible($summary_text) {
    /** @var \Behat\Mink\Element\NodeElement[] $elements */
    $elements = $this
      ->getSession()
      ->getPage()
      ->findAll('css', 'details > summary');

    foreach ($elements as $element) {
      if ($element->getText() == $summary_text) {
        return $element->getParent();
      }
    }
  }

  /**
   * Generates random image media assets.
   *
   * @param int $count
   *   (optional) How many to generate. Defaults to 1.
   * @param string $name
   *   (optional) The label of the media item wrapping the image. Defaults to
   *   a random string.
   * @param string $alt
   *   (optional) The alt text for the image.
   *
   * @Given a random image
   * @Given a random image named :name
   * @Given a random image with alt text :alt
   * @Given a random image named :name with alt text :alt
   * @Given :count random images
   */
  public function generateRandomImages($count = 1, $name = NULL, $alt = NULL) {
    $random = new Random();

    for ($i = 0; $i < $count; $i++) {
      $uri = $random->image(uniqid('public://random_') . '.png', '240x240', '640x480');

      $file = File::create([
        'uri' => $uri,
      ]);
      $file->setMimeType('image/png');
      $file->setTemporary();
      $file->save();

      $media = Media::create([
        'bundle' => 'image',
        'name' => $name ?: $random->name(32),
        'status' => TRUE,
        'image' => $file->id(),
        'field_media_in_library' => TRUE,
      ]);
      if ($alt) {
        $media->image->alt = $alt;
      }
      $media->save();
    }
  }

  /**
   * Generates random document media assets.
   *
   * @param int $count
   *   (optional) How many to generate. Defaults to 1.
   *
   * @Given a random document
   * @Given :count random documents
   */
  public function generateRandomDocument($count = 1) {
    $random = new Random();

    for ($i = 0; $i < $count; $i++) {
      $uri = uniqid('public://random_') . '.txt';
      file_put_contents($uri, $random->paragraphs(2));

      $file = File::create([
        'uri' => $uri,
      ]);
      $file->setMimeType('text/plain');
      $file->setTemporary();
      $file->save();

      Media::create([
        'bundle' => 'document',
        'name' => $random->name(32),
        'status' => TRUE,
        'image' => $file->id(),
        'field_media_in_library' => TRUE,
      ])->save();
    }
  }

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
    $driver = $this->getSession()->getDriver();

    if ($driver instanceof Selenium2Driver) {
      while (true) {
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
   * @throws \Behat\Mink\Exception\ExpectationException if there are not exactly
   * as many entities of the given type and bundle as expected.
   *
   * @Then there should be :count :entity_type entities
   * @Then there should be :count :bundle :entity_type entities
   * @Then there should be :count :entity_type entity
   * @Then there should be :count :bundle :entity_type entity
   */
  public function assertEntityExistence($entity_type, $count, $bundle = NULL) {
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

}
