<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\MediaBundle.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;

/**
 * Defines the Media bundle configuration entity.
 *
 * @EntityType(
 *   id = "media_bundle",
 *   label = @Translation("Content type"),
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "access" = "Drupal\media_entity\MediaBundleAccessController",
 *     "form" = {
 *       "add" = "Drupal\media_entity\MediaBundleFormController",
 *       "edit" = "Drupal\media_entity\MediaBundleFormController",
 *       "delete" = "Drupal\media_entity\Form\MediaBundleDeleteConfirm"
 *     },
 *     "list" = "Drupal\media_entity\MediaBundleListController",
 *   },
 *   admin_permission = "administer media bundles",
 *   config_prefix = "media_entity.bundle",
 *   bundle_of = "media",
 *   entity_keys = {
 *     "id" = "bundle",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "media_entity.type_edit"
 *   }
 * )
 */
class MediaBundle extends ConfigEntityBase implements MediaBundleInterface {

  /**
   * The machine name of this node type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  public $bundle;

  /**
   * The UUID of the node type.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable name of the node type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  public $name;

  /**
   * A brief description of this node type.
   *
   * @var string
   */
  public $description;

  /**
   * Help information shown to the user when creating a Node of this type.
   *
   * @var string
   */
  public $help;

  /**
   * Module-specific settings for this node type, keyed by module name.
   *
   * @var array
   *
   * @todo Pluginify.
   */
  public $settings = array();

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleSettings($module) {
    if (isset($this->settings[$module]) && is_array($this->settings[$module])) {
      return $this->settings[$module];
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('media_entity.bundle.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if (!$update) {
      // Clear the node type cache, so the new type appears.
      \Drupal::cache()->deleteTags(array('media_bundles' => TRUE));

      entity_invoke_bundle_hook('create', 'media', $this->id());
    }
    elseif ($this->getOriginalID() != $this->id()) {
      // Clear the node type cache to reflect the rename.
      \Drupal::cache()->deleteTags(array('media_bundles' => TRUE));

      // @todo update existing media entities.

      entity_invoke_bundle_hook('rename', 'media', $this->getOriginalID(), $this->id());
    }
    else {
      // Invalidate the cache tag of the updated node type only.
      cache()->invalidateTags(array('media_bundle' => $this->id()));
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

}
