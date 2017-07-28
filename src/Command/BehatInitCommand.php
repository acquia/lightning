<?php

namespace Drupal\lightning\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to generate Behat configuration for an installed Drupal site.
 */
class BehatInitCommand extends BehatCommandBase {

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * BehatInitCommand constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param string $app_root
   *   The Drupal application root.
   */
  public function __construct($file_system, $app_root) {
    parent::__construct($file_system);
    $this->appRoot = $app_root;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this
      ->setName('behat:init')
      ->setDescription('Generates a configuration file for executing Behat tests.')
      ->addArgument(
        'base_url',
        InputArgument::REQUIRED,
        'The base URL of your Drupal installation.'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = [];

    // Use the Mink Extension, if available.
    if (class_exists('\Behat\MinkExtension\ServiceContainer\MinkExtension')) {
      $config['default']['extensions']['Behat\MinkExtension'] = [
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
      $config['default']['extensions']['Drupal\DrupalExtension'] = [
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
          'warning_message_selector' => '.messages--warning',
          'login_form_selector' => '#user-login-form',
        ],
      ];
    }

    $this->writeConfig($config, $input->getOption('merge'));
  }

}
