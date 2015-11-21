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
   * The test media bundle.
   *
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $testBundle;

  /**
   * Creates media bundle.
   *
   * @param array $values
   *   The media bundle values.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Returns newly created media bundle.
   */
  protected function drupalCreateMediaBundle(array $values = array()) {
    if (!isset($values['bundle'])) {
      $id = strtolower($this->randomMachineName());
    }
    else {
      $id = $values['bundle'];
    }
    $values += array(
      'id' => $id,
      'label' => $id,
      'type' => 'generic',
      'type_configuration' => array(),
      'field_map' => array(),
    );

    $bundle = MediaBundle::create($values);
    $status = $bundle->save();

    $this->assertEqual($status, SAVED_NEW, t('Created media bundle %bundle.', array('%bundle' => $bundle->id())));

    return $bundle;
  }

}
