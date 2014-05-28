<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\MediaBundle.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
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
 *       "add" = "Drupal\media_entity\MediaBundleForm",
 *       "edit" = "Drupal\media_entity\MediaBundleForm",
 *       "delete" = "Drupal\media_entity\Form\MediaBundleDeleteForm"
 *     },
 *     "list_builder" = "Drupal\media_entity\MediaBundleListBuilder",
 *   },
 *   admin_permission = "administer media",
 *   config_prefix = "bundle",
 *   bundle_of = "media",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "type" = "type",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "media.bundle_edit",
 *     "delete-form" = "media.bundle_delete_confirm"
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
   * The human-readable name of the media bundle.
   *
   * @var string
   */
  public $label;

  /**
   * The type of this media bundle.
   *
   * @var string
   */
  public $type;

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
   * Returns the type of the media bundle.
   */
  public function type() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage_controller, $update = TRUE) {
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
  public static function postDelete(EntityStorageInterface $storage_controller, array $entities) {
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
