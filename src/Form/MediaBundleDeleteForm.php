<?php

/**
 * @file
 * Contains \Drupal\media_entity\Form\MediaBundleDeleteForm.
 */

namespace Drupal\media_entity\Form;

use Drupal\Core\Entity\EntityDeleteForm;

/**
 * Provides a form for media bundle deletion.
 */
class MediaBundleDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->urlInfo('collection');
  }

}
