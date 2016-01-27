<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Controller\EntityCrudController.
 */

namespace Drupal\lightning_media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for media controllers which create, update, and delete entities.
 */
abstract class EntityCrudController extends ControllerBase {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * EntityCrudController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently logged-in user.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('renderer')
    );
  }

  /**
   * Handles POST (create) requests from the CKEditor media widget.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  abstract public function post(Request $request);

  /**
   * Handles PUT (save) requests from the CKEditor media widget.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be saved.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  abstract public function put(EntityInterface $entity);

  /**
   * Handles DELETE requests from the CKEditor media widget.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be deleted.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  abstract public function delete(EntityInterface $entity);

  /**
   * Returns basic info about an entity, for inclusion in a JSON response.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   The entity's type, bundle, ID, UUID, and label.
   */
  protected function getEntityResponseData(EntityInterface $entity) {
    return array(
      'entity_type' => $entity->getEntityTypeId(),
      'bundle' => $entity->bundle(),
      'id' => $entity->id(),
      'uuid' => $entity->uuid(),
      'label' => $entity->label(),
    );
  }

}
