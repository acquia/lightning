<?php

/**
 * @file
 * Contains \Acquia\Lightning\Command\PackageCommand.
 */

namespace Acquia\Lightning\Command;

use Acquia\Lightning\Bower;
use Acquia\Lightning\MakeParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to generate scripts needed by drupal.org's packaging system.
 */
class PackageCommand extends Command {

  /**
   * The legacy make file (de-)serializer.
   *
   * @var \Acquia\Lightning\MakeParser
   */
  protected $makeParser;

  /**
   * A wrapper around Bower.
   *
   * @var \Acquia\Lightning\Bower
   */
  protected $bower;

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $this->makeParser = new MakeParser();
    $this->bower = new Bower();

    $output->writeln('Hi, rock star! Preparing package...');
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('package')
      ->setDescription('Generates scripts used by drupal.org\'s packaging system.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $make = shell_exec('drush make-convert composer.lock --format=make');
    $make = $this->makeParser->decode($make);

    if (isset($make['projects']['drupal'])) {
      $output->writeln('Found core project, creating make file.');

      // Always use drupal.org's core repository, or patches will not apply.
      $make['projects']['drupal']['download']['url'] = 'https://git.drupal.org/project/drupal.git';

      $core = [
        'core' => '8.x',
        'api' => 2,
        'projects' => [
          'drupal' => $make['projects']['drupal'],
        ],
      ];

      $this->write('drupal-org-core.make', $this->makeParser->encode($core));
      unset($make['projects']['drupal']);
    }

    foreach ($this->bower as $lib) {
      $package = $lib['name'];

      $make['libraries'][$package] = [
        'type' => 'library',
        'directory_name' => $package,
        'download' => [
          'type' => 'git',
          'url' => $lib['_source'],
          'tag' => $lib['_resolution']['tag'],
        ],
      ];
    }

    $this->write('drupal-org.make', $this->makeParser->encode($make));
    $output->writeln('Created make file for contributed projects and libraries.');
  }

  /**
   * Attempts to write a file.
   *
   * @param string $file
   *   The file to write, relative to the CWD.
   * @param string $contents
   *   The file contents.
   *
   * @throws \RuntimeException
   *   If the file could not be written.
   */
  private function write($file, $contents) {
    $victory = file_put_contents($file, $contents);
    if (empty($victory)) {
      throw new \RuntimeException('Could not write ' . $file . '.');
    }
  }

}
