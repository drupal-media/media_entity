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
   * {@inheritdoc}
   */
  public function onBundleRename($bundle, $bundle_new) {
    parent::onBundleRename($bundle, $bundle_new);
    // Update media entities with a new bundle.
    $this->database->update('media')
      ->fields(array('bundle' => $bundle_new))
      ->condition('bundle', $bundle)
      ->execute();
    $this->database->update('media_field_data')
      ->fields(array('bundle' => $bundle_new))
      ->condition('bundle', $bundle)
      ->execute();
  }

}
