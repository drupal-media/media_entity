<?php

namespace Drupal\media_entity\Plugin\MediaEntity\Type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;

/**
 * Provides generic media type.
 *
 * @MediaType(
 *   id = "generic",
 *   label = @Translation("Generic media"),
 *   description = @Translation("Generic media type."),
 *   allowed_field_types = {"string"}
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
  public function getField(MediaInterface $media, $name) {
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
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['text'] = [
      '#type' => 'markup',
      '#markup' => $this->t("This type provider doesn't need configuration."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function createSourceFieldStorage() {
    return $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->create([
        'entity_type' => 'media',
        'field_name' => $this->getSourceFieldName(),
        // Strings are harmless, inoffensive puppies: a good choice for a
        // generic media type.
        'type' => 'string',
      ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function createSourceField(MediaBundleInterface $bundle) {
    /** @var \Drupal\field\FieldConfigInterface $field */
    return $this->entityTypeManager
      ->getStorage('field_config')
      ->create([
        'field_storage' => $this->getSourceFieldStorage(),
        'bundle' => $bundle->id(),
      ]);
  }

}
