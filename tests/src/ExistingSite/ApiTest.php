<?php

namespace Drupal\Tests\lightning\ExistingSite;

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

    $this->container->get('config.factory')
      ->getEditable('lightning_api.settings')
      ->set('entity_json', TRUE)
      ->save();
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
