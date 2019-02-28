<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning
 */
class ModerationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_landing_page',
    'lightning_page',
    'lightning_roles',
    'lightning_workflow',
  ];

  /**
   * @dataProvider provider
   */
  public function test($node_type, $role) {
    $assert_session = $this->assertSession();

    $account = $this->drupalCreateUser([], NULL, $role === 'administrator');
    $account->addRole($role);
    $this->assertSame(SAVED_UPDATED, $account->save());
    $this->drupalLogin($account);

    $this->drupalGet("/node/add/$node_type");
    $assert_session->statusCodeEquals(200);
    $assert_session->buttonExists('Save');
    $assert_session->fieldNotExists('status[value]');
    $assert_session->buttonNotExists('Save and publish');
    $assert_session->buttonNotExists('Save as unpublished');
  }

  public function provider() {
    return [
      ['page', 'page_creator'],
      ['page', 'administrator'],
      ['landing_page', 'landing_page_creator'],
      ['landing_page', 'administrator'],
    ];
  }

}
