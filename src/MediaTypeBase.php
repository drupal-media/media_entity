<?php

namespace Drupal\media_entity;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base implementation of media type plugin.
 */
abstract class MediaTypeBase extends PluginBase implements SourceFieldInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface;
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface;
   */
  protected $entityFieldManager;

  /**
   * Media entity image config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Config\Config $config
   *   Media entity config object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, Config $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->config = $config;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory')->get('media_entity.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'source_field' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function attachConstraints(MediaInterface $media) {}

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];

    foreach ($this->entityFieldManager->getFieldStorageDefinitions('media') as $field_name => $field) {
      $allowed_type = in_array($field->getType(), $this->pluginDefinition['allowed_field_types'], TRUE);
      if ($allowed_type && !$field->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    // If there are existing fields to choose from, allow the user to reuse one.
    if ($options) {
      $form['source_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Field with source information.'),
        '#default_value' => $this->configuration['source_field'],
        '#empty_option' => $this->t('- Create -'),
        '#empty_value' => NULL,
        '#options' => $options,
        '#description' => $this->t('The field on media items of this type that will store the source information.'),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function getDefaultName(MediaInterface $media) {
    return 'media:' . $media->bundle() . ':' . $media->uuid();
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceField(MediaBundleInterface $bundle) {
    // If we don't know the name of the source field, we definitely need to
    // create it.
    if (empty($this->configuration['source_field'])) {
      return $this->createSourceField($bundle);
    }
    // Even if we do know the name of the source field, there is no guarantee
    // that it already exists. So check for the field and create it if needed.
    $field = $this->configuration['source_field'];
    $fields = $this->entityFieldManager->getFieldDefinitions('media', $bundle->id());
    return isset($fields[$field]) ? $fields[$field] : $this->createSourceField($bundle);
  }

  /**
   * Returns the source field storage definition.
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface
   *   The field storage definition. Will be unsaved if new.
   */
  protected function getSourceFieldStorage() {
    // If we don't know the name of the source field, we definitely need to
    // create its storage.
    if (empty($this->configuration['source_field'])) {
      return $this->createSourceFieldStorage();
    }
    // Even if we do know the name of the source field, we cannot guarantee that
    // its storage exists. So check for the storage and create it if needed.
    $field = $this->configuration['source_field'];
    $fields = $this->entityFieldManager->getFieldStorageDefinitions('media');
    return isset($fields[$field]) ? $fields[$field] : $this->createSourceFieldStorage();
  }

  /**
   * Creates the source field storage definition.
   *
   * @return \Drupal\field\FieldStorageConfigInterface
   *   The unsaved field storage definition.
   */
  abstract protected function createSourceFieldStorage();

  /**
   * Creates the source field definition for a bundle.
   *
   * @param \Drupal\media_entity\MediaBundleInterface $bundle
   *   The bundle.
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The unsaved field definition. The field storage definition, if new,
   *   should also be unsaved.
   */
  abstract protected function createSourceField(MediaBundleInterface $bundle);

  /**
   * Determine the name of the source field.
   *
   * @return string
   *   The source field name. If one is already stored in configuration, it is
   *   returned. Otherwise, a new, unused one is generated.
   */
  protected function getSourceFieldName() {
    if ($this->configuration['source_field']) {
      return $this->configuration['source_field'];
    }

    $base_id = 'field_media_' . $this->getPluginId();
    $tries = 0;
    $storage = $this->entityTypeManager->getStorage('field_storage_config');

    // Iterate at least once, until no field with the generated ID is found.
    do {
      $id = $base_id;
      // If we've tried before, increment and append the suffix.
      if ($tries) {
        $id .= '_' . $tries;
      }
      $field = $storage->load('media.' . $id);
      $tries++;
    }
    while ($field);

    return $id;
  }

}
