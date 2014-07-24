<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\Media.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\media_entity\MediaInterface;

/**
 * Defines the media entity class.
 *
 * @ContentEntityType(
 *   id = "media",
 *   label = @Translation("Media"),
 *   bundle_label = @Translation("Media bundle"),
 *   controllers = {
 *     "storage" = "Drupal\media_entity\MediaStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\media_entity\MediaAccessController",
 *     "form" = {
 *       "default" = "Drupal\media_entity\MediaForm",
 *       "delete" = "Drupal\media_entity\Form\MediaDeleteForm",
 *       "edit" = "Drupal\media_entity\MediaForm"
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "media",
 *   data_table = "media_field_data",
 *   revision_table = "media_revision",
 *   revision_data_table = "media_field_revision",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   render_cache = TRUE,
 *   entity_keys = {
 *     "id" = "mid",
 *     "revision" = "vid",
 *     "bundle" = "bundle",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "media_bundle",
 *   permission_granularity = "entity_type",
 *   admin_permission = "administer media",
 *   links = {
 *     "canonical" = "media.view",
 *     "edit-form" = "media.edit",
 *     "admin-form" = "media.bundle_edit"
 *   }
 * )
 */
class Media extends ContentEntityBase implements MediaInterface {

  /**
   * Value that represents the media being published.
   */
  const PUBLISHED = 1;

  /**
   * Value that represents the media being unpublished.
   */
  const NOT_PUBLISHED = 0;

  /**
   * {@inheritdoc}
   */
  public function getBundle() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? Media::PUBLISHED : Media::NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPublisher() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getPublisherId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublisherId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->get('type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setType($type) {
    $this->set('type', $type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceId() {
    return $this->get('resource_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setResourceId($id) {
    $this->set('resource_id', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no owner has been set explicitly, make the current user the owner.
    if (!$this->getPublisher()) {
      $this->setPublisherId(\Drupal::currentUser()->id());
    }
    // If no revision author has been set explicitly, make the media owner the
    // revision author.
    if (!$this->get('revision_uid')->entity) {
      $this->set('revision_uid', $this->getPublisherId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing node without adding a new revision, we
      // need to make sure $entity->revision_log is reset whenever it is empty.
      // Therefore, this code allows us to avoid clobbering an existing log
      // entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['mid'] = FieldDefinition::create('integer')
      ->setLabel(t('Media ID'))
      ->setDescription(t('The media ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The media UUID.'))
      ->setReadOnly(TRUE);

    $fields['vid'] = FieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The media revision ID.'))
      ->setReadOnly(TRUE);

    $fields['bundle'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Bundle'))
      ->setDescription(t('The media bundle.'))
      ->setSetting('target_type', 'media_bundle')
      ->setReadOnly(TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The media language code.'))
      ->setRevisionable(TRUE);

    $fields['name'] = FieldDefinition::create('string')
      ->setLabel(t('Media name'))
      ->setDescription(t('The name of this media.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Publisher ID'))
      ->setDescription(t('The user ID of the media publisher.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(0)
      ->setSetting('target_type', 'user');

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the media is published.'));

    $fields['created'] = FieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the media was created.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    $fields['changed'] = FieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the media was last edited.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    $fields['type'] = FieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of this media.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['resource_id'] = FieldDefinition::create('string')
      ->setLabel(t('Resource ID'))
      ->setDescription(t('The unique identifier of media resource that is associated with this media.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDefaultValue('');

    $fields['revision_timestamp'] = FieldDefinition::create('timestamp')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Revision publisher ID'))
      ->setDescription(t('The user ID of the publisher of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_log'] = FieldDefinition::create('string_long')
      ->setLabel(t('Revision Log'))
      ->setDescription(t('The log entry explaining the changes in this revision.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
