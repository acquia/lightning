<?php

namespace Drupal\lightning_core;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A storage handler for entity types that are bundles of other entity types.
 */
class BundleEntityStorage extends ConfigEntityStorage {

  /**
   * The access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessHandler;

  /**
   * BundleEntityStorage constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID generator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityAccessControlHandlerInterface $access_handler
   *   The access control handler.
   */
  public function __construct($entity_type, $config_factory, $uuid_service, $language_manager, EntityAccessControlHandlerInterface $access_handler) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager);
    $this->accessHandler = $access_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')->getAccessControlHandler($entity_type->getBundleOf())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL, $check_access = FALSE) {
    if ($check_access) {
      $ids = array_filter(
        $ids ?: $this->getQuery()->execute(),
        [$this->accessHandler, 'createAccess']
      );
    }
    return parent::loadMultiple($ids);
  }

}
