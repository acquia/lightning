<?php

/**
 * @file
 * Contains \LightningSubContext.
 */

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Sub context for Lightning step definitions.
 */
class LightningSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
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
   * Deletes the URL alias provided.
   *
   * @param string $path_alias
   *   The alias to delete (this is the alias entered by the user or generated
   *   by pathauto, not the system path).
   *
   * @Then I cleanup the :path_alias alias
   */
  public function cleanupTheAlias($path_alias) {
    if ($this->getDriver()->isBootstrapped()) {
      $system_path = \Drupal::service('path.alias_manager')->getPathByAlias($path_alias, NULL);
      if ($system_path == $path_alias) {
        throw new \RuntimeException('Path alias `' . $path_alias . '` not found.');
      }
      \Drupal::service('path.alias_storage')->delete(['source' => $system_path]);
    }
    else {
      throw new \RuntimeException('Drupal is not bootstrapped.');
    }
  }

  /**
   * Clicks an arbitrary element, found by CSS selector.
   *
   * @param string $selector
   *   The CSS selector.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   *   If the specified element is not present on the page.
   *
   * @When I click the :selector element
   */
  public function clickElementBySelector($selector) {
    $session = $this->getSession();

    $element = $session->getPage()->find('css', $selector);
    if ($element) {
      try {
        $this->scrollToElement($selector);
      }
      catch (UnsupportedDriverActionException $e) {
        // Don't worry about it.
      }
      $element->click();
    }
    else {
      throw new ElementNotFoundException($session, 'element', 'css', $selector);
    }
  }

  /**
   * Asserts that a role as a set of permissions.
   *
   * @param string $rid
   *   The role ID.
   * @param \Behat\Gherkin\Node\PyStringNode $permissions
   *   The permissions to check for.
   *
   * @Then the :role role should have permissions:
   * @Then the :role role should have permission to:
   */
  public function assertPermissions($rid, PyStringNode $permissions) {
    foreach ($permissions->getStrings() as $permission) {
      $this->assertPermission($rid, $permission);
    }
  }

  /**
   * Asserts that a role does NOT have a set of permissions.
   *
   * @param string $rid
   *   The role ID.
   * @param \Behat\Gherkin\Node\PyStringNode $permissions
   *   The permissions to check for.
   *
   * @Then the :role role should not have permissions:
   * @Then the :role role should not have permission to:
   */
  public function assertNoPermissions($rid, PyStringNode $permissions) {
    foreach ($permissions->getStrings() as $permission) {
      $this->assertNoPermission($rid, $permission);
    }
  }

  /**
   * Asserts that a role has a specific permission.
   *
   * @param string $rid
   *   The role ID.
   * @param string $permission
   *   The permission to check for.
   *
   * @Given the :role role has the :permission permission
   * @Given the :role role has permission to :permission
   *
   * @Then the :role role should have the :permission permission
   * @Then the :role role should have permission to :permission
   */
  public function assertPermission($rid, $permission) {
    $this->minkContext->assertAtPath('/admin/people/permissions/' . $rid);
    $this->assertSession()->checkboxChecked($rid . '[' . $permission . ']');
  }

  /**
   * Asserts that a role does not have a specific permission.
   *
   * @param string $rid
   *   The role ID.
   * @param string $permission
   *   The permission to check for.
   *
   * @Given the :role role does not have the :permission permission
   * @Given the :role role does not have permission to :permission
   *
   * @Then the :role role should not have the :permission permission
   * @Then the :role role should not have permission to :permission
   */
  public function assertNoPermission($rid, $permission) {
    $this->minkContext->assertAtPath('/admin/people/permissions/' . $rid);

    $field = $rid . '[' . $permission . ']';
    try {
      $this->assertSession()->fieldNotExists($field);
    }
    catch (ExpectationException $e) {
      $this->assertSession()->checkboxNotChecked($field);
    }
  }

  /**
   * Creates a media asset from an embed code.
   *
   * @param string $bundle
   *   The media bundle ID.
   * @param \Behat\Gherkin\Node\PyStringNode $embed_code
   *   The embed code.
   *
   * @Given :bundle media from embed code:
   *
   * @When I create :bundle media from embed code:
   */
  public function createMediaFromEmbedCode($bundle, PyStringNode $embed_code) {
    $entity = \Drupal::entityTypeManager()
      ->getStorage('media')
      ->create([
        'bundle' => $bundle,
        'name' => $this->getRandom()->string(),
        'embed_code' => (string) $embed_code,
        'field_media_in_library' => TRUE,
        'status' => TRUE,
      ]);
    $entity->save();

    array_push($this->getContext('EntitySubContext')->entities, $entity);
  }

  /**
   * Scrolls an element into the viewport.
   *
   * @param string $selector
   *   The element's CSS selector.
   *
   * @When I scroll to the :selector element
   */
  public function scrollToElement($selector) {
    $this->getSession()
      ->executeScript('document.querySelector("' . addslashes($selector) . '").scrollIntoView()');
  }

  /**
   * Expands a drop button.
   *
   * @param string $selector
   *   The data-drupal-selector attribute of the drop button.
   *
   * @When I expand the :selector drop button
   */
  public function expandDropButton($selector) {
    $selector = 'ul.dropbutton[data-drupal-selector="' . $selector . '"]';
    $this->clickElementBySelector($selector . ' li.dropbutton-toggle');
    $this->scrollToElement($selector);
  }

  /**
   * Waits a while, for debugging.
   *
   * @param int $seconds
   *   How long to wait.
   *
   * @When I wait :seconds seconds
   */
  public function wait($seconds) {
    sleep($seconds);
  }

  /**
   * Maximizes the window (probably for debugging purposes).
   *
   * @When I maximize the window
   */
  public function maximize() {
    $this->getSession()->getDriver()->maximizeWindow();
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
   * Asserts that a button is not present on the page.
   *
   * @param string $button
   *   The label of the button.
   *
   * @Then I should not see the :button button
   */
  public function assertNotButton($button) {
    try {
      $this->minkContext->assertButton($button);
    }
    catch (\Exception $e) {
      // If the assertion failed, the button does not exist and all is well.
    }
  }

  /**
   * Fills in an autocompleting entity reference field.
   *
   * @param string $entity_type
   *   The target entity type.
   * @param string $label
   *   The target entity's label.
   * @param string $field
   *   Entity reference field locator.
   *
   * @throws \Exception
   *   If no entities of the given type, with the given label, are found.
   *
   * @When I reference :entity_type :label in :field
   */
  public function referenceEntityByLabel($entity_type, $label, $field) {
    $label_property = \Drupal::entityTypeManager()
      ->getDefinition($entity_type)
      ->getKey('label');

    $entities = \Drupal::entityTypeManager()
      ->getStorage($entity_type)
      ->loadByProperties([
        $label_property => $label,
      ]);

    if ($entities) {
      $this->minkContext->fillField($field, $label . ' (' . reset($entities)->id() . ')');
    }
    else {
      throw new \Exception("No $entity_type labeled '$label' was found.");
    }
  }

}
