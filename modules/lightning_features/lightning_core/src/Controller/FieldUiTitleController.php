<?php

namespace Drupal\lightning_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dynamically generates titles for various Field UI routes.
 */
class FieldUiTitleController extends ControllerBase {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * FieldUiTitleController constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The translation service.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $translator) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * Title callback for certain Field UI routes.
   *
   * @return string
   *   Either the label of the bundle affected at the current route, or the
   *   route's default title if the bundle is not known.
   *
   * @see \Drupal\lightning_core\Routing\RouteSubscriber::alterRoutes()
   */
  public function bundle() {
    $route_parameters = $this->routeMatch->getParameters();

    if ($route_parameters->has('entity_type_id')) {
      // Determine the route parameter which contains the bundle entity,
      // assuming the entity type is bundle-able.
      $bundle = $this->entityTypeManager()
        ->getDefinition(
          // Field UI routes should always have an entity_type_id parameter.
          // Maybe a naive assumption, but this function should only ever
          // be called for Field UI routes anyway.
          $route_parameters->get('entity_type_id')
        )
        ->getBundleEntityType();

      if ($bundle) {
        $bundle = $route_parameters->get($bundle);
        if ($bundle instanceof EntityInterface) {
          return $bundle->label();
        }
      }
    }
    return $this->routeMatch->getRouteObject()->getDefault('_title');
  }

}
