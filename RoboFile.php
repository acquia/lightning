<?php

class RoboFile extends \Robo\Tasks {

  /**
   * {@inheritdoc}
   */
  protected function taskBehat($behat = NULL) {
    $behat = $behat ?: 'bin/behat';

    return parent::taskBehat($behat)
      ->config('docroot/sites/default/files/behat.yml')
      ->option('strict')
      ->stopOnFail();
  }

  public function testBehat(array $arguments) {
    $this
      ->taskExec('bin/selenium-server-standalone')
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
