<?php

namespace Drupal\lightning_media_document\Plugin\MediaEntity\Type;

use Drupal\lightning_media\FileInputExtensionMatchTrait;
use Drupal\lightning_media\InputMatchInterface;
use Drupal\lightning_media\SourceFieldInterface;
use Drupal\lightning_media\SourceFieldPluginTrait;
use Drupal\media_entity_document\Plugin\MediaEntity\Type\Document as BaseDocument;

/**
 * Input-matching version of the Document media type.
 */
class Document extends BaseDocument implements InputMatchInterface, SourceFieldInterface {

  use FileInputExtensionMatchTrait;
  use SourceFieldPluginTrait;

}
