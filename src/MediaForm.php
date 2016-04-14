<?php

/**
 * @file
 * Contains Drupal\media_entity\MediaForm.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the media edit forms.
 */
class MediaForm extends ContentEntityForm {

  /**
   * Default settings for this media bundle.
   *
   * @var array
   */
  protected $settings;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\media_entity\Entity\Media
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    $media = $this->entity;

    // If this is a new media, fill in the default values.
    if ($media->isNew()) {
      
      $media->setPublisherId($this->currentUser()->id());
      $media->setCreatedTime(REQUEST_TIME);
    }
    else {
      // Remove the log message from the original media entity.
      $media->revision_log = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $media = $this->entity;
    $account = $this->currentUser();
    $form = parent::form($form, $form_state);

    $form['advanced'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    );

    // Add a log field if the "Create new revision" option is checked, or if the
    // current user has the ability to check that option.
    $form['revision_information'] = array(
      '#type' => 'details',
      '#title' => $this->t('Revision information'),
      // Open by default when "Create new revision" is checked.
      '#open' => $media->isNewRevision(),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('media-form-revision-information'),
      ),
      '#weight' => 20,
      '#access' => $media->isNewRevision() || $account->hasPermission('administer media'),
    );

    $form['revision_information']['revision'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Create new revision'),
      '#default_value' => $media->isNewRevision(),
      '#access' => $account->hasPermission('administer media'),
    );

    // Check the revision log checkbox when the log textarea is filled in.
    // This must not happen if "Create new revision" is enabled by default,
    // since the state would auto-disable the checkbox otherwise.
    if (!$media->isNewRevision()) {
      $form['revision_information']['revision']['#states'] = array(
        'checked' => array(
          'textarea[name="revision_log"]' => array('empty' => FALSE),
        ),
      );
    }

    $form['revision_information']['revision_log'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Revision log message'),
      '#rows' => 4,
      '#default_value' => '',
      '#description' => $this->t('Briefly describe the changes you have made.'),
    );

    // Node author information for administrators.
    $form['author'] = array(
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('node-form-author'),
      ),
      '#attached' => array(
        'library' => array('node/drupal.node'),
      ),
      '#weight' => 90,
      '#optional' => TRUE,
    );

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['#attached']['library'][] = 'node/form';

    $form['#entity_builders']['update_status'] = [$this, 'updateStatus'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $media = $this->entity;

    // Add a "Publish" button.
    $element['publish'] = $element['submit'];
    // If the "Publish" button is clicked, we want to update the status to "published".
    $element['publish']['#published_status'] = TRUE;
    $element['publish']['#dropbutton'] = 'save';
    if ($media->isNew()) {
      $element['publish']['#value'] = t('Save and publish');
    }
    else {
      $element['publish']['#value'] = $media->isPublished() ? t('Save and keep published') : t('Save and publish');
    }
    $element['publish']['#weight'] = 0;

    // Add a "Unpublish" button.
    $element['unpublish'] = $element['submit'];
    // If the "Unpublish" button is clicked, we want to update the status to "unpublished".
    $element['unpublish']['#published_status'] = FALSE;
    $element['unpublish']['#dropbutton'] = 'save';
    if ($media->isNew()) {
      $element['unpublish']['#value'] = t('Save as unpublished');
    }
    else {
      $element['unpublish']['#value'] = !$media->isPublished() ? t('Save and keep unpublished') : t('Save and unpublish');
    }
    $element['unpublish']['#weight'] = 10;

    // If already published, the 'publish' button is primary.
    if ($media->isPublished()) {
      unset($element['unpublish']['#button_type']);
    }
    // Otherwise, the 'unpublish' button is primary and should come first.
    else {
      unset($element['publish']['#button_type']);
      $element['unpublish']['#weight'] = -10;
    }

    // Remove the "Save" button.
    $element['submit']['#access'] = FALSE;

    $element['delete']['#access'] = $media->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

  /**
   * Entity builder updating the media status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\media_entity\MediaInterface $media
   *   The media updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\media\MediaForm::form()
   */
  function updateStatus($entity_type_id, MediaInterface $media, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $media->setPublished($element['#published_status']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the media object from the submitted values.
    parent::submitForm($form, $form_state);
    $media = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision') && $form_state->getValue('revision') != FALSE) {
      $media->setNewRevision();
      // If a new revision is created, save the current user as revision author.
      $media->set('revision_timestamp', REQUEST_TIME);
      $media->set('revision_uid', $this->currentUser()->id());
    }
    else {
      $media->setNewRevision(FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $media = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision')) {
      $media->setNewRevision();
    }

    $media->save();

    if ($media->id()) {
      $form_state->setValue('mid', $media->id());
      if ($media->access('view')) {
        $form_state->setRedirect('entity.media.canonical', ['media' => $media->id()]);
      }
      else {
        $form_state->setRedirect('<front>');
      }
    }
    else {
      // In the unlikely case something went wrong on save, the media will be
      // rebuilt and media form redisplayed the same way as in preview.
      drupal_set_message(t('The media could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }

}
