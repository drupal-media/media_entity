<?php


/**
 * @file
 * Contains \Drupal\media_entity\Tests\MediaEntityTestBase.
 */

namespace Drupal\media_entity\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * Base test class for media entity tests.
 */
abstract class MediaEntityTestBase extends WebTestBase {

  /**
   * The test media bundle.
   *
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $testBundle;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->testBundle = $this->drupalCreateMediaBundle();
  }

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
