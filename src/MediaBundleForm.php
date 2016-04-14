<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaBundleForm.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\media_entity\MediaTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for node type forms.
 */
class MediaBundleForm extends EntityForm {

  /**
   * The instantiated plugin instances that have configuration forms.
   *
   * @var \Drupal\Core\Plugin\PluginFormInterface[]
   */
  protected $configurableInstances = [];

  /**
   * Manager for media entity type plugins.
   *
   * @var \Drupal\media_entity\MediaTypeManager
   */
  protected $mediaTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\media_entity\MediaTypeManager $media_type_manager
   */
  public function __construct(MediaTypeManager $media_type_manager) {
    $this->mediaTypeManager = $media_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.media_entity.type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $form['#entity'] = $bundle = $this->entity;
    $form_state->set('bundle', $bundle->id());

    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add media bundle');
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

    $form['queue_thumbnail_downloads'] = array(
      '#type' => 'radios',
      '#title' => t('Queue thumbnail downloads'),
      '#default_value' => (int) $bundle->getQueueThumbnailDownloads(),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#description' => t('Download thumbnails via a queue.'),
    );

    $plugins = $this->mediaTypeManager->getDefinitions();
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
      /** @var \Drupal\media_entity\MediaTypeBase $instance */
      $instance = $this->mediaTypeManager->createInstance($plugin, $plugin_configuration);
      $form['type_configuration'][$plugin] += $instance->buildConfigurationForm([], $form_state);
      // Store the instance for validate and submit handlers.
      $this->configurableInstances[$plugin] = $instance;
    }

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
      '#attached' => array(
        'library' => array('node/drupal.content_types'),
      ),
    );

    $form['workflow'] = array(
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    );

    $workflow_options = array(
      'status' => $bundle->getStatus(),
    );
    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    $workflow_options = array_combine($keys, $keys);
    $form['workflow']['options'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Default options'),
      '#default_value' => $workflow_options,
      '#options' => array(
        'status' => t('Published'),
      ),
    );

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = array(
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      );

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('media', $bundle->id());

      $form['language']['language_configuration'] = array(
        '#type' => 'language_configuration',
        '#entity_information' => array(
          'entity_type' => 'media',
          'bundle' => $bundle->id(),
        ),
        '#default_value' => $language_configuration,
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Let the selected plugin validate its settings.
    $plugin = $this->entity->getType()->getPluginId();
    $this->configurableInstances[$plugin]->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $workflow_options = ['status'];
    foreach ($workflow_options as $option) {
      $this->entity->$option = (bool) $form_state->getValue(['options', $option]);
    }

    // Let the selected plugin save its settings.
    $plugin = $this->entity->getType()->getPluginId();
    $this->configurableInstances[$plugin]->submitConfigurationForm($form, $form_state);
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
    $entity->set('type_configuration', $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var  \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entity;
    $status = $bundle->save();

    $t_args = array('%name' => $bundle->label());
    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The media bundle %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The media bundle %name has been added.', $t_args));
      $this->logger('media')->notice('Added bundle %name.', $t_args);
    }

    $form_state->setRedirectUrl($bundle->urlInfo('collection'));
  }

}
