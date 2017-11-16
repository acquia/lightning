<?php

namespace Drupal\lightning_workflow\Update;

use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Workflow 2.2.4.
 *
 * @Update("2.2.4")
 */
final class Update224 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  protected $moduleInstaller;

  public function __construct(TranslationInterface $translation, ModuleInstallerInterface $module_installer) {
    $this->setStringTranslation($translation);
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('string_translation'),
      $container->get('module_installer')
    );
  }

  /**
   * @update
   *
   * @ask Do you want to migrate your moderated content to Content Moderation?
   */
  public function migrate(DrupalStyle $io) {
    $io->writeln('Now is a good time to get a coffee: this may take a while.');

    $io->writeln('Installing moderation_upgrade.');
    $this->moduleInstaller->install(['moderation_upgrade']);

    /** @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_manager */
    $migration_manager = \Drupal::service('plugin.manager.migration');

    $io->write('Saving existing moderation states...');
    $migrations = $migration_manager->createInstances(['moderation_upgrade_save']);
    $processed = $this->executeMigrations($migrations);
    $io->writeln("done! Processed $processed items.");
    $io->writeln('Moderation states were saved for all translations of all revisions of all moderated entities.');

    $io->writeln('Removing moderation states. This is necessary in order to uninstall Workbench Moderation.');
    $migrations = $migration_manager->createInstances(['moderation_upgrade_clear']);
    $processed = $this->executeMigrations($migrations);
    $io->writeln("Done. Moderation states were removed from $processed items.");

    $io->write('Uninstalling Workbench Moderation...');
    $this->moduleInstaller->uninstall(['workbench_moderation']);
    $io->writeln('done!');

    $io->write('Installing Content Moderation...');
    $this->moduleInstaller->install(['content_moderation']);
    $io->writeln('done!');

    $io->write('Restoring saved moderation states...');
    $migrations = $migration_manager->createInstances(['moderation_upgrade_restore']);
    $processed = $this->executeMigrations($migrations);
    $io->writeln("done! Processed $processed items.");

    $io->writeln('Uninstalling moderation_upgrade.');
    $this->moduleInstaller->uninstall(['moderation_upgrade']);

    $io->writeln('Congratulations, you have been migrated to Content Moderation :) You may remove Workbench Moderation from your code base.');
  }

  protected function executeMigrations(array $migrations) {
    $message = new MigrateMessage();

    $total = 0;

    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    foreach ($migrations as $migration) {
      $executable = new MigrateExecutable($migration, $message);
      $executable->import();
      $total += $migration->getIdMap()->processedCount();
    }
    return $total;
  }

}
