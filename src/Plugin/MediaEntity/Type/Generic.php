<?php

/**
 * Contains \Drupal\media_entity\Plugin\MediaEntity\Type\Generic.
 */

namespace Drupal\media_entity\Plugin\MediaEntity\Type;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\media_entity\MediaTypeInterface;

/**
 * Provides generic media type.
 *
 * @MediaType(
 *   id = "generic",
 *   label = @Translation("Generic media"),
 *   description = @Translation("Generic media type.")
 * )
 */
class Generic extends PluginBase implements MediaTypeInterface {
  use StringTranslationTrait;

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getField($name) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function validate() { }

}
