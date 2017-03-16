<?php

namespace Drupal\Tests\lightning_media\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * @group lightning
 * @group lightning_media
 */
class MediaReferenceFieldTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning';

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Toolbar does a JS thing that PhantomJS 1.x doesn't like.
    $this->container
      ->get('module_installer')
      ->uninstall(['toolbar']);
  }

  public function testMediaReferenceField() {
    // Log in as an administrator.
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    // Create an entity reference field that references media.
    $this->drupalGet('/admin/structure/types/manage/page/fields/add-field');

    $values = [
      'new_storage_type' => 'entity_reference',
      'label' => 'Foobar',
      'field_name' => 'foobar',
    ];
    $this->submitForm($values, 'Save and continue', 'field-ui-field-storage-add-form');

    $values = [
      'settings[target_type]' => 'media',
    ];
    $this->submitForm($values, 'Save field settings', 'field-storage-config-edit-form');

    $page = $this->getSession()->getPage();
    $page->checkField('settings[handler_settings][target_bundles][image]');
    $this->awaitAjax();
    $page->pressButton('Save settings');

    // Assert that the correct widget is used.
    $component = entity_get_form_display('node', 'page', 'default')
      ->getComponent('field_foobar');
    $this->assertInternalType('array', $component);
    $this->assertEquals('entity_browser_entity_reference', $component['type']);
  }

  /**
   * Waits for AJAX requests to complete.
   */
  protected function awaitAjax() {
    $this->assertJsCondition('(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(":animated").length))');
  }

}
