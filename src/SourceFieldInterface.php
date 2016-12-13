<?php

namespace Drupal\media_entity;

/**
 * Interface for media type plugins that depend on a field.
 */
interface SourceFieldInterface extends MediaTypeInterface {

  /**
   * Returns the source field for a bundle using this plugin.
   *
   * @param \Drupal\media_entity\MediaBundleInterface $bundle
   *   The media bundle.
   *
   * @return \Drupal\field\FieldConfigInterface
   */
  public function getSourceField(MediaBundleInterface $bundle);

}
