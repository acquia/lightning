<?php

namespace Drupal\lightning_media;

/**
 * Interface for media bundle resolvers.
 *
 * Media bundle resolvers are plugins which determine which media bundle(s) are
 * appropriate for handling an input value. The input value can be of any type,
 * and the resolver needs to figure out which media bundle -- singular! -- is
 * best suited to handle that input.
 *
 * @deprecated in Lightning 2.0.4 and will be removed in Lightning 3.x. Media
 * type plugins should implement InputMatchInterface directly instead.
 */
interface BundleResolverInterface {
}
