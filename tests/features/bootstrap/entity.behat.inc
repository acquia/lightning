<?php

/**
 * @file
 * Contains \EntitySubContext.
 */

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Subcontext for creating and cleaning up entities of any type.
 */
class EntitySubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * Entities created during the scenario.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  public $entities = [];

  /**
   * The Lightning subcontext.
   *
   * @var \LightningSubContext
   */
  protected $lightning;

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
  public function gatherContexts() {
    $this->lightning = $this->getContext('LightningSubContext');
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Ensures the Drupal driver is bootstrapped.
   *
   * @throws \RuntimeException
   *   If the Drupal driver is not bootstrapped.
   */
  protected function ensureBootstrap() {
    if ($this->getDriver()->isBootstrapped() == FALSE) {
      throw new \RuntimeException('Drupal is not bootstrapped.');
    }
  }

  /**
   * Creates a set of entities of a given type.
   *
   * @param string $entity_type
   *   The entity type to create.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   The entities to create.
   *
   * @Given :entity_type entities:
   */
  public function createEntities($entity_type, TableNode $table) {
    $this->ensureBootstrap();

    foreach ($table as $row) {
      if ($entity_type == 'media' && empty($row['uid'])) {
        $row['uid'] = $this->uid();
      }

      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->create($row);
      $entity->save();
      array_push($this->entities, $entity);
    }
  }

  /**
   * Deletes all entities created during the scenario.
   *
   * @AfterScenario
   */
  public function cleanEntities() {
    foreach ($this->entities as $entity) {
      $entity->delete();
    }
  }

  /**
   * Returns the uid of the currently logged-in user from the DrupalContext.
   *
   * Under certain circumstances (which I don't understand yet),
   * \Drupal::currentUser() is anonymous even after logging in! If that's the
   * case, we can pull the uid right out of the DrupalContext using reflection.
   * This is a naughty hack and should eventually be removed.
   *
   * @return int
   *   The ID of the logged-in user, according to the Drupal context.
   */
  protected function uid() {
    $context = $this->getContext('Drupal\DrupalExtension\Context\DrupalContext');

    $property = (new \ReflectionObject($context))->getProperty('user');
    $property->setAccessible(TRUE);
    return $property->getValue($context)->uid;
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
    $this->ensureBootstrap();

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
      throw new \UnexpectedValueException("No $entity_type entities found.");
    }
  }

  /**
   * Visits the current revision of a node.
   *
   * @When I visit the current revision
   */
  public function visitCurrentNodeRevision() {
    $this->minkContext->clickLink('Revisions');
    $this->lightning->clickElementBySelector('td.revision-current:first-child a');
  }

  /**
   * Visits a specific revision of a node.
   *
   * @param int $n
   *   The one-based index of the revision.
   *
   * @When /^I visit the (\d+)(?:st|nd|rd|th) revision$/
   */
  public function visitNthRevision($n) {
    $this->minkContext->clickLink('Revisions');
    $this->lightning->clickElementBySelector('main table tr:nth-child(' . $n . ') td:first-child a');
  }

  /**
   * Tracks the latest entities of a given type for post-scenario deletion.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $sort_field
   *   (optional) The creation time field to sort on. Defaults to 'created'.
   * @param int $limit
   *   (optional) The maximum number of entities to track. Defaults to 1.
   */
  public function trackNewest($entity_type, $sort_field = 'created', $limit = 1) {
    $this->ensureBootstrap();

    $entities = \Drupal::entityQuery($entity_type)
      ->sort($sort_field, 'DESC')
      ->range(0, $limit)
      ->execute();

    $entities = \Drupal::entityTypeManager()
      ->getStorage($entity_type)
      ->loadMultiple($entities);

    $this->entities = array_merge($this->entities, $entities);
  }

}
