<?php

/**
 * @file
 * Contains Drupal\media_entity\Plugin\views\field\Media.
 */

namespace Drupal\media_entity\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * Field handler to provide simple renderer that allows linking to a media item.
 * Definition terms:
 * - link_to_media default: Should this field have the checkbox "link to media"
 *   enabled by default.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("media_name")
 */
class MediaName extends FieldPluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\field\FieldPluginBase::init().
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    // Don't add the additional fields to groupby.
    if (!empty($this->options['link_to_media'])) {
      $this->additional_fields['mid'] = array('table' => 'media', 'field' => 'mid');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['link_to_media_name'] = array('default' => isset($this->definition['link_to_media_name default']) ? $this->definition['link_to_media_name default'] : FALSE, 'bool' => TRUE);
    return $options;
  }

  /**
   * Provide link to media option.
   */
  public function buildOptionsForm(&$form, &$form_state) {
    $form['link_to_media_name'] = array(
      '#title' => t('Link this field to the original piece of content'),
      '#description' => t("Enable to override this field's links."),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['link_to_media_name']),
    );

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * Prepares link to the media item.
   *
   * @param string $data
   *   The XSS safe string for the link text.
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from a single row of a view's query result.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($data, ResultRow $values) {
    if (!empty($this->options['link_to_media_name']) && !empty($this->additional_fields['mid'])) {
      if ($data !== NULL && $data !== '') {
        $this->options['alter']['make_link'] = TRUE;
        $this->options['alter']['path'] = "media/" . $this->getValue($values, 'mid');
        if (isset($this->aliases['langcode'])) {
          $languages = \Drupal::languageManager()->getLanguages();
          $langcode = $this->getValue($values, 'langcode');
          if (isset($languages[$langcode])) {
            $this->options['alter']['language'] = $languages[$langcode];
          }
          else {
            unset($this->options['alter']['language']);
          }
        }
      }
      else {
        $this->options['alter']['make_link'] = FALSE;
      }
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    return $this->renderLink($this->sanitizeValue($value), $values);
  }

}
