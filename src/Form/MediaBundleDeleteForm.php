<?php

/**
 * @file
 * Contains \Drupal\media_entity\Form\MediaBundleDeleteForm.
 */

namespace Drupal\media_entity\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for media bundle deletion.
 */
class MediaBundleDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the media bundle %bundle?', array('%bundle' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.media_bundle.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // @todo Check if there are media in the bundle.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form,FormStateInterface $form_state) {
    $this->entity->delete();
    $t_args = array('%name' => $this->entity->label());
    drupal_set_message(t('The media bundle %name has been deleted.', $t_args));
    $this->logger('media')->notice('Deleted media bundle %name.', $t_args);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
