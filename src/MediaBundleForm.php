<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaBundleForm.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\Component\Utility\String;

/**
 * Form controller for node type forms.
 */
class MediaBundleForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $bundle = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = String::checkPlain($this->t('Add media bundle'));
    }
    elseif ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit %label media bundle', array('%label' => $bundle->label()));
    }

    $form['label'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $bundle->label(),
      '#description' => t('The human-readable name of this media bundle.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    // @todo: '#disabled' not always FALSE.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $bundle->id(),
      '#maxlength' => 32,
      '#disabled' => FALSE,
      '#machine_name' => array(
        'exists' => array('\Drupal\media_entity\Entity\MediaBundle', 'exists'),
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for this media bundle.'),
    );

    $form['type'] = array(
      '#title' => t('Type'),
      '#type' => 'textfield',
      '#default_value' => $bundle->type(),
      '#description' => t('The type of this media bundle e.g. image, video, audio.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $bundle->getDescription(),
      '#description' => t('Describe this media bundle. The text will be displayed on the <em>Add new media</em> page.'),
    );
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save media bundle');
    $actions['delete']['#value'] = t('Delete media bundle');
    $actions['delete']['#access'] = $this->entity->access('delete');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $bundle = $this->entity;
    $bundle->id = trim($bundle->id());

    $status = $bundle->save();

    $t_args = array('%name' => $bundle->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The media bundle %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The media bundle %name has been added.', $t_args));
      watchdog('node', 'Added bundle %name.', $t_args, WATCHDOG_NOTICE);
    }

    $form_state['redirect_route']['route_name'] = 'media.overview_bundles';
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $form_state['redirect_route'] = array(
      'route_name' => 'media.bundle_delete_confirm',
      'route_parameters' => array(
        'media_bundle' => $this->entity->id(),
      ),
    );
  }

}
