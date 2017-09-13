<?php

class RoboFile extends \Robo\Tasks {

  /**
   * {@inheritdoc}
   */
  protected function taskBehat($behat = NULL) {
    $behat = $behat ?: 'bin/behat';

    return parent::taskBehat($behat)
      ->config('docroot/sites/default/files/behat.yml')
      ->stopOnFail();
  }

  public function testBehat($feature = NULL) {
    $task = $this->taskBehat();

    if ($feature) {
      $feature = "tests/features/$feature";

      if (file_exists("$feature.feature")) {
        $feature .= '.feature';
      }
      $task->arg($feature);
    }
    return $task;
  }

}
