<?php

namespace Drupal\lightning\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains optional updates targeting Lightning 4.0.5.
 *
 * @Update("4.0.5")
 */
final class Update405 implements ContainerInjectionInterface {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * Update405 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
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
   * Enables the Autosave Form and Conflict modules.
   *
   * @update
   *
   * @ask Do you want to enable the Autosave Form and Conflict modules?
   */
  public function enableAutosaveForm() {
    $this->moduleInstaller->install(['autosave_form', 'conflict']);
  }

  /**
   * Enables the Redirect module.
   *
   * @update
   *
   * @ask Do you want to enable the Redirect module?
   */
  public function enableRedirect() {
    $this->moduleInstaller->install(['redirect']);
  }

}
