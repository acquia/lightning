<?php

namespace Drupal\lightning_media\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the MediaBundleResolver annotation.
 *
 * Bundle resolvers are plugins, defined by Lightning Media, that can take
 * indeterminate input of any type and determine if that input can be handled
 * by any existing media bundles. The input could be anything -- a string (i.e.,
 * an embed code or URL), a complete entity (i.e., an uploaded file), or
 * whatever else.
 *
 * Resolvers should return a fully-loaded media bundle entity if they find one
 * that will work for a given input, or FALSE if not.
 *
 * @Annotation
 */
class MediaBundleResolver extends Plugin {

  /**
   * Which field (input) types the resolver can handle.
   *
   * This should be field_types in a plugin annotation, but PHPCS flags an
   * error for non-camelCased properties.
   *
   * @var string[]
   */
  public $fieldTypes = [];

}
