<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\MediaBundle.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Defines the Media bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "media_bundle",
 *   label = @Translation("Media bundle"),
 *   handlers = {
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
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "type",
 *     "queue_thumbnail_downloads",
 *     "third_party_settings",
 *     "type_configuration",
 *     "field_map",
 *     "status",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/media/manage/{media_bundle}",
 *     "delete-form" = "/admin/structure/media/manage/{media_bundle}/delete",
 *     "collection" = "/admin/structure/media",
 *   }
 * )
 */
class MediaBundle extends ConfigEntityBundleBase implements MediaBundleInterface, EntityWithPluginCollectionInterface {

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
   * A brief description of this media bundle.
   *
   * @var string
   */
  public $description;

  /**
   * The type plugin id.
   *
   * @var string
   */
  public $type = 'generic';

  /**
   * Are thumbnail downloads queued.
   *
   * @var bool
   */
  public $queue_thumbnail_downloads = FALSE;

  /**
   * The type plugin configuration.
   *
   * @var array
   */
  public $type_configuration = array();

  /**
   * Type lazy plugin collection.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $typePluginCollection;

  /**
   * Field map. Fields provided by type plugin to be stored as entity fields.
   *
   * @var array
   */
  public $field_map = array();

  /**
   * Default status of this media bundle.
   *
   * @var array
   */
  public $status = TRUE;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array(
      'type_configuration' => $this->typePluginCollection(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel(MediaInterface $media) {
    $bundle = entity_load('media_bundle', $media->bundle());
    return $bundle ? $bundle->label() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function exists($id) {
    return (bool) static::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeConfiguration() {
    return $this->type_configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setTypeConfiguration($configuration) {
    $this->type_configuration = $configuration;
    $this->typePluginCollection = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueThumbnailDownloads() {
    return $this->queue_thumbnail_downloads;
  }

  /**
   * {@inheritdoc}
   */
  public function setQueueThumbnailDownloads($queue_thumbnail_downloads) {
    $this->queue_thumbnail_downloads = $queue_thumbnail_downloads;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->typePluginCollection()->get($this->type);
  }

  /**
   * Returns type lazy plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The tag plugin collection.
   */
  protected function typePluginCollection() {
    if (!$this->typePluginCollection) {
      $this->typePluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.media_entity.type'), $this->type, $this->type_configuration);
    }
    return $this->typePluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }
}
