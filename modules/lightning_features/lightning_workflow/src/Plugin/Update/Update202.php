<?php

namespace Drupal\lightning_workflow\Plugin\Update;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning\UpdateBase;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Workflow 2.0.2.
 *
 * @Update("2.0.2")
 */
class Update202 extends UpdateBase {

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Update202 constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Symfony\Component\Console\Style\OutputStyle $io
   *   The console output driver.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The string translation service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OutputStyle $io, TranslationInterface $translation, ModuleInstallerInterface $module_installer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $io, $translation);
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, OutputStyle $io = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $io,
      $container->get('string_translation'),
      $container->get('module_installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $proceed = $this->io->confirm(
      $this->t('Do you want to install Diff?')
    );
    if ($proceed) {
      $this->moduleInstaller->install(['diff']);
    }
  }

}
