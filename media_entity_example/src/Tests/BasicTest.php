<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\BasicTest.
 */

namespace Drupal\media_entity_example\Tests;

use Drupal\media_entity\MediaBundleInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Ensures that the shipped config works correctly.
 *
 * @group media
 */
class BasicTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity_example');

  /**
   * Tests the shipped default config.
   */
  public function testDefaultConfig() {
    $bundle_entity = \Drupal::entityManager()
      ->getStorage('media_bundle')
      ->load('image');

    $this->assertTrue($bundle_entity instanceof MediaBundleInterface, 'The image media bundle was correctly created.');
  }

}
