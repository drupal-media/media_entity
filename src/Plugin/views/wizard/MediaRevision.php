<?php

/**
 * @file
 * Contains \Drupal\media_entity\Plugin\views\wizard\MediaRevision.
 */

namespace Drupal\media_entity\Plugin\views\wizard;

use Drupal\views\Plugin\views\wizard\WizardPluginBase;

/**
 * Tests creating media revision views with the wizard.
 *
 * @ViewsWizard(
 *   id = "media_revision",
 *   base_table = "media_revision",
 *   title = @Translation("Media revisions")
 * )
 */
class MediaRevision extends WizardPluginBase {

  /**
   * Set the created column.
   */
  protected $createdColumn = 'changed';

  /**
   * Set default values for the path field options.
   */
  protected $pathField = array(
    'id' => 'vid',
    'table' => 'media_revision',
    'field' => 'vid',
    'exclude' => TRUE,
    'alter' => array(
      'alter_text' => FALSE,
    )
  );

  /**
   * Set the additional information for the pathField property.
   */
  protected $pathFieldsSupplemental = array(
    array(
      'id' => 'mid',
      'table' => 'media',
      'field' => 'mid',
      'exclude' => TRUE,
    )
  );

  /**
   * Set default values for the filters.
   */
  protected $filters = array(
    'status' => array(
      'value' => TRUE,
      'table' => 'media_field_revision',
      'field' => 'status',
      'provider' => 'media'
    )
  );

  /**
   * {@inheritdoc}
   */
  protected function defaultDisplayOptions() {
    $display_options = parent::defaultDisplayOptions();

    // Add permission-based access control.
    $display_options['access']['type'] = 'perm';
    $display_options['access']['provider'] = 'user';
    $display_options['access']['perm'] = 'view revisions';

    // Remove the default fields, since we are customizing them here.
    unset($display_options['fields']);

    /* Field: Media revision: Created date */
    $display_options['fields']['changed']['id'] = 'changed';
    $display_options['fields']['changed']['table'] = 'media_field_revision';
    $display_options['fields']['changed']['field'] = 'changed';
    $display_options['fields']['changed']['provider'] = 'media';
    $display_options['fields']['changed']['alter']['alter_text'] = FALSE;
    $display_options['fields']['changed']['alter']['make_link'] = FALSE;
    $display_options['fields']['changed']['alter']['absolute'] = FALSE;
    $display_options['fields']['changed']['alter']['trim'] = FALSE;
    $display_options['fields']['changed']['alter']['word_boundary'] = FALSE;
    $display_options['fields']['changed']['alter']['ellipsis'] = FALSE;
    $display_options['fields']['changed']['alter']['strip_tags'] = FALSE;
    $display_options['fields']['changed']['alter']['html'] = FALSE;
    $display_options['fields']['changed']['hide_empty'] = FALSE;
    $display_options['fields']['changed']['empty_zero'] = FALSE;

    /* Field: Media revision: Name */
    $display_options['fields']['name']['id'] = 'name';
    $display_options['fields']['name']['table'] = 'media_field_revision';
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
