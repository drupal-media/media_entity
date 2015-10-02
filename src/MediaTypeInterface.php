<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaTypeInterface.
 */

namespace Drupal\media_entity;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for media types.
 */
interface MediaTypeInterface extends PluginInspectionInterface {

  /**
   * Returns the display label.
   *
   * @return string
   *   The display label.
   */
  public function label();

  /**
   * Gets list of fields provided by this plugin.
   *
   * @return array
   *   Associative array with field names as keys and descriptions as values.
   */
  public function providedFields();

  /**
   * Get's a media-related field/value.
   *
   * @param MediaInterface $media
   *   Media object.
   * @param $name
   *   Name of field to fetch.
   *
   * @return mixed
   *   Field value or FALSE if data unavailable.
   */
  public function getField(MediaInterface $media, $name);

  /**
   * Gets settings (sub) form for type plugin.
   *
   * @param MediaBundleInterface $bundle
   *   Media bundle.
   *
   * @return mixed
   *   Form structure. Form elements should reflect configuration schema.
   */
  public function settingsForm(MediaBundleInterface $bundle);

  /**
   * Attaches type-specific constraints to media.
   *
   * @param MediaInterface $media
   *   Media entity.
   */
  public function attachConstraints(MediaInterface $media);

  /**
   * Gets thumbnail image.
   *
   * Media type plugin is responsible for returning URI of the generic thumbnail
   * if no other is available. This functions should always return a valid URI.
   *
   * @param MediaInterface $media
   *   Media.
   *
   * @return string
   *   URI of the thumbnail.
   */
  public function thumbnail(MediaInterface $media);

}
