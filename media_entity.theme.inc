<?php
/**
 * @file
 * Theme functions for the media_entity module.
 */
use Drupal\Component\Utility\String;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;

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
      $link_url = Url::fromRoute('media.add', array('media_bundle' => $bundle->id))->setOption('html', TRUE);
      $variables['bundles'][$bundle->id] = array(
        'add_link' => \Drupal::l($link_text, $link_url),
      );
    }
  }
}

/**
 * Prepares variables for media templates.
 *
 * Default template: media.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - media: An individual media for display.
 */
function template_preprocess_media(&$variables) {
  /** @var \Drupal\media_entity\MediaInterface $media */
  $media = $variables['elements']['#media'];

  $variables['name'] = String::checkPlain($media->getName());

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  $variables['attributes']['class'][] = 'media';
  $variables['attributes']['class'][] = drupal_html_class('media-' . $media->getBundle());
  if (!$media->isPublished()) {
    $variables['attributes']['class'][] = 'unpublished';
  }
  if ($variables['elements']['#view_mode']) {
    $variables['attributes']['class'][] = drupal_html_class('view-mode-' . $variables['elements']['#view_mode']);
  }
}
