<?php

namespace Drupal\lightning_media_document\Plugin\media\Source;

use Drupal\lightning_media\FileInputExtensionMatchTrait;
use Drupal\lightning_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\File as BaseFile;

/**
 * Input-matching version of the File media source.
 */
class File extends BaseFile implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
