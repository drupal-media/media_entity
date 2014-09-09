<?php

/**
 * @file
 * Contains \Drupal\media_entity\Entity\MediaBundle.
 */

namespace Drupal\media_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Plugin\DefaultSinglePluginBag;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Defines the Media bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "media_bundle",
 *   label = @Translation("Media bundle"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\media_entity\MediaBundleForm",
 *       "edit" = "Drupal\media_entity\MediaBundleForm",
 *       "delete" = "Drupal\media_entity\Form\MediaBundleDeleteForm"
 *     },
 *     "list_builder" = "Drupal\media_entity\MediaBundleListBuilder",
 *   },
 *   admin_permission = "administer media",
 *   config_prefix = "bundle",
 *   bundle_of = "media",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "media.bundle_edit",
 *     "delete-form" = "media.bundle_delete_confirm"
 *   }
 * )
 */
class MediaBundle extends ConfigEntityBundleBase implements MediaBundleInterface, EntityWithPluginBagsInterface {

  /**
   * The machine name of this media bundle.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the media bundle.
   *
   * @var string
   */
  public $label;

  /**
   * A brief description of this media bundle.
   *
   * @var string
   */
  public $description;

  /**
   * The type plugin id.
   *
   * @var string
   */
  public $type = 'generic';

  /**
   * The type plugin configuration.
   *
   * @var array
   */
  public $type_configuraton = array();

  /**
   * Type plugin bag.
   *
   * @var \Drupal\Core\Plugin\DefaultSinglePluginBag
   */
  protected $typeBag;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginBags() {
    return array(
      'type' => $this->typeBag(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel(MediaInterface $media) {
    $bundle = entity_load('media_bundle', $media->bundle());
    return $bundle ? $bundle->label() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function exists($id) {
    return (bool) static::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->typeBag()->get($this->type);
  }

  /**
   * Returns type plugin bag.
   *
   * @return \Drupal\Core\Plugin\DefaultSinglePluginBag
   *   The tag plugin bag.
   */
  protected function typeBag() {
    if (!$this->typeBag) {
      $this->typeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.media_entity.type'), $this->type, $this->type_configuraton);
    }
    return $this->typeBag;
  }

}
