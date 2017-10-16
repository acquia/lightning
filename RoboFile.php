<?php

class RoboFile extends \Robo\Tasks {

  /**
   * {@inheritdoc}
   */
  protected function taskBehat($behat = NULL) {
    return parent::taskBehat($behat ?: 'bin/behat')
      ->config('docroot/sites/default/files/behat.yml')
      ->option('strict');
  }

  protected function taskDrupal($command, $console = NULL) {
    return $this->taskExec($console ?: '../bin/drupal')
      ->rawArg($command)
      ->dir('docroot');
  }

  protected function taskDrush($command, $drush = NULL) {
    return $this->taskExec($drush ?: '../bin/drush')
      ->rawArg($command)
      ->dir('docroot');
  }

  public function update($version) {
    $tasks = $this->restore($version);

    if ($tasks) {
      $tasks
        ->addTask(
          $this->taskDrush('updatedb')->option('yes')
        )
        ->addTask(
          $this->taskDrupal('update:lightning')->option('no-interaction')->option('since', $version)
        );
    }
    return $tasks;
  }

  public function restore($version) {
    $fixture = "tests/fixtures/$version.sql";

    if (file_exists("$fixture.bz2")) {
      return $this->collectionBuilder()
        ->addTask(
          $this->taskExec('bunzip2')->arg("$fixture.bz2")->option('keep')->option('force')
        )
        ->addTask(
          $this->taskDrupal('database:restore')->option('file', "../$fixture")
        )
        ->completion(
          $this->taskFilesystemStack()->remove($fixture)
        );
    }
    else {
      $this->say("$version fixture does not exist.");
    }
  }

  /**
   * Run Behat tests.
   *
   * To run all tests, simply run 'test:behat'. To run a specific feature, you
   * can pass its path, relative to the tests/features directory:
   *
   * test:behat media/image.feature
   *
   * You can omit the .feature extension. This example runs
   * tests/features/workflow/diff.feature:
   *
   * test:behat workflow/diff
   *
   * This also works with a directory of features. This example runs everything
   * in tests/features/media:
   *
   * test:behat media
   *
   * Any command-line options after the initial -- will be passed unmodified to
   * Behat. So you can filter tests by tags, like normal:
   *
   * test:behat -- --tags=javascript,~media
   *
   * This command will start Selenium Server in the background during the test
   * run, to support functional JavaScript tests.
   *
   * @option $all Run all selected tests without stopping on failure.
   */
  public function testBehat(array $arguments, array $options = ['all' => FALSE]) {
    $this
      ->taskExec('bin/selenium-server-standalone')
      ->rawArg('-port 4444')
      ->rawArg('-log selenium.log')
      ->background()
      ->run();

    $task = $this->taskBehat();

    if (empty($options['all'])) {
      $task->stopOnFail();
    }

    foreach ($arguments as $argument) {
      if ($argument{0} == '-') {
        $task->rawArg($argument);
      }
      else {
        $feature = "tests/features/$argument";

        if (file_exists("$feature.feature")) {
          $feature .= '.feature';
        }
        $task->arg($feature);
      }
    }
    return $task;
  }

}
