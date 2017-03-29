<?php

/**
 * @file
 * Contains \EntitySubContext.
 */

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\DrupalExtension\Context\DrupalContext;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * Subcontext for creating and cleaning up entities of any type.
 */
class EntitySubContext extends DrupalSubContextBase {

  /**
   * Entities created during the scenario, organized by type.
   *
   * @var array
   */
  protected $entities = [];

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
      $this->queueEntity($entity);
    }
  }

  /**
   * Marks any automatically created node as belonging to the current user.
   *
   * @param \Drupal\DrupalExtension\Hook\Scope\EntityScope $scope
   *   The hook scope.
   *
   * @beforeNodeCreate
   */
  public function setNodeAuthor(EntityScope $scope) {
    $node = $scope->getEntity();

    if (!isset($node->uid)) {
      $user = $this->getContext(DrupalContext::class)->user;

      if (is_object($user)) {
        $node->uid = $user->uid;
      }
    }
  }

  /**
   * Queues a node to be deleted at the end of the scenario.
   *
   * @param \Drupal\DrupalExtension\Hook\Scope\EntityScope $scope
   *   The hook scope.
   *
   * @afterNodeCreate
   */
  public function queueNode(EntityScope $scope) {
    $node = Node::load($scope->getEntity()->nid);
    $this->queueEntity($node);
  }

  /**
   * Queues a taxonomy term to be deleted at the end of the scenario.
   *
   * @param \Drupal\DrupalExtension\Hook\Scope\EntityScope $scope
   *   The hook scope.
   *
   * @afterTermCreate
   */
  public function queueTerm(EntityScope $scope) {
    $term = Term::load($scope->getEntity()->tid);
    $this->queueEntity($term);
  }

  /**
   * Queues a user to be deleted at the end of the scenario.
   *
   * @param \Drupal\DrupalExtension\Hook\Scope\EntityScope $scope
   *   The hook scope.
   *
   * @afterUserCreate
   */
  public function queueUser(EntityScope $scope) {
    $user = User::load($scope->getEntity()->uid);
    $this->queueEntity($user);
  }

  /**
   * Queues an entity to be deleted at the end of the scenario.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to queue.
   */
  public function queueEntity(EntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();
    $this->entities[$entity_type][] = $entity;
  }

  /**
   * Deletes all entities created during the scenario.
   *
   * @AfterScenario
   */
  public function cleanEntities() {
    foreach ($this->entities as $entity_type => $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        // Clean up the entity's alias, if there is one.
        $path = '/' . $entity->toUrl()->getInternalPath();
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);
        if ($alias != $path) {
          \Drupal::service('path.alias_storage')->delete(['alias' => $alias]);
        }
      }
      \Drupal::entityTypeManager()->getStorage($entity_type)->delete($entities);
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

    $session = $this->getSession();

    $rows = $session->getPage()->findAll('css', '.diff-revisions tbody tr');
    /** @var \Behat\Mink\Element\NodeElement $row */
    foreach ($rows as $row) {
      if ($row->find('css', 'td:last-child')->getText() == 'Current revision') {
        return $row->find('css', 'td:first-child a')->click();
      }
    }

    // WTF? None of the rows were the current revision.
    throw new ExpectationException('Current revision not found.', $session->getDriver());
  }

  /**
   * Compares two revisions of a node.
   *
   * @param string $a
   *   The one-based index of a revision to compare. 1st is oldest.
   * @param string $b
   *   The one-based index of a revision to compare. 1st is oldest.
   *
   * @When I compare the :a and :b revisions
   */
  public function compareRevisions($a, $b) {
    $re = '/^[0-9]+(st|nd|rd|th)$/i';

    if (preg_match($re, $a)) {
      $a = substr($a, 0, -2);
    }
    if (preg_match($re, $b)) {
      $b = substr($b, 0, -2);
    }
    $a = ((int) $a) - 1;
    $b = ((int) $b) - 1;

    $this->minkContext->clickLink('Revisions');

    /** @var \Behat\Mink\Element\NodeElement[] $rows */
    $rows = $this->getSession()
      ->getPage()
      ->findAll('css', '.diff-revisions tbody tr');

    $rows = array_reverse($rows);
    $a = $rows[$a]->findField('radios_left')->getValue();
    $b = $rows[$b]->findField('radios_right')->getValue();

    $this->minkContext->selectOption('radios_left', $a);
    $this->minkContext->selectOption('radios_right', $b);
    $this->minkContext->pressButton('Compare');
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
   *
   * @When I queue the latest :entity_type entity for deletion
   * @When I queue the latest :limit :entity_type entities for deletion
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

    array_walk($entities, [$this, 'queueEntity']);
  }

}
