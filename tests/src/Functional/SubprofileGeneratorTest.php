<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Note: Drush must be installed.
 *
 * See https://cgit.drupalcode.org/devel/tree/drupalci.yml?h=8.x-2.x and its docs
 * at
 * https://www.drupal.org/drupalorg/docs/drupal-ci/customizing-drupalci-testing-for-projects.
 */

/**
 * @coversDefaultClass \Drupal\lightning\Generators\SubProfileGenerator
 * @group devel
 */
class SubprofileGeneratorTest extends BrowserTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests drush commands.
   */
  public function testCommands() {
    $this->config('core.extension')->set('module.lightning', 0)->set('profile', 'lightning')->save();
    $this->assertFalse(file_exists('profiles/custom/wizards/wizards.info.yml'));
    $this->drush('generate', ['lightning-subprofile'], ['answers' => '{"name": "Wizards", "machine_name": "wizards", "description": "This is the description.", "exclusions": "No"}']);
    $output = $this->getOutput();
    $this->assertContains('profiles/custom/wizards/wizards.info.yml', $output);
    $this->assertTrue(file_exists('profiles/custom/wizards/wizards.info.yml'));
    $this->assertContains('name: Wizards', file_get_contents('profiles/custom/wizards/wizards.info.yml'));
  }

}
