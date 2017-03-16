<?php

namespace Drupal\lightning_media;

/**
 * Defines the interface for a preview handler.
 *
 * Preview handlers are responsible for altering media entity forms in order
 * to provide and manage a live preview of the entity. Different media types
 * can handle preview differently (depending on the source field type and other
 * factors), so entity forms which will have a preview should use an
 * appropriate implementation of this interface.
 *
 * @deprecated in Lightning 2.0.5 and will be removed in Lightning 2.1.0. Set
 * the 'preview' key on the media type plugin definition instead.
 */
interface PreviewHandlerInterface {
}
