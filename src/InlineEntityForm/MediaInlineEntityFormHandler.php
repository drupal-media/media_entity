<?php

/**
 * Contains \Drupal\media_entity\InlineEntityForm\MediaInlineEntityFormHandler.
 */

namespace Drupal\media_entity\InlineEntityForm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\inline_entity_form\InlineEntityForm\EntityInlineEntityFormHandler;

/**
 * Media inline form handler.
 */
class MediaInlineEntityFormHandler extends EntityInlineEntityFormHandler {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function tableFields($bundles) {
    $fields = parent::tableFields($bundles);

    unset($fields['name']);

    $fields['thumbnail'] = [
      'type' => 'field',
      'label' => $this->t('Thumbnail'),
      'weight' => 1,
      'display_options' => [
        'type' => 'image',
        'settings' => [
          'image_style' => 'thumbnail',
        ],
      ],
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function entityFormSubmit(&$entity_form, FormStateInterface $form_state) {
    parent::entityFormSubmit($entity_form, $form_state);

    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $entity_form['#entity'];

    // Make sure media thumbnail is set correctly.
    $entity->automaticallySetThumbnail();

    if ($entity_form['#save_entity']) {
      $entity->save();
    }
  }
}
