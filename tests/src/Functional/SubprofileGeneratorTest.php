<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Serialization\Yaml;
use Drupal\lightning\ComponentDiscovery;
use Drupal\lightning\Generators\SubProfileGenerator;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests the Drush command to generate a Lightning sub-profile.
 *
 * @covers \Drupal\lightning\Generators\SubProfileGenerator
 *
 * @group lightning
 */
class SubprofileGeneratorTest extends BrowserTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Data provider for ::test().
   *
   * @return array
   *   The sets of arguments to pass to the test method.
   */
  public function provider() {
    return [
      'additional modules in install list' => [
        'answers' => [
          'install' => ['consumers'],
        ],
      ],
      'with exclusions' => [
        'answers' => [
          'exclude' => '0, 3',
        ],
      ],
    ];
  }

  /**
   * Tests the lightning-subprofile generator for Drush.
   *
   * @param array $answers
   *   The answers to the generator's questions, keyed by the questions' machine
   *   names.
   *
   * @see \Drupal\lightning\Generators\SubProfileGenerator::interact()
   *
   * @dataProvider provider
   */
  public function test(array $answers = []) {
    // We need Drush to run commands defined by the Lightning profile, but we
    // don't want to actually install the Lightning profile into the test site
    // because it takes freaking forever. So, this hack is a quick way to expose
    // our generator to Drush.
    $this->config('core.extension')
      ->set('module.lightning', 0)
      ->set('profile', 'lightning')
      ->save();

    $answers += [
      'name' => 'Wizards',
      'description' => 'This is the description.',
      'install' => [],
      'exclude' => [],
    ];
    $answers['machine_name'] = 'wizards';
    $answers['exclusions'] = $answers['exclude'] ? 'Yes' : 'No';

    // Generate the profile relative to the site directory, so that it will be
    // automatically cleaned up when the test is done.
    $output_dir = $this->getDrupalRoot() . "/$this->siteDirectory";

    $this->drush('generate', ['lightning-subprofile'], [
      'answers' => Json::encode($answers),
      'directory' => $output_dir,
    ]);
    $this->assertFileExists("$output_dir/custom/wizards/wizards.info.yml");
    $this->assertFileExists("$output_dir/custom/wizards/wizards.install");
    $this->assertFileExists("$output_dir/custom/wizards/wizards.profile");

    $info = file_get_contents("$output_dir/custom/wizards/wizards.info.yml");
    $info = Yaml::decode($info);
    // Assert the constant values...
    $this->assertSame($answers['name'], $info['name']);
    $this->assertSame('profile', $info['type']);
    $this->assertArrayNotHasKey('core', $info);
    $this->assertNotEmpty($info['core_version_requirement']);
    $this->assertSame(['bartik', 'seven'], $info['themes']);
    $this->assertSame('lightning', $info['base profile']);

    // ...and the stuff that can change depending on user input.
    if ($answers['description']) {
      $this->assertSame($answers['description'], $info['description']);
    }
    else {
      $this->assertArrayNotHasKey('description', $info);
    }

    if ($answers['install']) {
      $this->assertSame($answers['install'], $info['install']);
    }
    else {
      $this->assertArrayNotHasKey('install', $info);
    }

    if ($answers['exclude']) {
      $component_discovery = new ComponentDiscovery($this->getDrupalRoot());;
      $components = array_keys($component_discovery->getAll());

      // The exclusions are submitted as a comma-separated string of numeric
      // array indices, so transform them into an array of integer indices.
      $exclude = SubProfileGenerator::toArray($answers['exclude']);
      $exclude = array_map('intval', $exclude);

      foreach ($exclude as $index) {
        $this->assertContains($components[$index], $info['exclude']);
      }
    }
    else {
      $this->assertArrayNotHasKey('exclude', $info);
    }
  }

}
