<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\BasicTest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that basic functions work correctly.
 *
 * @group media
 */
class BasicTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity');

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
      $id = strtolower($this->randomName(8));
    }
    else {
      $id = $values['bundle'];
    }
    $values += array(
      'id' => $id,
      'label' => $id,
      'type' => $id,
    );

    $bundle = entity_create('media_bundle', $values);
    $status = $bundle->save();

    $this->assertEqual($status, SAVED_NEW, t('Created media bundle %bundle.', array('%bundle' => $bundle->id())));

    return $bundle;
  }

  /**
   * Tests creating a media bundle programmatically.
   */
  public function testMediaBundleCreation() {
    $bundle = $this->drupalCreateMediaBundle();

    $bundle_exists = (bool) entity_load('media_bundle', $bundle->id());
    $this->assertTrue($bundle_exists, 'The new media bundle has been created in the database.');
  }

  /**
   * Tests creating a media entity programmatically.
   */
  public function testMediaEntityCreation() {
    $media = entity_create('media', array(
      'bundle' => 'default',
      'name' => 'Unnamed',
      'type' => 'Unknown',
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
    $media = entity_create('media', array(
      'bundle' => 'default',
      'name' => 'Unnamed',
      'type' => 'Unknown',
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
