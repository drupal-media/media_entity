<?php

/**
 * @file
 * Contains Drupal\media_entity\Plugin\views\field\LinkDelete.
 */

namespace Drupal\media_entity\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link to delete the media item.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("media_link_delete")
 */
class LinkDelete extends Link {

  /**
   * Prepares the link to delete the media item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $media
   *   The media entity this field belongs to.
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($media, ResultRow $values) {
    // Ensure user has access to delete this media item.
    if (!$media->access('delete')) {
      return;
    }

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = "media/" . $media->id() . "/delete";
    $this->options['alter']['query'] = drupal_get_destination();

    $text = !empty($this->options['text']) ? $this->options['text'] : t('Delete');
    return $text;
  }

}
