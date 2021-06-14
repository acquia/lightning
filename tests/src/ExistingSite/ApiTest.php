<?php

namespace Drupal\Tests\lightning\ExistingSite;

use Drupal\Component\Serialization\Yaml;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests the decoupled API shipped with the Lightning profile.
 *
 * @group lightning
 */
class ApiTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    $config_factory->getEditable('lightning_api.settings')
      ->set('entity_json', TRUE)
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
   * Tests viewing a configuration entity as JSON via the API.
   */
  public function testViewConfigEntityAsJson() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/structure/contact');
    $page->clickLink('View JSON');
    $assert_session->statusCodeEquals(200);

    $this->drupalGet('/admin/structure/media');
    $page->clickLink('View JSON');
    $assert_session->statusCodeEquals(200);
  }

}
