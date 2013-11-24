<?php

/**
 * @file
 * Contains of \Drupal\media_entity\MediaStorageController.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
 * Controller class for media.
 */
class MediaStorageController extends FieldableDatabaseStorageController implements MediaStorageControllerInterface {

  /**
   * {@inheritdoc}.
   */
  public function renameBundle($old_id, $new_id) {
    db_update('media')
      ->fields(array('bundle' => $new_id))
      ->condition('bundle', $old_id)
      ->execute();

    db_update('media_field_data')
      ->fields(array('bundle' => $new_id))
      ->condition('bundle', $old_id)
      ->execute();
  }
}
