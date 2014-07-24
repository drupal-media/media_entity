<?php
/**
 * @file
 * Theme functions for the media_entity module.
 */

/**
 * Prepares variables for list of available media bundles.
 *
 * Default template: media-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of content types.
 */
function template_preprocess_media_add_list(&$variables) {
  $variables['bundles'] = array();
  if (!empty($variables['content'])) {
    foreach ($variables['content'] as $bundle) {
      $link_text = '<span class="label">' . $bundle->label . '</span><div class="description">' . $bundle->description . '</div>';
      $variables['bundles'][$bundle->id] = array(
        'add_link' => l($link_text, 'media/add/' . $bundle->id, array('html' => TRUE)),
      );
    }
  }
}
