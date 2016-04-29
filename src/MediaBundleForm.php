<?php

namespace Drupal\media_entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\language\Entity\ContentLanguageSettings;
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
   * Ajax callback triggered by the type provider select element.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxTypeProviderData(array $form, FormStateInterface $form_state) {
    $plugin = $this->entity->getType()->getPluginId();
    return $form['type_configuration'][$plugin];
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
      $form['#title'] = $this->t('Edit %label media bundle', ['%label' => $bundle->label()]);
    }

    $form['label'] = [
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $bundle->label(),
      '#description' => t('The human-readable name of this media bundle.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    // @todo: '#disabled' not always FALSE.
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $bundle->id(),
      '#maxlength' => 32,
      '#disabled' => FALSE,
      '#machine_name' => [
        'exists' => ['\Drupal\media_entity\Entity\MediaBundle', 'exists'],
        'source' => ['label'],
      ],
      '#description' => t('A unique machine-readable name for this media bundle.'),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $bundle->getDescription(),
      '#description' => t('Describe this media bundle. The text will be displayed on the <em>Add new media</em> page.'),
    ];

    $form['queue_thumbnail_downloads'] = [
      '#type' => 'radios',
      '#title' => t('Queue thumbnail downloads'),
      '#default_value' => (int) $bundle->getQueueThumbnailDownloads(),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#description' => t('Download thumbnails via a queue.'),
    ];

    $plugins = $this->mediaTypeManager->getDefinitions();
    $options = [];
    foreach ($plugins as $plugin => $definition) {
      $options[$plugin] = $definition['label'];
    }

    $form['type'] = [
      '#type' => 'select',
      '#title' => t('Type provider'),
      '#default_value' => $bundle->getType()->getPluginId(),
      '#options' => $options,
      '#description' => t('Media type provider plugin that is responsible for additional logic related to this media.'),
      '#ajax' => [
        'callback' => '::ajaxTypeProviderData',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Updating type provider configuration form.'),
        ],
        'wrapper' => 'edit-type-configuration-plugin-wrapper',
      ]
    ];

    $form['type_configuration'] = [
      '#type' => 'fieldset',
      '#title' => t('Type provider configuration'),
      '#tree' => TRUE,
    ];

    if ($plugin = $bundle->getType()->getPluginId()) {
      $plugin_configuration = (empty($this->configurableInstances[$plugin]['plugin_config'])) ? $bundle->type_configuration : $this->configurableInstances[$plugin]['plugin_config'];
      /** @var \Drupal\media_entity\MediaTypeBase $instance */
      $instance = $this->mediaTypeManager->createInstance($plugin, $plugin_configuration);
      // Store the configuration for validate and submit handlers.
      $this->configurableInstances[$plugin]['plugin_config'] = $plugin_configuration;

      $form['type_configuration'][$plugin] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'edit-type-configuration-plugin-wrapper',
        ]
      ];
      $form['type_configuration'][$plugin] += $instance->buildConfigurationForm([], $form_state);
    }

    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => ['node/drupal.content_types'],
      ],
    ];

    $form['workflow'] = [
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    ];

    $workflow_options = [
      'status' => $bundle->getStatus(),
    ];
    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    $workflow_options = array_combine($keys, $keys);
    $form['workflow']['options'] = [
      '#type' => 'checkboxes',
      '#title' => t('Default options'),
      '#default_value' => $workflow_options,
      '#options' => [
        'status' => t('Published'),
      ],
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      ];

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('media', $bundle->id());

      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'media',
          'bundle' => $bundle->id(),
        ],
        '#default_value' => $language_configuration,
      ];
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
    $plugin_configuration = !empty($this->configurableInstances[$plugin]['plugin_config']) ? $this->configurableInstances[$plugin]['plugin_config'] : array();
    $instance = $this->mediaTypeManager->createInstance($plugin, $plugin_configuration);
    $instance->validateConfigurationForm($form, $form_state);
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
    $plugin_configuration = !empty($this->configurableInstances[$plugin]['plugin_config']) ? $this->configurableInstances[$plugin]['plugin_config'] : array();
    $instance = $this->mediaTypeManager->createInstance($plugin, $plugin_configuration);
    $instance->submitConfigurationForm($form, $form_state);
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
    $configuration = $form_state->getValue('type_configuration');

    // Store previous plugin config.
    $plugin = $entity->getType()->getPluginId();
    $this->configurableInstances[$plugin]['plugin_config'] = empty($configuration[$plugin]) ? [] : $configuration[$plugin];

    /** @var \Drupal\media_entity\MediaBundleInterface $entity */
    parent::copyFormValuesToEntity($entity, $form, $form_state);

    // Use type configuration for the plugin that was chosen.
    $plugin = $entity->getType()->getPluginId();
    $plugin_configuration = empty($configuration[$plugin]) ? [] : $configuration[$plugin];
    $entity->set('type_configuration', $plugin_configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var  \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entity;
    $status = $bundle->save();

    $t_args = ['%name' => $bundle->label()];
    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The media bundle %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The media bundle %name has been added.', $t_args));
      $this->logger('media')->notice('Added bundle %name.', $t_args);
    }

    $form_state->setRedirectUrl($bundle->toUrl('collection'));
  }

}
