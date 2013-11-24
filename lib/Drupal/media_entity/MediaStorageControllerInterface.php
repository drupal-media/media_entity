<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaStorageControllerInterface.
 */

namespace Drupal\media_entity;

/**
 * Provides an interface defining a media storage controller.
 */
interface MediaStorageControllerInterface {
  /**
   * Propagates rename of media bundle to all media entities that use it.
   *
   * @param string $old_id
   *   Old bundle ID.
   * @param string $new_id
   *   New bundle ID.
   *
   * @return
   *   The number of rows matched by the update.
   */
  public function renameBundle($old_id, $new_id);
}
