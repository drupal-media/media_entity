<?php

/**
 * @file
 * Contains \Drupal\media_entity\Controller\MediaController.
 */

namespace Drupal\media_entity\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\media_entity\MediaInterface;

/**
 * Returns responses for Media entity routes.
 */
class MediaController extends ControllerBase {

  /**
   * Displays a media.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The media we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(MediaInterface $media) {
    $build = $this->buildPage($media);

    foreach ($media->uriRelationships() as $rel) {
      $uri = $media->uri($rel);
      // Set the node path as the canonical URL to prevent duplicate content.
      $build['#attached']['drupal_add_html_head_link'][] = array(
        array(
          'rel' => $rel,
          'href' => $this->urlGenerator()->generateFromPath($uri['path'], $uri['options']),
        )
      , TRUE);

      if ($rel == 'canonical') {
        // Set the non-aliased canonical path as a default shortlink.
        $build['#attached']['drupal_add_html_head_link'][] = array(
          array(
            'rel' => 'shortlink',
            'href' => $this->urlGenerator()->generateFromPath($uri['path'], array_merge($uri['options'], array('alias' => TRUE))),
          )
        , TRUE);
      }
    }

    return $build;
  }

  /**
   * The _title_callback for the media.view route.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The current media.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(MediaInterface $media) {
    return String::checkPlain($this->entityManager()->getTranslationFromContext($media)->label());
  }

  /**
   * Builds a media page render array.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The media we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  protected function buildPage(MediaInterface $media) {
    return array('media' => $this->entityManager()->getViewBuilder('media')->view($media));
  }

}
