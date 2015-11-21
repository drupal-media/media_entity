<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaViewsData.
 */

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

    return $data;
  }

}
