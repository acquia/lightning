<?php

/**
 * @file
 * Contains \Drupal\Tests\lightning_media\Unit\UploadControllerTest.
 */

namespace Drupal\Tests\lightning_media\Unit;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\lightning_media\Controller\UploadController;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\lightning_media\Controller\UploadController
 * @package lightning_media
 */
class UploadControllerTest extends UnitTestCase {

  protected $fileStorage;

  protected $mediaStorage;

  protected $renderer;

  protected $currentUser;

  protected $transliteration;

  protected $imageFactory;

  protected $controller;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->fileStorage = $this->prophesize(EntityStorageInterface::class);
    $this->mediaStorage = $this->prophesize(EntityStorageInterface::class);
    $this->renderer = $this->prophesize(RendererInterface::class);
    $this->currentUser = $this->prophesize(AccountInterface::class);
    $this->transliteration = $this->prophesize(TransliterationInterface::class);
    $this->imageFactory = $this->prophesize(ImageFactory::class);

    $this->controller = new UploadController(
      $this->fileStorage->reveal(),
      $this->mediaStorage->reveal(),
      $this->renderer->reveal(),
      $this->currentUser->reveal(),
      $this->transliteration->reveal(),
      $this->imageFactory->reveal()
    );
  }

  /**
   * @covers ::upload
   */
  public function testUploadPostNoFile() {
    /** @var \Symfony\Component\HttpFoundation\JsonResponse $response */
    $response = $this->controller->upload(new Request());
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals('{}', $response->getContent());
  }

  /**
   * @covers ::upload
   */
  public function testUploadPostFile() {
  }

}

/** Legacy functions which have not yet been refactored into services. **/

function file_destination($uri) {
  return $uri;
}

function file_uri_target($uri) {
  return str_replace('public://', NULL, $uri);
}
