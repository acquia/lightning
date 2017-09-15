<?php

namespace Drupal\lightning_media\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Media 2.1.8.
 *
 * @Update("2.1.8")
 */
final class Update218 implements ContainerInjectionInterface {

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Update218 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   */
  public function __construct(ModuleInstallerInterface $module_installer) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer')
    );
  }

  /**
   * @update
   *
   * @ask Do you want to enable bulk media upload?
   */
  public function bulkUpload() {
    $this->moduleInstaller->install(['lightning_media_bulk_upload']);
  }

}
