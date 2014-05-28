<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\BasicTest.
 */

namespace Drupal\media_entity_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Sets up page and article content types.
 */
class BasicTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity_example');

  public static function getInfo() {
    return array(
      'name' => 'Basic example tests',
      'description' => 'Ensures that the shipped config works correctly.',
      'group' => 'Media',
    );
  }

  /**
   * Tests the shipped default config.
   */
  public function testDefaultConfig() {
    $bundle_entity = \Drupal::entityManager()
      ->getStorage('media_bundle')
      ->load('image');

    $this->assertTrue(is_object($bundle_entity) && ($bundle_entity instanceof \Drupal\Core\Entity\EntityInterface), 'The image media bundle was correctly created.');
  }

}
