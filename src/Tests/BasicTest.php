<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\BasicTest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\media_entity\Entity\Media;

/**
 * Ensures that basic functions work correctly.
 *
 * @group media_entity
 */
class BasicTest extends MediaEntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity');

  /**
   * Tests creating a media bundle programmatically.
   */
  public function testMediaBundleCreation() {
    $bundle = $this->drupalCreateMediaBundle();
    /** @var $bundle_storage \Drupal\media_entity\MediaBundleInterface */
    $bundle_storage = $this->container->get('entity.manager')->getStorage('media_bundle');

    $bundle_exists = (bool) $bundle_storage->load($bundle->id());
    $this->assertTrue($bundle_exists, 'The new media bundle has been created in the database.');

    // Test default bundle created from default configuration.
    $this->container->get('module_installer')->install(array('media_entity_test'));
    $test_bundle = $bundle_storage->load('test');
    $this->assertTrue((bool) $test_bundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($test_bundle->get('label'), 'Test bundle', 'Correct label detected.');
    $this->assertEqual($test_bundle->get('description'), 'Test bundle.', 'Correct description detected.');
    $this->assertEqual($test_bundle->get('type'), 'generic', 'Correct plugin ID detected.');
    $this->assertEqual($test_bundle->get('type_configuration'), array(), 'Correct plugin configuration detected.');
    $this->assertEqual($test_bundle->get('field_map'), array(), 'Correct field map detected.');
  }

  /**
   * Tests creating a media entity programmatically.
   */
  public function testMediaEntityCreation() {
    $media = Media::create(array(
      'bundle' => $this->testBundle->id(),
      'name' => 'Unnamed',
    ));
    $media->save();

    $media_not_exist = (bool) entity_load('media', rand(1000, 9999));
    $this->assertFalse($media_not_exist, 'The media entity does not exist.');

    $media_exists = (bool) entity_load('media', $media->id());
    $this->assertTrue($media_exists, 'The new media entity has been created in the database.');
  }

  /**
   * Runs basic tests for media_access function.
   */
  public function testMediaAccess() {
    $media = Media::create(array(
      'bundle' => $this->testBundle->id(),
      'name' => 'Unnamed',
    ));
    $media->save();

    // Ensures user without 'view media' permission can't access media pages.
    $web_user1 = $this->drupalCreateUser();
    $this->drupalLogin($web_user1);
    $this->drupalGet('media/' . $media->id());
    $this->assertResponse(403);

    // Ensures user with 'view media' permission can access media pages.
    $web_user2 = $this->drupalCreateUser(array('view media'));
    $this->drupalLogin($web_user2);
    $this->drupalGet('media/' . $media->id());
    $this->assertResponse(200);
  }

}
