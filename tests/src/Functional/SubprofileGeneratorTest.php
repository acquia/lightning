<?php


namespace Drupal\Tests\lightning\Functional;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Note: Drush must be installed. See
 * https://cgit.drupalcode.org/devel/tree/drupalci.yml?h=8.x-2.x and its docs
 * at
 * https://www.drupal.org/drupalorg/docs/drupal-ci/customizing-drupalci-testing-for-projects
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
   * {@inheritdoc}
   */
  protected $profile = 'lightning';

  /**
   * Tests drush commands.
   */
  public function testCommands() {
    $this->drush('generate', ['lightning-subprofile'], ['answers' => '{"name": "Wizards", "machine_name": "wizards", "description": "This is the description.", "install": "consumers", "exclusions": "Yes", "exclude": "2, 3"}']);
    $output = $this->getOutput();
    $this->assertContains('profiles/custom/wizards/wizards.info.yml', $output);
  }

}
