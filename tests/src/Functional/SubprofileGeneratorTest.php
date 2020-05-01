<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Serialization\Yaml;
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
          'exclude' => 'lightning_search, lightning_media_instagram',
        ],
      ],
      'inclusions and exclusions' => [
        'answers' => [
          'install' => ['consumers', 'book'],
          'exclude' => 'lightning_media_instagram',
        ],
      ],
      'default answers' => [
        'answers' => [],
      ],
      'specific machine name and description' => [
        'answers' => [
          'machine_name' => 'something_different',
          'description' => 'This profile rules.',
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
    $drupal_root = $this->getDrupalRoot();
    $this->assertFileExists("$drupal_root/../composer.json", 'A composer.json file must exist in the directory above the Drupal root.');

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
      'machine_name' => 'wizards',
      // Ideally, these would be empty strings, but it seems that Symfony
      // Console (or possibly Drush) mangles empty strings in JSON-encoded data,
      // results in the --answers option containing invalid JSON. Setting these
      // to NULL is a workaround, but it achieves the desired result.
      'description' => NULL,
      'install' => NULL,
      'exclude' => NULL,
    ];
    $options = [
      'answers' => Json::encode($answers),
      // Generate the profile in the test site's directory, so that it will be
      // automatically cleaned up when the test is done.
      'directory' => "$drupal_root/$this->siteDirectory/profiles/custom",
    ];

    // The generator will ask if we want to exclude any components. We need to
    // explicitly answer the prompt either way, since we are running the command
    // quasi-interactively.
    if ($answers['exclude']) {
      $options['yes'] = NULL;
    }
    else {
      $options['no'] = NULL;
    }

    $machine_name = $answers['machine_name'];
    // DrushTestTrait::drush() will invoke the command with the --no-interaction
    // option, bypassing the generator's interact() method -- which contains all
    // the logic we want to test! The SHELL_INTERACTIVE environment variable
    // forces Symfony Console to run the method anyway.
    $this->drush('generate', ['lightning-subprofile'], $options, NULL, "$drupal_root/..", 0, NULL, [
      'SHELL_INTERACTIVE' => 1,
    ]);

    // Ensure that the new profile is picked up by the extension system. Because
    // ExtensionDiscovery does not expect the available extensions to change in
    // the course of a single request, and has no public method to reset its
    // internal static cache, we need to pry open its static $files property
    // and force-reset it.
    $reflector = new \ReflectionClass('\Drupal\Core\Extension\ExtensionDiscovery');
    $property = $reflector->getProperty('files');
    $property->setAccessible(TRUE);
    $property->setValue([]);

    $extension = $this->container->get('extension.list.profile')
      ->reset()
      ->get($machine_name);

    // Get the raw info for the generated profile. The profile list's
    // getExtensionInfo() method would merge in default values from the
    // extension system and ancestral profiles, which interfere with these
    // assertions.
    $info = file_get_contents($extension->getPathname());
    $info = Yaml::decode($info);

    // Assert the constant values.
    $this->assertSame($answers['name'], $info['name']);
    $this->assertSame('profile', $info['type']);
    $this->assertArrayNotHasKey('core', $info);
    $this->assertNotEmpty($info['core_version_requirement']);
    $this->assertSame(['bartik', 'seven'], $info['themes']);
    $this->assertSame('lightning', $info['base profile']);

    // Assert the stuff that can change depending on user input.
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
      $exclude = SubProfileGenerator::toArray($answers['exclude']);

      foreach ($exclude as $module) {
        $this->assertContains($module, $info['exclude']);
      }
    }
    else {
      $this->assertArrayNotHasKey('exclude', $info);
    }

    // Load up the new profile to ensure it's valid PHP and includes an install
    // hook.
    $module_handler = $this->container->get('module_handler');
    $module_handler->addProfile($machine_name, $extension->getPath());
    $module_handler->load($machine_name);
    $module_handler->loadInclude($machine_name, 'install');
    $this->assertTrue(function_exists($machine_name . '_install'));

    // Ensure the .profile file at least includes a comment.
    $file = $module_handler->getModule($machine_name)->getExtensionPathname();
    $this->assertNotEmpty(file_get_contents($file));
  }

}
