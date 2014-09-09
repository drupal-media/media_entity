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

  public function settingsForm();

  public function validate();

}
