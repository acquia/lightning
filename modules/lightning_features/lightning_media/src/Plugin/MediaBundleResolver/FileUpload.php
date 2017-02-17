<?php

namespace Drupal\lightning_media\Plugin\MediaBundleResolver;

use Drupal\lightning_media\BundleResolverBase;

/**
 * Bundle resolver for uploaded files.
 *
 * @deprecated in Lightning 2.0.4 and will be removed in Lightning 3.x. Media
 * type plugins should implement InputMatchInterface directly instead.
 *
 * @MediaBundleResolver(
 *   id = "file_upload"
 * )
 */
class FileUpload extends BundleResolverBase {
}
