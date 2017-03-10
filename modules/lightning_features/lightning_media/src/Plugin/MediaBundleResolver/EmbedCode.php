<?php

namespace Drupal\lightning_media\Plugin\MediaBundleResolver;

use Drupal\lightning_media\BundleResolverBase;

/**
 * Bundle resolver for embed codes.
 *
 * @deprecated in Lightning 2.0.4 and will be removed in Lightning 2.1.0. Media
 * type plugins should implement InputMatchInterface directly instead.
 *
 * @MediaBundleResolver(
 *   id = "embed_code"
 * )
 */
class EmbedCode extends BundleResolverBase {
}
