<?php

namespace Drupal\Tests\lightning\Kernel;

use Drupal\Core\Extension\Extension;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\ComponentDiscovery;

/**
 * @group lightning
 *
 * @coversDefaultClass \Drupal\lightning\ComponentDiscovery
 */
class ComponentDiscoveryTest extends KernelTestBase {

  /**
   * The ComponentDiscovery under test.
   *
   * @var \Drupal\lightning\ComponentDiscovery
   */
  protected $discovery;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->discovery = new ComponentDiscovery(
      $this->container->get('app.root')
    );
  }

  /**
   * @covers ::getAll
   */
  public function testGetAll() {
    $components = $this->discovery->getAll();

    $this->assertInstanceOf(Extension::class, $components['lightning_core']);
    $this->assertInstanceOf(Extension::class, $components['lightning_search']);
    $this->assertInstanceOf(Extension::class, $components['lightning_dev']);
    $this->assertArrayNotHasKey('panels', $components);
    $this->assertArrayNotHasKey('views', $components);
  }

  /**
   * @covers ::getMainComponents
   */
  public function testGetMainComponents() {
    $components = $this->discovery->getMainComponents();

    $this->assertInstanceOf(Extension::class, $components['lightning_api']);
    $this->assertInstanceOf(Extension::class, $components['lightning_core']);
    $this->assertInstanceOf(Extension::class, $components['lightning_layout']);
    $this->assertInstanceOf(Extension::class, $components['lightning_media']);
    $this->assertInstanceOf(Extension::class, $components['lightning_workflow']);

    $this->assertArrayNotHasKey('lightning_contact_form', $components);
    $this->assertArrayNotHasKey('lightning_page', $components);
    $this->assertArrayNotHasKey('lightning_roles', $components);
    $this->assertArrayNotHasKey('lightning_search', $components);
  }

  /**
   * @covers ::getSubComponents
   */
  public function testGetSubComponents() {
    $components = $this->discovery->getSubComponents();

    $this->assertInstanceOf(Extension::class, $components['lightning_contact_form']);
    $this->assertInstanceOf(Extension::class, $components['lightning_page']);
    $this->assertInstanceOf(Extension::class, $components['lightning_roles']);
    $this->assertInstanceOf(Extension::class, $components['lightning_search']);
    $this->assertArrayNotHasKey('lightning_core', $components);
  }

}
