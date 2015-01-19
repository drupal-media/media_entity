<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaBundleForm.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Form controller for node type forms.
 */
class MediaBundleForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
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

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $bundle->getDescription(),
      '#description' => t('Describe this media bundle. The text will be displayed on the <em>Add new media</em> page.'),
    );

    $plugins = \Drupal::service('plugin.manager.media_entity.type')->getDefinitions();
    $options = array();
    foreach ($plugins as $plugin => $definition) {
      $options[$plugin] = $definition['label'];
    }

    $form['type'] = array(
      '#type' => 'select',
      '#title' => t('Type provider'),
      '#default_value' => $bundle->getType()->getPluginId(),
      '#options' => $options,
      '#description' => t('Media type provider plugin that is responsible for additional logic related to this media.'),
    );

    $form['type_configuration'] = array(
      '#type' => 'fieldset',
      '#title' => t('Type provider configuration'),
      '#tree' => TRUE,
    );

    foreach ($plugins as $plugin => $definition) {
      $plugin_configuration = $bundle->getType()->getPluginId() == $plugin ? $bundle->type_configuration : array();
      $form['type_configuration'][$plugin] = array(
        '#type' => 'container',
        '#states' => array(
          'visible' => array(
            ':input[name="type"]' => array('value' => $plugin),
          ),
        ),
      );
      $form['type_configuration'][$plugin] += \Drupal::service('plugin.manager.media_entity.type')->createInstance($plugin, $plugin_configuration)->settingsForm($this->entity);
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save media bundle');
    $actions['delete']['#value'] = t('Delete media bundle');
    $actions['delete']['#access'] = $this->entity->access('delete');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\media_entity\MediaBundleInterface $entity */
    parent::copyFormValuesToEntity($entity, $form, $form_state);

    // Use type configuration for the plugin that was chosen.
    $configuration = $form_state->getValue('type_configuration');
    $configuration = empty($configuration[$entity->getType()->getPluginId()]) ? [] : $configuration[$entity->getType()->getPluginId()];
    $entity->setTypeConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var  \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entity;
    $bundle->id = trim($bundle->id());

    $status = $bundle->save();

    $t_args = array('%name' => $bundle->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The media bundle %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The media bundle %name has been added.', $t_args));
      $this->logger('media')->notice('Added bundle %name.', $t_args);
    }

    $form_state->setRedirect($bundle->urlInfo('collection'));
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.media_bundle.delete_form', array('media_bundle' => $this->entity->id()));
  }

}
