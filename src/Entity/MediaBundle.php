<?php

namespace Drupal\media_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\SourceFieldInterface;

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
 *       "delete" = "Drupal\media_entity\Form\MediaBundleDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\media_entity\MediaBundleListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer media bundles",
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
 *     "new_revision",
 *     "third_party_settings",
 *     "type_configuration",
 *     "field_map",
 *     "status",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/media/add",
 *     "edit-form" = "/admin/structure/media/manage/{media_bundle}",
 *     "delete-form" = "/admin/structure/media/manage/{media_bundle}/delete",
 *     "collection" = "/admin/structure/media",
 *   }
 * )
 */
class MediaBundle extends ConfigEntityBundleBase implements MediaBundleInterface, EntityWithPluginCollectionInterface, EntityDescriptionInterface {

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
   * Default value of the 'Create new revision' checkbox of this media bundle.
   *
   * @var bool
   */
  protected $new_revision = FALSE;

  /**
   * The type plugin configuration.
   *
   * @var array
   */
  public $type_configuration = [];

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
  public $field_map = [];

  /**
   * Default status of this media bundle.
   *
   * @var bool
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
    return [
      'type_configuration' => $this->typePluginCollection(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel(MediaInterface $media) {
    $bundle = static::load($media->bundle());
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
  public function setDescription($description) {
    $this->description = $description;
    return $this;
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

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    $this->new_revision = $new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If the handler uses a source field, we'll need to store its name before
    // saving. We'd need to double-save if we did this in postSave().
    $handler = $this->getType();
    if ($handler instanceof SourceFieldInterface) {
      $storage = $handler->getSourceField($this)->getFieldStorageDefinition();
      // If the field storage is a new (unsaved) config entity, save it.
      if ($storage instanceof FieldStorageConfigInterface && $storage->isNew()) {
        $storage->save();
      }
      // Store the field name. We always want to update this value because the
      // field name may have changed, or a new field may have been created,
      // depending on the user's actions or the handler's behavior.
      $configuration = $handler->getConfiguration();
      $configuration['source_field'] = $storage->getName();
      $this->setTypeConfiguration($configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // If the handler is using a source field, we may need to save it if it's
    // new. The field storage is guaranteed to exist already because preSave()
    // took care of that.
    $handler = $this->getType();
    if ($handler instanceof SourceFieldInterface) {
      $field = $handler->getSourceField($this);

      // If the field is new, save it and add it to this bundle's view and form
      // displays.
      if ($field->isNew()) {
        // Ensure the field is saved correctly before adding it to the displays.
        $field->save();

        $entity_type = $field->getTargetEntityTypeId();
        $bundle = $field->getTargetBundle();

        if ($field->isDisplayConfigurable('form')) {
          // Use the default widget and settings.
          $component = \Drupal::service('plugin.manager.field.widget')
            ->prepareConfiguration($field->getType(), []);

          entity_get_form_display($entity_type, $bundle, 'default')
            ->setComponent($field->getName(), $component)
            ->save();
        }
        if ($field->isDisplayConfigurable('view')) {
          // Use the default formatter and settings.
          $component = \Drupal::service('plugin.manager.field.formatter')
            ->prepareConfiguration($field->getType(), []);

          entity_get_display($entity_type, $bundle, 'default')
            ->setComponent($field->getName(), $component)
            ->save();
        }
      }
    }
  }

}
