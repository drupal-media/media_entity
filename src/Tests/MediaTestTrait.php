<?php


/**
 * @file
 * Contains \Drupal\media_entity\Tests\MediaTestTrait.
 */

namespace Drupal\media_entity\Tests;

use Drupal\media_entity\Entity\MediaBundle;

/**
 * Provides common functionality for media entity test classes.
 */
trait MediaTestTrait {

  /**
   * Creates media bundle.
   *
   * @param array $values
   *   The media bundle values.
   *
   * @param string $type_name
   *   (optional) The media type provider plugin that is responsible for
   *   additional logic related to this media).
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Returns newly created media bundle.
   */
  protected function drupalCreateMediaBundle(array $values = array(), $type_name = 'generic') {
    if (!isset($values['bundle'])) {
      $id = strtolower($this->randomMachineName());
    }
    else {
      $id = $values['bundle'];
    }
    $values += array(
      'id' => $id,
      'label' => $id,
      'type' => $type_name,
      'type_configuration' => array(),
      'field_map' => array(),
      'status' => 1,
    );

    $bundle = MediaBundle::create($values);
    $status = $bundle->save();

    $this->assertEqual($status, SAVED_NEW, t('Created media bundle %bundle.', array('%bundle' => $bundle->id())));

    return $bundle;
  }

}
