<?php

/**
 * @file
 * Contains \LightningSubContext.
 */

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
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
   * The Drupal context.
   *
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * Content type IDs created during a scenario.
   *
   * @var string[]
   */
  protected $contentTypes = [];

  /**
   * Media entities created during a scenario.
   *
   * @var integer[]
   */
  protected $media = [];

  /**
   * Pre-scenario hook.
   *
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
   * @throws \Exception
   *   If the specified CKEditor instance does not exist.
   *
   * @Given CKEditor :id exists
   *
   * @Then CKEditor :id should exist
   */
  public function ckEditorShouldExist($id) {
    $exists = $this->getSession()
      ->evaluateScript("CKEDITOR.instances.hasOwnProperty('$id');");

    if ($exists == FALSE) {
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
  public function iPutTextIntoCkEditor($text, $id) {
    $this->getSession()
      ->executeScript("CKEDITOR.instances['$id'].insertHtml('$text');");
  }

  /**
   * Asserts that a CKEditor's content contains a snippet of text.
   *
   * @param string $id
   *   The editor's instance ID in CKEDITOR.instances.
   * @param string $text
   *   The text (or HTML) snippet to look for.
   *
   * @throws \Exception
   *   If the editor doesn't contain the specified text.
   *
   * @Then CKEditor :id should contain :text
   */
  public function ckEditorShouldContain($id, $text) {
    $html = $this->getCkEditorContent($id);

    if (strpos($html, $text) == FALSE) {
      throw new \Exception("CKEditor $id did not contain '$text''.");
    }
  }

  /**
   * Assert that a CKEditor's content matches a regular expression.
   *
   * @param string $id
   *   The editor's instance ID in CKEDITOR.instances.
   * @param string $expression
   *   The regular expression to match.
   *
   * @throws \Exception
   *   If the expression does not match.
   *
   * @Then CKEditor :id should match :expression
   */
  public function ckEditorShouldMatch($id, $expression) {
    $html = $this->getCkEditorContent($id);

    if (preg_match($expression, $html) == 0) {
      throw new \Exception("CKEditor $id did not match '$expression'.");
    }
  }

  /**
   * Gets the content of a CKEditor instance.
   *
   * @param string $id
   *   The editor instance ID in CKEDITOR.instances.
   *
   * @return string
   *   The HTML content of the editor.
   */
  protected function getCkEditorContent($id) {
    return $this->getSession()
      ->evaluateScript("CKEDITOR.instances['$id'].getData();");
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
  public function iExecuteTheCommandInCkeditor($command, $id) {
    $this->getSession()
      ->evaluateScript("CKEDITOR.instances['$id'].execCommand('$command');");
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
   * Visits a random entity of a specific type and bundle.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $bundle
   *   The bundle ID.
   *
   * @When I visit a :entity_type entity of type :bundle
   */
  public function visitEntity($entity_type, $bundle) {
    if ($this->getDriver()->isBootstrapped()) {
      $bundle_key = \Drupal::entityTypeManager()
        ->getDefinition($entity_type)
        ->getKey('bundle');

      $entities = \Drupal::entityQuery($entity_type)
        ->condition($bundle_key, $bundle)
        ->execute();

      if ($entities) {
        $path = \Drupal::entityTypeManager()
          ->getStorage($entity_type)
          ->load(reset($entities))
          ->toUrl()
          ->getInternalPath();

        $this->visitPath($path);
      }
      else {
        throw new \UnexpectedValueException('No ' . $entity_type . ' entities found.');
      }
    }
    else {
      throw new \RuntimeException('Drupal is not bootstrapped.');
    }
  }

  /**
   * Deletes the URL alias provided.
   *
   * @param string $path_alias
   *   The alias to delete (this is the alias entered by the user or generated
   *   by pathauto, not the system path)
   *
   * @Then I cleanup the :path_alias alias
   */
  public function cleanupTheAlias($path_alias) {
    if ($this->getDriver()->isBootstrapped()) {
      $system_path = \Drupal::service('path.alias_manager')->getPathByAlias($path_alias, null);
      if ($system_path == $path_alias) {
        throw new \RuntimeException('Path alias `' . $path_alias . '` not found.');
      }
      \Drupal::service('path.alias_storage')->delete(array('source' => $system_path));
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
   * Applies Panelizer to a node type.
   *
   * @param string $bundle
   *   The node type ID.
   *
   * @Given I have applied Panelizer to the :bundle node type
   * @Given I have panelized the :bundle node type
   * @When I apply Panelizer to the :bundle node type
   * @When I panelize the :bundle node type
   */
  public function panelize($bundle) {
    $this->minkContext->assertAtPath("admin/structure/types/manage/$bundle/display");
    $this->minkContext->checkOption('panelizer[enable]');
    $this->minkContext->checkOption('panelizer[custom]');
    $this->minkContext->pressButton('Save');
  }

  /**
   * Removes Panelizer from a node type.
   *
   * @param string $bundle
   *   The node type ID.
   *
   * @Given I have removed Panelizer from the :bundle node type
   * @Given I have unpanelized the :bundle node type
   * @When I remove Panelizer from the :bundle node type
   * @When I unpanelize the :bundle node type
   */
  public function unpanelize($bundle) {
    $this->minkContext->assertAtPath("admin/structure/types/manage/$bundle/display");
    $this->minkContext->uncheckOption('panelizer[enable]');
    $this->minkContext->uncheckOption('panelizer[custom]');
    $this->minkContext->pressButton('Save');
  }

  /**
   * Creates a content type.
   *
   * @param string $id
   *   The content type ID.
   *
   * @Given I have created a :id content type
   * @Given I have a :id content type
   * @Given a :id content type
   * @When I create a :id content type
   */
  public function createContentType($id) {
    if (! in_array($id, $this->contentTypes)) {
      $this->visitPath('/admin/structure/types/add');

      $form = $this->getSession()->getPage()->find('css', '[data-drupal-selector="node-type-add-form"]');
      $form->findField('name')->setValue($id);
      $form->findField('type')->setValue($id);
      $form->submit();
    }
  }

  /**
   * Cleans up content types created during a scenario.
   *
   * @AfterScenario
   */
  public function cleanContentTypes() {
    foreach ($this->contentTypes as $type) {
      $this->visitPath('/admin/structure/types/manage/' . $type . '/delete');

      // If we can't access the page, don't bother. We did our due diligence.
      if ($this->getSession()->getStatusCode() == 200) {
        $this->minkContext->pressButton('Delete');
      }
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
    array_push($this->media, $entity);
  }

  /**
   * Cleans up any media entities created during a scenario.
   *
   * @AfterScenario
   */
  public function cleanMedia() {
    /** @var \Drupal\media_entity\MediaInterface $media */
    foreach ($this->media as $media) {
      $media->delete();
    }
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

}
