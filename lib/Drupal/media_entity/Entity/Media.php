<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\Media.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\media_entity\MediaInterface;

/**
 * Defines the media entity class.
 *
 * @EntityType(
 *   id = "media",
 *   label = @Translation("Media"),
 *   bundle_label = @Translation("Media bundle"),
 *   controllers = {
 *     "storage" = "Drupal\media_entity\MediaStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\media_entity\MediaAccessController",
 *     "form" = {
 *       "default" = "Drupal\media_entity\MediaFormController",
 *       "delete" = "Drupal\media_entity\Form\MediaDeleteForm",
 *       "edit" = "Drupal\media_entity\MediaFormController"
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationController"
 *   },
 *   base_table = "media",
 *   data_table = "media_field_data",
 *   revision_table = "media_revision",
 *   revision_data_table = "media_field_revision",
 *   uri_callback = "media_entity_uri",
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
 *   bundle_keys = {
 *     "bundle" = "bundle"
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
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('mid')->value;
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::preCreate().
   */
  public static function preCreate(EntityStorageControllerInterface $storage_controller,  array &$values) {
    $values['created'] = REQUEST_TIME;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    // Before saving the node, set changed and revision times.
    $this->changed->value = REQUEST_TIME;
  }

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
  public function setName($title) {
    $this->set('name', $title);
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
  public static function baseFieldDefinitions($entity_type) {
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
      ->setDescription(t('The media language code.'));

    $fields['name'] = FieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of this media.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
      ))
      ->setPropertyConstraints('value', array('Length' => array('max' => 255)));

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Publisher ID'))
      ->setDescription(t('The user ID of the media publisher.'))
      ->setSettings(array(
        'target_type' => 'user',
        'default_value' => 0,
      ));

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the media is published.'));

    // @todo Convert to a "created" field in https://drupal.org/node/2145103.
    $fields['created'] = FieldDefinition::create('integer')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the media was created.'));

    // @todo Convert to a "changed" field in https://drupal.org/node/2145103.
    $fields['changed'] = FieldDefinition::create('integer')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the media was last edited.'))
      ->setPropertyConstraints('value', array('EntityChanged' => array()));

    $fields['type'] = FieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of this media.'))
      ->setRequired(TRUE)
      ->setPropertyConstraints('value', array('Length' => array('max' => 255)));

    $fields['resource_id'] = FieldDefinition::create('string')
      ->setLabel(t('Resource ID'))
      ->setDescription(t('The unique identifier of media resource that is associated with this media.'))
      ->setRequired(TRUE)
      ->setPropertyConstraints('value', array('Length' => array('max' => 255)));

    $fields['revision_timestamp'] = FieldDefinition::create('integer')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE);

    $fields['revision_uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Revision publisher ID'))
      ->setDescription(t('The user ID of the publisher of the current revision.'))
      ->setSettings(array('target_type' => 'user'))
      ->setQueryable(FALSE);

    $fields['log'] = FieldDefinition::create('string')
      ->setLabel(t('Log'))
      ->setDescription(t('The log entry explaining the changes in this revision.'));

    return $fields;
  }

}
