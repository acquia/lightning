<?php

namespace Drupal\Tests\lightning_api\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_api\Form\OAuthKeyForm;
use Drupal\lightning_api\OAuthKey;

/**
 * @group lightning
 * @group lightning_api
 */
class OAuthKeyFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_api',
    'system',
  ];

  public function testForm() {
    $dir = drupal_realpath('temporary://');

    $form_state = (new FormState)->setValues([
      'dir' => $dir,
      'private_key' => 'private.key',
      'public_key' => 'public.key',
    ]);

    $this->container
      ->get('form_builder')
      ->submitForm(OAuthKeyForm::class, $form_state);

    $this->assertKey($dir . '/private.key');
    $this->assertKey($dir . '/public.key');
  }

  private function assertKey($path) {
    $this->assertFileExists($path);
    $this->assertSame(OAuthKey::PERMISSIONS, fileperms($path) & 0777);
    drupal_unlink($path);
  }

}
