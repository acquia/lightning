<?php

namespace Drupal\lightning\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command for including arbitrary sets of tests in Behat configuration.
 */
class BehatIncludeCommand extends BehatCommandBase {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this
      ->setName('behat:include')
      ->setDescription('Adds tests to your Behat configuration.')
      ->addArgument(
        'paths',
        InputArgument::REQUIRED | InputArgument::IS_ARRAY,
        'At least one directory of test features to add to your Behat configuration.',
        []
      )
      ->addOption(
        'suite',
        NULL,
        InputOption::VALUE_REQUIRED,
        'Which test suite to add the test features to.',
        'default'
      );

    // Subcontexts are a Drupal Extension feature, so only add the
    // --with-subcontexts option if Drupal Extension is available.
    if (class_exists('\Drupal\DrupalExtension\ServiceContainer\DrupalExtension')) {
      $this->addOption(
        'with-subcontexts',
        NULL,
        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'Additional directories of subcontexts to expose to Drupal Extension.'
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);

    $input->setArgument(
      'paths',
      array_map([$this, 'ensurePath'], $input->getArgument('paths'))
    );

    if ($input->hasOption('with-subcontexts')) {
      $input->setOption(
        'with-subcontexts',
        array_map([$this, 'ensurePath'], $input->getOption('with-subcontexts'))
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->readConfig();

    $suite = $input->getOption('suite');

    foreach ($input->getArgument('paths') as $path) {
      $config['default']['suites'][$suite]['paths'][] = $path;
    }

    if ($input->hasOption('with-subcontexts')) {
      foreach ($input->getOption('with-subcontexts') as $dir) {
        $config['default']['extensions']['Drupal\DrupalExtension']['subcontexts']['paths'][] = $dir;
      }
    }

    $this->writeConfig($config, $input->getOption('merge'));
  }

}
