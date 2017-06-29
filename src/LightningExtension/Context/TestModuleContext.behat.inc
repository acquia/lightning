<?php

namespace Acquia\LightningExtension\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Allows test modules to be installed during a scenario and uninstalled after.
 */
class TestModuleContext extends DrupalSubContextBase {

  /**
   * Determines the test modules to install during the scenario.
   *
   * @param ScenarioScope $scope
   *   The current scenario scope.
   *
   * @return string[]
   *   The test modules to install.
   */
  protected function getModules(ScenarioScope $scope) {
    $modules = [];

    $tags = array_merge(
      $scope->getFeature()->getTags(),
      $scope->getScenario()->getTags()
    );
    foreach ($tags as $tag) {
      if (strpos($tag, 'with-module:') === 0) {
        $modules[] = substr($tag, 12);
      }
    }
    return array_unique($modules);
  }

  /**
   * Installs test modules for this scenario.
   *
   * @param BeforeScenarioScope $scope
   *   The scenario scope.
   *
   * @BeforeScenario
   */
  public function install(BeforeScenarioScope $scope) {
    $modules = $this->getModules($scope);

    if ($modules) {
      $this->getDriver()->bootstrap();
      \Drupal::service('module_installer')->install($modules);
    }
  }

  /**
   * Uninstalls any test modules installed for the scenario.
   *
   * @param AfterScenarioScope $scope
   *   The scenario scope.
   *
   * @AfterScenario
   */
  public function uninstall(AfterScenarioScope $scope) {
    $modules = $this->getModules($scope);

    if ($modules) {
      \Drupal::service('module_installer')->uninstall($modules);
    }
  }

}
