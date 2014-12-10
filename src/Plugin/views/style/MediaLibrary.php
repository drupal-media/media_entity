<?php

/**
 * @file
 * Definition of Drupal\media_entity\Plugin\views\style\MediaLibrary.
 */

namespace Drupal\media_entity\Plugin\views\style;

use Drupal\views\Plugin\views\style\DefaultStyle;

/**
 * Displays media library.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "media_entity_library",
 *   title = @Translation("Media library"),
 *   help = @Translation("Displays media items in a library."),
 *   theme = "views_view_unformatted",
 *   display_types = {"normal"}
 * )
 */
class MediaLibrary extends DefaultStyle {

  /**
   * {@inheritdoc}
   */
  public function renderGroupingSets($sets, $level = 0) {
    $output = parent::renderGroupingSets($sets, $level);

    // Add library that makes output look as a library.
    $output['#attached']['library'][] = 'media_entity/library';

    return $output;
  }

}
