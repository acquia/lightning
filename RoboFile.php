<?php

class RoboFile extends \Robo\Tasks {

  /**
   * {@inheritdoc}
   */
  protected function taskBehat($behat = NULL) {
    return parent::taskBehat($behat ?: 'vendor/bin/behat')
      ->config('docroot/sites/default/files/behat.yml')
      ->option('strict');
  }

  protected function taskDrupal($command, $console = NULL) {
    return $this->taskExec($console ?: '../vendor/bin/drupal')
      ->rawArg($command)
      ->dir('docroot');
  }

  protected function taskDrush($command, $drush = NULL) {
    return $this->taskExec($drush ?: '../vendor/bin/drush')
      ->rawArg($command)
      ->dir('docroot');
  }

  /**
   * Updates from a previous version of Lightning.
   *
   * @param string $version
   *   The version from which to update.
   *
   * @see ::restore()
   *
   * @return \Robo\Contract\TaskInterface|NULL
   *   The task(s) to run, or NULL if the specified version is invalid.
   */
  public function update($version) {
    $tasks = $this->restore($version);

    if ($tasks) {
      $tasks
        ->addTask(
          $this->taskDrush('updatedb')->option('yes')
        )
        ->addTask(
          $this->taskDrupal('update:lightning')->option('no-interaction')->arg($version)
        );
    }
    return $tasks;
  }

  /**
   * Restores a database dump of a previous version of Lightning.
   *
   * @param string $version
   *   The semantic version from which to restore, e.g. 2.1.7. A dump of this
   *   version must exist in the tests/fixtures directory, named like
   *   $version.sql.bz2.
   *
   * @return \Robo\Contract\TaskInterface|NULL
   *   The task(s) to run, or NULL if the fixture does not exist.
   */
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
   */
  public function testBehat(array $arguments) {
    $this
      ->taskExec('vendor/bin/selenium-server-standalone')
      ->rawArg('-port 4444')
      ->rawArg('-log selenium.log')
      ->background()
      ->run();

    $task = $this->taskBehat();

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
