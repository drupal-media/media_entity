<?php

/**
 * @file
 * Contains \Drupal\media_entity\Plugin\views\wizard\Media.
 */

namespace Drupal\media_entity\Plugin\views\wizard;

use Drupal\views\Plugin\views\wizard\WizardPluginBase;

/**
 * @todo: replace numbers with constants.
 */

/**
 * Tests creating media views with the wizard.
 *
 * @ViewsWizard(
 *   id = "media",
 *   base_table = "media",
 *   title = @Translation("Media")
 * )
 */
class Media extends WizardPluginBase {

  /**
   * Set the created column.
   */
  protected $createdColumn = 'media_field_data-created';

  /**
   * Set default values for the path field options.
   */
  protected $pathField = array(
    'id' => 'mid',
    'table' => 'mid',
    'field' => 'mid',
    'exclude' => TRUE,
    'alter' => array(
      'alter_text' => TRUE,
      'text' => 'media/[mid]'
    )
  );

  /**
   * Set default values for the filters.
   */
  protected $filters = array(
    'status' => array(
      'value' => TRUE,
      'table' => 'media_field_data',
      'field' => 'status',
      'provider' => 'media'
    )
  );

  /**
   * {@inheritdoc}
   */
  public function getAvailableSorts() {
    // You can't execute functions in properties, so override the method
    return array(
      'media_field_data-name:DESC' => t('Name')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultDisplayOptions() {
    $display_options = parent::defaultDisplayOptions();

    // Add permission-based access control.
    $display_options['access']['type'] = 'perm';
    $display_options['access']['provider'] = 'user';
    $display_options['access']['perm'] = 'access content';

    // Remove the default fields, since we are customizing them here.
    unset($display_options['fields']);

    // Add the name field, so that the display has content if the user switches
    // to a row style that uses fields.
    /* Field: Media: Name */
    $display_options['fields']['name']['id'] = 'name';
    $display_options['fields']['name']['table'] = 'media_field_data';
    $display_options['fields']['name']['field'] = 'name';
    $display_options['fields']['name']['provider'] = 'media';
    $display_options['fields']['name']['label'] = '';
    $display_options['fields']['name']['alter']['alter_text'] = 0;
    $display_options['fields']['name']['alter']['make_link'] = 0;
    $display_options['fields']['name']['alter']['absolute'] = 0;
    $display_options['fields']['name']['alter']['trim'] = 0;
    $display_options['fields']['name']['alter']['word_boundary'] = 0;
    $display_options['fields']['name']['alter']['ellipsis'] = 0;
    $display_options['fields']['name']['alter']['strip_tags'] = 0;
    $display_options['fields']['name']['alter']['html'] = 0;
    $display_options['fields']['name']['hide_empty'] = 0;
    $display_options['fields']['name']['empty_zero'] = 0;

    return $display_options;
  }

}
