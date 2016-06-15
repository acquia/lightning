<?php

namespace Drupal\lightning_core;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A service for managing the configuration and deployment of content roles.
 */
class ContentRoleManager {

  /**
   * The config object being manipulated.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The node type entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeTypeStorage;

  /**
   * ContentRoleManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->config = $config_factory->getEditable('lightning_core.settings');
    $this->nodeTypeStorage = $entity_type_manager->getStorage('node_type');
  }

  /**
   * Grants permissions (or meta-permissions) to a content role.
   *
   * @param string $role_id
   *   The content role ID.
   * @param string[] $permissions
   *   The permissions to grant. Can contain the '?' token, which will be
   *   replaced with the node type ID.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function grantPermissions($role_id, array $permissions) {
    $key = "content_roles.{$role_id}";

    $role = $this->config->get($key);
    $role['permissions'] = array_merge($role['permissions'], $permissions);
    $this->config->set($key, $role)->save();

    if ($role['enabled']) {
      foreach ($this->nodeTypeStorage->loadMultiple() as $node_type) {
        $rid = $node_type->id() . '_' . $role_id;
        $grant = str_replace('?', $node_type->id(), $role['permissions']);
        user_role_grant_permissions($rid, $grant);
      }
    }

    return $this;
  }

}
