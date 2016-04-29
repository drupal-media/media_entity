<?php

/**
 * Contains \Drupal\media_entity\Plugin\MediaEntity\Type\Generic.
 */

namespace Drupal\media_entity\Plugin\MediaEntity\Type;

use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides generic media type.
 *
 * @MediaType(
 *   id = "generic",
 *   label = @Translation("Generic media"),
 *   description = @Translation("Generic media type.")
 * )
 */
class Generic extends MediaTypeBase {

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface$media, $name) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    return $this->config->get('icon_base') . '/generic.png';
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['text'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This type provider doesn\'t need configuration.'),
    ];

    return $form;
  }

}
