<?php

namespace Drupal\Tests\lightning\ExistingSite;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\Entity\EntityViewMode;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests specialized handling for view modes.
 *
 * @group lightning
 */
class ViewModeTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode */
    $view_mode = EntityViewMode::create([
      'id' => 'node.foobaz',
      'label' => 'Foobaz',
      'targetEntityType' => 'node',
    ]);
    $view_mode
      ->setThirdPartySetting('lightning_core', 'internal', TRUE)
      ->setThirdPartySetting('lightning_core', 'description', 'Behold, my glorious view mode.')
      ->save();

    // If the samlauth module is installed, ensure that it is configured (in
    // this case, using its own test data) to avoid errors when creating user
    // accounts in this test.
    if ($this->container->get('module_handler')->moduleExists('samlauth')) {
      $path = $this->container->get('extension.list.module')
        ->getPath('samlauth');
      $data = file_get_contents("$path/test_resources/samlauth.authentication.yml");
      $data = Yaml::decode($data);

      $this->container->get('config.factory')
        ->getEditable('samlauth.authentication')
        ->setData($data)
        ->save();
    }
  }

  /**
   * Tests that users are informed about internal view modes.
   */
  public function testInternalViewMode() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->createUser(['administer node display']);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/structure/types/manage/page/display');
    $page->checkField('Foobaz');
    $page->pressButton('Save');
    $assert_session->elementTextContains('css', '.messages--status', 'The Foobaz mode now uses custom display settings.');
    $page->find('css', '.messages--status')->clickLink('configure them');
    $assert_session->elementTextContains('css', '.messages--warning', "This display is internal and will not be seen by normal users.");
    $assert_session->pageTextContains('Behold, my glorious view mode.');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    EntityViewMode::load('node.foobaz')->delete();
    parent::tearDown();
  }

}
