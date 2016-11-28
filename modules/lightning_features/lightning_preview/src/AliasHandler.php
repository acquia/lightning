<?php

namespace Drupal\lightning_preview;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\replication\Event\ReplicationEvent;
use Drupal\replication\Event\ReplicationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A service for dealing with path aliases across workspaces.
 */
class AliasHandler implements EventSubscriberInterface {

  /**
   * The path alias storage service.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * The machine name of the source workspace during a replication.
   *
   * @var string
   */
  protected $replicationSource;

  /**
   * AliasHandler constructor.
   *
   * @param \Drupal\Core\Path\AliasStorageInterface $alias_storage
   *   The path alias storage service.
   */
  public function __construct(AliasStorageInterface $alias_storage) {
    $this->aliasStorage = $alias_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ReplicationEvents::PRE_REPLICATION => [
        'onReplicationStart',
      ],
      ReplicationEvents::POST_REPLICATION => [
        'onReplicationEnd',
      ],
    ];
  }

  /**
   * Reacts when a replication begins.
   *
   * @param \Drupal\replication\Event\ReplicationEvent $event
   *   The event object.
   */
  public function onReplicationStart(ReplicationEvent $event) {
    $this->replicationSource = $event->getSourceWorkspace()->getMachineName();
  }

  /**
   * Reacts when a replication completes, regardless of success or failure.
   */
  public function onReplicationEnd() {
    $this->replicationSource = NULL;
  }

  /**
   * Replicates a path alias into a target workspace.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The target entity (i.e., on the target side of the replication).
   */
  public function replicateAlias(EntityInterface $entity) {
    // If there is a replication event, it means that a replication is now in
    // progress.
    if ($entity instanceof FieldableEntityInterface && $entity->hasField('workspace') && $this->replicationSource) {
      $source_workspace = $this->replicationSource;
      $target_workspace = $entity->workspace->entity->getMachineName();

      if ($entity->hasField('path')) {
        $alias = $entity->path->alias;

        if ($alias) {
          if ($target_workspace == 'live') {
            $alias = static::stripPrefix($alias, $source_workspace);
          }
          elseif ($source_workspace == 'live') {
            $alias = static::addPrefix($alias, $target_workspace);
          }
          // By default, create a new alias...
          $entity->path->pid = NULL;
          $entity->path->source = NULL;
          $entity->path->alias = $alias;

          // ...but if the entity isn't new, we might need to update an existing
          // alias, which we can try to locate using the entity's system path.
          if ($entity->isNew() == FALSE) {
            $existing_alias = $this->aliasStorage->load([
              'source' => '/' . $entity->toUrl()->getInternalPath(),
            ]);

            // If an alias exists, update that one.
            if ($existing_alias) {
              $entity->path->source = $existing_alias['source'];
              $entity->path->pid = $existing_alias['pid'];
            }
          }
        }
      }
    }
  }

  /**
   * Returns the machine name of the active workspace.
   *
   * @return string
   *   The machine name of the active workspace.
   */
  protected static function defaultPrefix() {
    return \Drupal::service('workspace.manager')
      ->getActiveWorkspace()
      ->getMachineName();
  }

  /**
   * Strips a workspace name name out of a path.
   *
   * @param string $path
   *   The prefixed path.
   * @param string $prefix
   *   (optional) The workspace machine name. Defaults to the active workspace.
   *
   * @return string
   *   The unprefixed path.
   */
  public static function stripPrefix($path, $prefix = NULL) {
    $prefix = $prefix ?: static::defaultPrefix();

    // The live workspace never uses prefixing.
    if ($prefix == 'live') {
      return $path;
    }
    else {
      return preg_replace('/^\/' . $prefix . '\//', '/', $path);
    }
  }

  /**
   * Prepends a workspace machine name to a path.
   *
   * @param string $path
   *   The unprefixed path.
   * @param string $prefix
   *   (optional) The workspace machine name. Defaults to the active workspace.
   *
   * @return string
   *   The prefixed path.
   */
  public static function addPrefix($path, $prefix = NULL) {
    $prefix = $prefix ?: static::defaultPrefix();

    // The live workspace never uses prefixing.
    if ($prefix == 'live') {
      return $path;
    }

    $path = explode('/', ltrim($path, '/'));

    if ($path[0] != $prefix) {
      array_unshift($path, $prefix);
    }

    return '/' . implode('/', $path);
  }

}
