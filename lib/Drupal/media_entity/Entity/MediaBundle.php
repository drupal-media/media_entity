<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\MediaBundle.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Defines the Media bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "media_bundle",
 *   label = @Translation("Media bundle"),
 *   controllers = {
 *     "form" = {
 *       "add" = "Drupal\media_entity\MediaBundleFormController",
 *       "edit" = "Drupal\media_entity\MediaBundleFormController",
 *       "delete" = "Drupal\media_entity\Form\MediaBundleDeleteForm"
 *     },
 *     "list" = "Drupal\media_entity\MediaBundleListController",
 *   },
 *   admin_permission = "administer media",
 *   config_prefix = "bundle",
 *   bundle_of = "media",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "media.bundle_edit"
 *   }
 * )
 */
class MediaBundle extends ConfigEntityBase implements MediaBundleInterface {

  /**
   * The machine name of this media bundle.
   *
   * @var string
   */
  public $id;

  /**
   * The UUID of the media bundle.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable name of the media bundle.
   *
   * @var string
   */
  public $label;

  /**
   * A brief description of this media bundle.
   *
   * @var string
   */
  public $description;


  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if (!$update) {
      // Clear the media bundle cache, so the new bundle appears.
      \Drupal::cache()->deleteTags(array('media_bundles' => TRUE));

      entity_invoke_bundle_hook('create', 'media', $this->id());
    }
    elseif ($this->getOriginalID() != $this->id()) {
      // Clear the media bundle cache to reflect the rename.
      \Drupal::cache()->deleteTags(array('media_bundles' => TRUE));

      // Update bundle id with corresponding media.
      entity_invoke_bundle_hook('rename', 'media', $this->getOriginalID(), $this->id());
    }
    else {
      // Invalidate the cache tag of the updated media bundle only.
      Cache::invalidateTags(array('media_bundle' => $this->id()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::postDelete($storage_controller, $entities);

    // Clear the media bundle cache to reflect the removal.
    $storage_controller->resetCache(array_keys($entities));
    foreach ($entities as $entity) {
      entity_invoke_bundle_hook('delete', 'media', $entity->id());
    }
  }

  public static function getLabel(MediaInterface $media) {
    $bundle = entity_load('media_bundle', $media->bundle());
    return $bundle ? $bundle->label() : FALSE;
  }

  public static function exists($id) {
    return (bool) entity_load('media_bundle', $id);
  }

}
