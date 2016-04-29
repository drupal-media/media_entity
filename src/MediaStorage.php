<?php

namespace Drupal\media_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Media storage class.
 */
class MediaStorage extends SqlContentEntityStorage implements MediaStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function onBundleRename($bundle, $bundle_new, $entity_type_id) {
    parent::onBundleRename($bundle, $bundle_new, $entity_type_id);
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
