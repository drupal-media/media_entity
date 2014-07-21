<?php

/**
 * @file
 * Definition of Drupal\media_entity\MediaForm.
 */

namespace Drupal\media_entity;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;

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

    // Set up default values, if required.
    $this->settings = array(
      'options' => array('status'),
    );

    // If this is a new media, fill in the default values.
    if ($media->isNew()) {
      foreach (array('status') as $key) {
        // Multistep media forms might have filled in something already.
        if ($media->$key->isEmpty()) {
          $media->$key = (int) in_array($key, $this->settings['options']);
        }
      }
      $media->setPublisherId($this->currentUser()->id());
      $media->setCreatedTime(REQUEST_TIME);
    }
    else {
      $media->date = format_date($media->getCreatedTime(), 'custom', 'Y-m-d H:i:s O');
      // Remove the log message from the original media entity.
      $media->revision_log = NULL;
    }
    // Always use the default revision setting.
    $media->setNewRevision(in_array('revision', $this->settings['options']));
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, array &$form_state) {
    $account = \Drupal::currentUser();

    $media = $this->entity;
    $media_bundle = entity_load('media_bundle', $media->getBundle());

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @bundle</em> @title', array('@bundle' => $media_bundle->label(), '@title' => $media->label()));
    }

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = array(
      '#type' => 'hidden',
      '#default_value' => $media->getChangedTime(),
    );

    $form['advanced'] = array(
      '#type' => 'vertical_tabs',
      '#attributes' => array('class' => array('entity-meta')),
      '#weight' => 99,
    );

    // Add a log field if the "Create new revision" option is checked, or if
    // the current user has the ability to check that option.
    $form['revision_information'] = array(
      '#type' => 'details',
      '#group' => 'advanced',
      '#title' => t('Revision information'),
      // Open by default when "Create new revision" is checked.
      '#open' => $media->isNewRevision(),
      '#attributes' => array(
        'class' => array('media-form-revision-information'),
      ),
      '#weight' => 20,
      '#access' => $media->isNewRevision() || $account->hasPermission('administer media'),
      '#optional' => TRUE,
    );

    $form['revision'] = array(
      '#type' => 'checkbox',
      '#title' => t('Create new revision'),
      '#default_value' => $media->isNewRevision(),
      '#access' => $account->hasPermission('administer media'),
      '#group' => 'revision_information',
    );

    $form['revision_log'] = array(
      '#type' => 'textarea',
      '#title' => t('Revision log message'),
      '#rows' => 4,
      '#default_value' =>$media->revision_log->value,
      '#description' => t('Briefly describe the changes you have made.'),
      '#group' => 'revision_information',
      '#access' => $account->hasPermission('administer media'),
      '#states' => array(
        'visible' => array(
          ':input[name="revision"]' => array('checked' => TRUE),
        ),
      ),
    );

    // Media publisher information for administrators.
    $form['publisher'] = array(
      '#type' => 'details',
      '#access' => $account->hasPermission('administer media'),
      '#title' => t('Authoring information'),
      '#collapsed' => TRUE,
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('media-form-publisher'),
      ),
      '#weight' => 90,
    );

    $form['uid'] = array(
      '#type' => 'textfield',
      '#title' => t('Published by'),
      '#maxlength' => 60,
      '#autocomplete_route_name' => 'user.autocomplete',
      '#default_value' => $media->getPublisher() ? $media->getPublisher()->getUsername() : '',
      '#weight' => -1,
      '#description' => t('Leave blank for anonymous.'),
      '#group' => 'publisher',
    );
    $form['created'] = array(
      '#type' => 'textfield',
      '#title' => t('Authored on'),
      '#maxlength' => 25,
      '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($media->date) ? date_format(date_create($media->date), 'Y-m-d H:i:s O') : format_date($media->getCreatedTime(), 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($media->date) ? date_format(date_create($media->date), 'O') : format_date($media->getCreatedTime(), 'custom', 'O'))),
      '#default_value' => !empty($media->date) ? $media->date : '',
      '#group' => 'publisher',
    );

    return parent::form($form, $form_state, $media);
  }

  /**
   * Updates the media by processing the submitted values.
   *
   * Overrides Drupal\Core\Entity\EntityForm::submit().
   */
  public function submit(array $form, array &$form_state) {
    // Build the media object from the submitted values.
    $media = parent::submit($form, $form_state);

    // Save as a new revision if requested to do so.
    if (!empty($form_state['values']['revision'])) {
      $media->setNewRevision();
      // If a new revision is created, save the current user as revision author.
      $media->set('revision_timestamp', REQUEST_TIME);
      $media->set('revision_uid', $this->currentUser()->id());
    }
    else {
      $media->setNewRevision(FALSE);
    }

    return $media;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    $entity = parent::buildEntity($form, $form_state);
    // A user might assign the media publisher by entering a user name in the node
    // form, which we then need to translate to a user ID.
    if (!empty($form_state['values']['uid']) && $account = user_load_by_name($form_state['values']['uid'])) {
      $entity->setPublisherId($account->id());
    }
    else {
      $entity->setPublisherId(0);
    }

    if (!empty($form_state['values']['created']) && $form_state['values']['created'] instanceOf DrupalDateTime) {
      $entity->setCreatedTime($form_state['values']['created']->getTimestamp());
    }
    else {
      $entity->setCreatedTime(REQUEST_TIME);
    }
    return $entity;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, array &$form_state) {
    $media = $this->entity;
    $media->save();

    if ($media->id()) {
      $form_state['values']['mid'] = $media->id();
      $form_state['mid'] = $media->id();
      if ($media->access('view')) {
        $form_state['redirect_route'] = array(
          'route_name' => 'media.view',
          'route_parameters' => array(
            'media' => $media->id(),
          ),
        );
      }
      else {
        $form_state['redirect_route']['route_name'] = '<front>';
      }
    }
    else {
      // In the unlikely case something went wrong on save, the media will be
      // rebuilt and media form redisplayed the same way as in preview.
      drupal_set_message(t('The media could not be saved.'), 'error');
      $form_state['rebuild'] = TRUE;
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::delete().
   */
  public function delete(array $form, array &$form_state) {
    $destination = array();
    $query = \Drupal::request()->query;
    if ($query->has('destination')) {
      $destination = drupal_get_destination();
      $query->remove('destination');
    }
    $form_state['redirect_route'] = array(
      'route_name' => 'media.delete_confirm',
      'route_parameters' => array(
        'media' => $this->entity->id(),
      ),
      'options' => array(
        'query' => $destination,
      ),
    );
  }

}
