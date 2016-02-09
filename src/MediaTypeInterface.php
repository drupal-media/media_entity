<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaTypeInterface.
 */

namespace Drupal\media_entity;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for media types.
 */
interface MediaTypeInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

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

  /**
   * Gets the default thumbnail image.
   *
   * @return string
   *   Uri of the default thumbnail image.
   */
  public function getDefaultThumbnail();

}
