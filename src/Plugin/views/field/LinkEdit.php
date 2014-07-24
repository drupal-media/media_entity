<?php

/**
 * @file
 * Contains views_handler_field_media_link_edit.
 */

namespace Drupal\media_entity\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link to edit the media item.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("media_link_edit")
 */
class LinkEdit extends Link {

  /**
   * Prepares the link to the media.
   *
   * @param \Drupal\Core\Entity\EntityInterface $media
   *   The media entity this field belongs to.
   * @param ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($media, ResultRow $values) {
    // Ensure user has access to edit this media.
    if (!$media->access('update')) {
      return;
    }

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = "media/" . $media->id() . "/edit";
    $this->options['alter']['query'] = drupal_get_destination();

    $text = !empty($this->options['text']) ? $this->options['text'] : t('Edit');
    return $text;
  }

}
