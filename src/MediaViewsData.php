<?php

namespace Drupal\media_entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the media entity type.
 */
class MediaViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['media_field_data']['table']['wizard_id'] = 'media';
    $data['media_field_revision']['table']['wizard_id'] = 'media_revision';
    $data['media']['media_bulk_form'] = array(
      'title' => t('Media operations bulk form'),
      'help' => t('Add a form element that lets you run operations on multiple media entities.'),
      'field' => array(
        'id' => 'media_bulk_form',
      ),
    );

    return $data;
  }

}
