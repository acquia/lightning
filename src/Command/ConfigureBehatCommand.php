<?php

namespace Drupal\lightning\Command;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to generate Behat configuration for an installed Drupal site.
 */
class ConfigureBehatCommand extends Command {

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ConfigureBehatCommand constructor.
   *
   * @param string $app_root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct($app_root, ModuleHandlerInterface $module_handler) {
    parent::__construct('behat:configure');

    $this->appRoot = $app_root;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this
      ->setDescription('Generates a configuration file for executing Behat tests.')
      ->addArgument(
        'base_url',
        InputArgument::REQUIRED,
        'The base URL of your Drupal installation.'
      )
      ->addOption(
        'destination',
        NULL,
        InputOption::VALUE_REQUIRED,
        'URI at which to write the generated configuration.',
        'public://behat.yml'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $profile = [];

    // Use the Mink Extension, if available.
    if (class_exists('\Behat\MinkExtension\ServiceContainer\MinkExtension')) {
      $profile['extensions']['Behat\MinkExtension'] = [
        'base_url' => $input->getArgument('base_url'),
        'goutte' => NULL,
        'selenium2' => [
          'wd_host' => 'http://127.0.0.1:4444/wd/hub',
          'browser' => 'chrome',
        ],
      ];
    }

    // Use the Drupal Extension, if available.
    if (class_exists('\Drupal\DrupalExtension\ServiceContainer\DrupalExtension')) {
      $profile['extensions']['Drupal\DrupalExtension'] = [
        'api_driver' => 'drupal',
        'blackbox' => NULL,
        'drupal' => [
          'drupal_root' => (string) $this->appRoot,
        ],
        'drush' => [
          'alias' => 'self',
        ],
        'subcontexts' => [
          'autoload' => FALSE,
        ],
        'selectors' => [
          'error_message_selector' => '.messages [role="alert"]',
          'login_form_selector' => '#user-login-form',
        ],
      ];
    }
    $config = ['default' => $profile];

    foreach ($this->moduleHandler->getModuleList() as $module) {
      $base_path = $this->appRoot . '/' . $module->getPath();

      $import = $base_path . '/tests/behat.yml';

      if (file_exists($import)) {
        $import = file_get_contents($import);
        $import = str_replace('%paths.base%', $base_path, $import);
        $import = Yaml::decode($import);

        $config = NestedArray::mergeDeep($config, $import);
      }
    }

    $destination = drupal_realpath($input->getOption('destination'));

    $success = file_put_contents($destination, Yaml::encode($config));
    if ($success === FALSE) {
      throw new \RuntimeException('Failed to write ' . $destination);
    }
    $output->writeln('Generated ' . $destination);
  }

}
