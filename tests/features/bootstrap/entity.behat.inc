<?php

/**
 * @file
 * Contains \EntitySubContext.
 */

use Drupal\Core\Entity\EntityInterface;
use Drupal\DrupalExtension\Context\DrupalContext;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Hook\Scope\BeforeNodeCreateScope;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;

/**
 * Subcontext for creating and cleaning up entities of any type.
 *
 * @internal
 * This class is part of Lightning's internal testing code. It is not an API and
 * should not be extended. This class will be marked final, and all protected
 * members will be made private, in Lightning 4.x.
 */
class EntitySubContext extends DrupalSubContextBase {

  /**
   * Entities created during the scenario, organized by type.
   *
   * @var array
   */
  protected $entities = [];

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
      $user = $this->getContext(DrupalContext::class)->getUserManager()->getCurrentUser();

      if (is_object($user)) {
        $node->uid = $user->uid;
      }
    }
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

  public function queueNode() {
    @trigger_error(__METHOD__ . ' is deprecated. Use ' . __CLASS__ . '::queueEntity() instead.', E_USER_DEPRECATED);
  }

  public function queueTerm() {
    @trigger_error(__METHOD__ . ' is deprecated. Use ' . __CLASS__ . '::queueEntity() instead.', E_USER_DEPRECATED);
  }

  public function queueUser() {
    @trigger_error(__METHOD__ . ' is deprecated. Use ' . __CLASS__ . '::queueEntity() instead.', E_USER_DEPRECATED);
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

  /**
   * Alters a node before it is created.
   *
   * @param \Drupal\DrupalExtension\Hook\Scope\BeforeNodeCreateScope $scope
   *   The hook scope.
   *
   * @BeforeNodeCreate
   */
  public function onNodeCreate(BeforeNodeCreateScope $scope) {
    $node = $scope->getEntity();

    if (isset($node->status) && empty($node->moderation_state)) {
      $node->moderation_state = $node->status ? 'published' : 'draft';
    }
  }

}
