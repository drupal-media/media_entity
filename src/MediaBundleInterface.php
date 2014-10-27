<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaBundleInterface.
 */

namespace Drupal\media_entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a media bundle entity.
 */
interface MediaBundleInterface extends ConfigEntityInterface {

  /**
   * Returns the label.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The Media entity.
   *
   * @return string|bool
   *   Returns the label of the bundle that entity belongs to.
   */
  public static function getLabel(MediaInterface $media);

  /**
   * Returns the media bundle ID.
   *
   * @param int $id
   *   The Media bundle ID.
   *
   * @return bool
   *   Returns the media bundle ID.
   */
  public static function exists($id);

  /**
   * Returns the Media bundle description.
   *
   * @return string
   *   Returns the Media bundle description.
   */
  public function getDescription();

  /**
   * Returns the media type plugin.
   *
   * @return \Drupal\media_entity\MediaTypeInterface
   *   The type.
   */
  public function getType();

  /**
   * Returns the media type configuration.
   *
   * @return array
   *   The type configuration.
   */
  public function getTypeConfiguration();

  /**
   * Sets the media type configuration.
   *
   * @param array $configuration
   *   The type configuration.
   */
  public function setTypeConfiguration($configuration);
}
