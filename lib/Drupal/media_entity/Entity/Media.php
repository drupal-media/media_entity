<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\Media.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Defines the media entity class.
 *
 * @EntityType(
 *   id = "media",
 *   label = @Translation("Media"),
 *   bundle_label = @Translation("Media bundle"),
 *   module = "media_entity",
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\FieldableDatabaseStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\media_entity\MediaAccessController",
 *     "form" = {
 *       "default" = "Drupal\media_entity\MediaFormController",
 *       "delete" = "Drupal\Core\Entity\ContentEntityConfirmFormBase",
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
 *   route_base_path = "admin/structure/media/manage/{bundle}",
 *   permission_granularity = "entity_type",
 *   admin_permission = "administer media",
 *   links = {
 *     "canonical" = "/media/{media}",
 *     "edit-form" = "/media/{media}/edit",
 *     "version-history" = "/media/{media}/revisions"
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
    $properties['mid'] = array(
      'label' => t('Media ID'),
      'description' => t('The media ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The media UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['vid'] = array(
      'label' => t('Revision ID'),
      'description' => t('The media revision ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['bundle'] = array(
      'label' => t('Bundle'),
      'description' => t('The media bundle.'),
      'type' => 'string_field',
      'read-only' => TRUE,
    );
    $properties['langcode'] = array(
      'label' => t('Language code'),
      'description' => t('The media language code.'),
      'type' => 'language_field',
    );
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => t('The name of this node.'),
      'type' => 'string_field',
      'required' => TRUE,
      'settings' => array(
        'default_value' => '',
      ),
      'property_constraints' => array(
        'value' => array('Length' => array('max' => 255)),
      ),
    );
    $properties['uid'] = array(
      'label' => t('Publisher ID'),
      'description' => t('The user ID of the media publisher.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['status'] = array(
      'label' => t('Publishing status'),
      'description' => t('A boolean indicating whether the media is published.'),
      'type' => 'boolean_field',
    );
    $properties['created'] = array(
      'label' => t('Created'),
      'description' => t('The time that the media was created.'),
      'type' => 'integer_field',
    );
    $properties['changed'] = array(
      'label' => t('Changed'),
      'description' => t('The time that the media was last edited.'),
      'type' => 'integer_field',
      'property_constraints' => array(
        'value' => array('EntityChanged' => array()),
      ),
    );
    $properties['type'] = array(
      'label' => t('Type'),
      'description' => t('The type of this media.'),
      'required' => TRUE,
      'type' => 'string_field',
      'property_constraints' => array(
        'value' => array('Length' => array('max' => 255)),
      ),
    );
    $properties['resource_id'] = array(
      'label' => t('Resource ID'),
      'description' => t('The unique identifier of media resource that is associated with this media.'),
      'required' => TRUE,
      'type' => 'string_field',
      'property_constraints' => array(
        'value' => array('Length' => array('max' => 255)),
      ),
    );
    $properties['revision_timestamp'] = array(
      'label' => t('Revision timestamp'),
      'description' => t('The time that the current revision was created.'),
      'type' => 'integer_field',
      'queryable' => FALSE,
    );
    $properties['revision_uid'] = array(
      'label' => t('Revision publisher ID'),
      'description' => t('The user ID of the publisher of the current revision.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'user'),
      'queryable' => FALSE,
    );
    $properties['log'] = array(
      'label' => t('Log'),
      'description' => t('The log entry explaining the changes in this version.'),
      'type' => 'string_field',
    );
    return $properties;
  }

}
