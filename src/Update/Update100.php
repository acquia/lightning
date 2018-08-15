<?php

namespace Drupal\lightning\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\lightning_roles\ContentRoleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("1.0.0")
 */
final class Update100 implements ContainerInjectionInterface {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * The content role manager.
   *
   * @var \Drupal\lightning_roles\ContentRoleManager
   */
  private $contentRoleManager;

  /**
   * Update100 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\lightning_roles\ContentRoleManager
   *   The content role manager.
   */
  public function __construct(ModuleInstallerInterface $module_installer, ContentRoleManager $content_role_manager) {
    $this->moduleInstaller = $module_installer;
    $this->contentRoleManager = $content_role_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer'),
      $container->get('lightning.content_roles')
    );
  }

  /**
   * Enables the Moderation Sidebar module.
   *
   * @update
   */
  public function enableModerationSidebar() {
    $this->moduleInstaller->install(['moderation_sidebar']);
    $this->contentRoleManager->grantPermissions('creator', [
      'use moderation sidebar',
    ]);
  }

}
