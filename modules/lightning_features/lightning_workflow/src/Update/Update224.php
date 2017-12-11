<?php

namespace Drupal\lightning_workflow\Update;

use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Workflow 2.2.4.
 *
 * @Update("2.2.4")
 */
final class Update224 implements ContainerInjectionInterface {

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Update224 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ModuleInstallerInterface $module_installer, ModuleHandlerInterface $module_handler) {
    $this->moduleInstaller = $module_installer;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer'),
      $container->get('module_handler')
    );
  }

  /**
   * @update
   *
   * @ask Do you want to migrate your moderated content to Content Moderation?
   */
  public function migrate(DrupalStyle $io) {
    $io->writeln('Installing wbm2cm.');
    $this->moduleInstaller->install(['wbm2cm']);

    /** @var \Drupal\wbm2cm\MigrationController $controller */
    $controller = \Drupal::service('wbm2cm.migration_controller');

    $io->writeln('Saving existing moderation states...');
    $controller->executeStep('save');
    $io->writeln('Removing moderation states. This is necessary in order to uninstall Workbench Moderation.');
    $controller->executeStep('clear');

    $io->writeln('Installing Content Moderation...');
    $this->moduleInstaller->uninstall(['workbench_moderation']);
    $this->moduleInstaller->install(['content_moderation']);

    $io->writeln('Restoring saved moderation states...');
    $controller->executeStep('restore');

    $io->writeln('Congratulations, you have been migrated to Content Moderation :)');
    $this->moduleInstaller->uninstall(['wbm2cm']);

    if ($this->moduleHandler->moduleExists('lightning_scheduled_updates')) {
      $this->moduleInstaller->uninstall(['lightning_scheduled_updates']);
      $this->moduleInstaller->install(['lightning_scheduler']);
    }
  }

}
