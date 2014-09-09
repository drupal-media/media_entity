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

  public function providedFields();

  public function getField($name);

  /**
   * Gets settings (sub) form for type plugin.
   *
   * @param MediaBundleInterface $bundle
   *   Media bundle.
   * @return mixed
   *   Form structure. Form elements should reflect configuration schema.
   */
  public function settingsForm(MediaBundleInterface $bundle);

  /**
   * Validates media.
   *
   * @param MediaInterface $media
   *   Media.
   *
   * @throws MediaTypeException
   *   Exception in case of failed validation.
   */
  public function validate(MediaInterface $media);

}
