<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaTypeBase.
 */

namespace Drupal\media_entity;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Base implementation of media type plugin.
 */
abstract class MediaTypeBase extends PluginBase implements MediaTypeInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * The entity manager object.
   *
   * @var \Drupal\Core\Entity\EntityManager;
   */
  protected $entityManager;

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
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   Entity manager service.
   * @param \Drupal\Core\Config\Config $config
   *   Media entity config object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManager $entity_manager, Config $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('config.factory')->get('media_entity.settings')
    );
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
}
