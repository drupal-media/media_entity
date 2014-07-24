<?php

/**
 * @file
 * Provide views data for media_entity.module.
 */

/**
 * Implements hook_views_data().
 */
function media_entity_views_data() {

  // Define the base group of this table. Fields that don't have a group defined
  // will go into this field by default.
  $data['media']['table']['group'] = t('Media');

  // Advertise this table as a possible base table.
  $data['media']['table']['base'] = array(
    'field' => 'mid',
    'title' => t('Media'),
    'weight' => -10,
    'access query tag' => 'media_access',
    'defaults' => array(
      'field' => 'name',
    ),
  );
  $data['media']['table']['entity type'] = 'media';

  $data['media_field_data']['table']['group'] = t('Media');
  $data['media_field_data']['table']['entity type'] = 'media';
  $data['media_field_data']['table']['join']['media'] = array(
    'type' => 'INNER',
    'left_field' => 'mid',
    'field' => 'mid',
  );

  $data['media']['mid'] = array(
    'title' => t('Mid'),
    'help' => t('The media ID.'),
    'field' => array(
      // TODO: Provide something smarter; with support for linking etc.
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  $data['media_field_data']['name'] = array(
    'title' => t('Name'),
    'help' => t('The media title.'),
    'field' => array(
      'id' => 'media_name',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'filter' => array(
      'id' => 'string',
    ),
    'argument' => array(
      'id' => 'string',
    ),
  );

  $data['media_field_data']['created'] = array(
    'title' => t('Create date'),
    'help' => t('The date the media was created/uploaded.'),
    'field' => array(
      'id' => 'date',
    ),
    'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
  );

  $data['media_field_data']['changed'] = array(
    'title' => t('Updated date'),
    'help' => t('The date the media was last updated.'),
    'field' => array(
      'id' => 'date',
    ),
    'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
  );

  $data['media_field_data']['bundle'] = array(
    'title' => t('Bundle'),
    'help' => t('The media bundle (for example, "images", "recordings", "trailers", etc).'),
    'field' => array(
      // TODO: This will output machine name. We need to display label with optional
      // support for machine name.
      'id' => 'standard',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'filter' => array(
      'id' => 'bundle',
    ),
    'argument' => array(
      // TODO: This will worked based on machine name. We want to use human-readable
      // label for that.
      'id' => 'string',
    ),
  );

  $data['media_field_data']['status'] = array(
    'title' => t('Published status'),
    'help' => t('Whether or not the media is published.'),
    'field' => array(
      'id' => 'boolean',
      'output formats' => array(
        'published-notpublished' => array(t('Published'), t('Not published')),
      ),
    ),
    'filter' => array(
      'id' => 'boolean',
      'label' => t('Published status'),
      'type' => 'yes-no',
      // Use status = 1 instead of status <> 0 in WHERE statement.
      'use_equal' => TRUE,
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  // TODO: Add "published or admin".

  if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
    $data['media']['translation_link'] = array(
      'title' => t('Translation link'),
      'help' => t('Provide a link to the translations overview for media.'),
      'field' => array(
        'id' => 'content_translation_link',
      ),
    );
  }

  $data['media']['bulk_form'] = array(
    'title' => t('Media operations bulk form'),
    'help' => t('Add a form element that lets you run operations on multiple media items.'),
    'field' => array(
      // TODO: Review and figure out what to customize in an optional custom.
      // plugin that extends this one.
      'id' => 'action_bulk_form',
    ),
  );

  $data['media']['view_media'] = array(
    'field' => array(
      'title' => t('Link to media'),
      'help' => t('Provide a simple link to the media.'),
      'id' => 'media_link',
    ),
  );

  $data['media']['edit_media'] = array(
    'field' => array(
      'title' => t('Link to edit media'),
      'help' => t('Provide a simple link to edit the media.'),
      'id' => 'media_link_edit',
    ),
  );

  $data['media']['delete_media'] = array(
    'field' => array(
      'title' => t('Link to delete media'),
      'help' => t('Provide a simple link to delete the media.'),
      'id' => 'media_link_delete',
    ),
  );

  // Bogus fields for aliasing purposes.
  $data['media_field_data']['created_fulldate'] = array(
    'title' => t('Created date'),
    'help' => t('Date in the form of CCYYMMDD.'),
    'argument' => array(
      'field' => 'created',
      'id' => 'date_fulldate',
    ),
  );

  $data['media_field_data']['created_year_month'] = array(
    'title' => t('Created year + month'),
    'help' => t('Date in the form of YYYYMM.'),
    'argument' => array(
      'field' => 'created',
      'id' => 'date_year_month',
    ),
  );

  $data['media_field_data']['created_year'] = array(
    'title' => t('Created year'),
    'help' => t('Date in the form of YYYY.'),
    'argument' => array(
      'field' => 'created',
      'id' => 'date_year',
    ),
  );

  $data['media_field_data']['created_month'] = array(
    'title' => t('Created month'),
    'help' => t('Date in the form of MM (01 - 12).'),
    'argument' => array(
      'field' => 'created',
      'id' => 'date_month',
    ),
  );

  $data['media_field_data']['created_day'] = array(
    'title' => t('Created day'),
    'help' => t('Date in the form of DD (01 - 31).'),
    'argument' => array(
      'field' => 'created',
      'id' => 'date_day',
    ),
  );

  $data['media_field_data']['created_week'] = array(
    'title' => t('Created week'),
    'help' => t('Date in the form of WW (01 - 53).'),
    'argument' => array(
      'field' => 'created',
      'id' => 'date_week',
    ),
  );

  $data['media_field_data']['changed_fulldate'] = array(
    'title' => t('Updated date'),
    'help' => t('Date in the form of CCYYMMDD.'),
    'argument' => array(
      'field' => 'changed',
      'id' => 'date_fulldate',
    ),
  );

  $data['media_field_data']['changed_year_month'] = array(
    'title' => t('Updated year + month'),
    'help' => t('Date in the form of YYYYMM.'),
    'argument' => array(
      'field' => 'changed',
      'id' => 'date_year_month',
    ),
  );

  $data['media_field_data']['changed_year'] = array(
    'title' => t('Updated year'),
    'help' => t('Date in the form of YYYY.'),
    'argument' => array(
      'field' => 'changed',
      'id' => 'date_year',
    ),
  );

  $data['media_field_data']['changed_month'] = array(
    'title' => t('Updated month'),
    'help' => t('Date in the form of MM (01 - 12).'),
    'argument' => array(
      'field' => 'changed',
      'id' => 'date_month',
    ),
  );

  $data['media_field_data']['changed_day'] = array(
    'title' => t('Updated day'),
    'help' => t('Date in the form of DD (01 - 31).'),
    'argument' => array(
      'field' => 'changed',
      'id' => 'date_day',
    ),
  );

  $data['media_field_data']['changed_week'] = array(
    'title' => t('Updated week'),
    'help' => t('Date in the form of WW (01 - 53).'),
    'argument' => array(
      'field' => 'changed',
      'id' => 'date_week',
    ),
  );

  $data['media_field_data']['uid'] = array(
    'title' => t('Publisher uid'),
    'help' => t('The user publishing the content. If you need more fields than the uid add the media: publisher relationship'),
    'relationship' => array(
      'title' => t('Media published'),
      'help' => t('Relate media to the user who published it.'),
      'id' => 'standard',
      'base' => 'users',
      'field' => 'uid',
      'label' => t('publisher'),
    ),
    'filter' => array(
      'id' => 'user_name',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'field' => array(
      'id' => 'user',
    ),
  );

  // TODO: Add "user has a revision" field (similar to node).

  $data['media_revision']['table']['entity type'] = 'media';
  // Define the base group of this table. Fields that don't have a group defined
  // will go into this field by default.
  $data['media_revision']['table']['group']  = t('Media revision');

  // Advertise this table as a possible base table.
  $data['media_revision']['table']['base'] = array(
    'field' => 'vid',
    'title' => t('Media revision'),
    'help' => t('Media revision is a history of changes to media.'),
    'defaults' => array(
      'field' => 'name',
    ),
  );

  // For other base tables, explain how we join.
  $data['media_revision']['table']['join'] = array(
    'media' => array(
      'left_field' => 'vid',
      'field' => 'vid',
    ),
  );

  $data['media_revision']['mid'] = array(
    'title' => t('Mid'),
    'help' => t('The revision MID of the media revision.'),
    'field' => array(
      'id' => 'standard',
    ),
    'argument' => array(
      // TODO: Provide something smarter (link to media, ...).
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'relationship' => array(
      'id' => 'standard',
      'base' => 'media',
      'base field' => 'mid',
      'title' => t('Media'),
      'label' => t('Get the actual media from a media revision.'),
    ),
  );

  $data['media_revision']['vid'] = array(
    'title' => t('Vid'),
    'help' => t('The revision ID of the media revision.'),
    'field' => array(
      'id' => 'standard',
    ),
    'argument' => array(
      // TODO: Provide something smarter (like node_vid).
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'relationship' => array(
      'id' => 'standard',
      'base' => 'media',
      'base field' => 'vid',
      'title' => t('Media'),
      'label' => t('Get the actual media from a media revision.'),
    ),
  );

  if (\Drupal::moduleHandler()->moduleExists('language')) {
    $data['media_revision']['langcode'] = array(
      'title' => t('Language'),
      'help' => t('The language the media is in.'),
      'field' => array(
        'id' => 'language',
      ),
      'filter' => array(
        'id' => 'language',
      ),
      'argument' => array(
        'id' => 'language',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
    );
  }

  $data['media_revision']['revision_log'] = array(
    'title' => t('Revision log message'),
    'help' => t('The log message entered when the revision was created.'),
    'field' => array(
      'id' => 'xss',
    ),
    'filter' => array(
      'id' => 'string',
    ),
  );

  $data['media_revision']['revision_uid'] = array(
    'title' => t('User'),
    'help' => t('Relate a media revision to the user who created the revision.'),
    'relationship' => array(
      'id' => 'standard',
      'base' => 'users',
      'base field' => 'uid',
      'label' => t('revision user'),
    ),
  );

  $data['media_field_revision']['table']['entity type'] = 'media';
  // Define the base group of this table. Fields that don't have a group defined
  // will go into this field by default.
  $data['media_field_revision']['table']['group']  = t('Media revision');

  // For other base tables, explain how we join.
  $data['media_field_revision']['table']['join'] = array(
    'media' => array(
      'left_field' => 'vid',
      'field' => 'vid',
    ),
  );

  $data['media_field_revision']['status'] = array(
    'title' => t('Published'),
    'help' => t('Whether or not the media revision is published.'),
    'field' => array(
      'id' => 'boolean',
      'output formats' => array(
        'published-notpublished' => array(t('Published'), t('Not published')),
      ),
    ),
    'filter' => array(
      'id' => 'boolean',
      'label' => t('Published'),
      'type' => 'yes-no',
      // Use status = 1 instead of status <> 0 in WHERE statement.
      'use_equal' => TRUE,
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  $data['media_field_revision']['name'] = array(
    'title' => t('Name'),
    'help' => t('The media revision name.'),
    'field' => array(
      // TODO: Use something smarter (like node_revision).
      'id' => 'standard',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'filter' => array(
      'id' => 'string',
    ),
    'argument' => array(
      'id' => 'string',
    ),
  );

  $data['media_field_revision']['changed'] = array(
    'title' => t('Updated date'),
    'help' => t('The date the media was last updated.'),
    'field' => array(
      'id' => 'date',
    ),
    'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
  );

  // TODO: Add "link to media revision", "link to revert revision", "delete revision link".

  return $data;
}
