<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Core\Update\UpdateKernel;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests the update path from Lightning 4.x.
 *
 * @group lightning
 */
class UpdatePath4xTest extends UpdatePathTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/4.1.0-beta1.php.gz',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function replaceUser1() {
    // When the parent method re-saves the user account, an obscure code path
    // through Layout Builder and Lightning Media is triggered, resulting in
    // the use of an old media source plugin ID that will not exist after the
    // updates are run. Normally, Lightning Media accounts for this by aliasing
    // the old plugin ID, but that is only done if the kernel is an
    // UpdateKernel...which is NOT the case in this test's memory space. So,
    // although this is dirty and brittle, I don't know of a better way around
    // it; we simply need to have a (mocked) UpdateKernel in place when the
    // media source plugin definition cache is rebuilt.
    $kernel = $this->container->get('kernel');
    $this->container->set('kernel', $this->prophesize(UpdateKernel::class)->reveal());

    // Clear the cached plugin definitions, then rebuild them. The fact that
    // an UpdateKernel is present means that the old media source plugin ID
    // should be aliased and cached correctly.
    // @see lightning_media_instagram_media_source_info_alter()
    $plugin_manager = $this->container->get('plugin.manager.media.source');
    $plugin_manager->clearCachedDefinitions();
    $plugin_manager->getDefinitions();

    // Restore the real kernel and proceed merrily on our way.
    $this->container->set('kernel', $kernel);
    parent::replaceUser1();
  }

  /**
   * Tests updating from Lightning 4.1.0-beta1 via the UI.
   */
  public function test() {
    require_once __DIR__ . '/../../update.php';
    $this->getRandomGenerator()->image('public://star.png', '16x16', '16x16');
    $this->runUpdates();
    $this->drush('update:lightning', [], ['yes' => NULL]);
  }

}
