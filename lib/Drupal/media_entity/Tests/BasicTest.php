<?php

/**
 * @file
 * Definition of Drupal\media_entity\Tests\BasicTest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\Core\Session\AccountInterface;
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
  public static $modules = array('media_entity');

  public static function getInfo() {
    return array(
      'name' => 'Basic tests',
      'description' => 'Ensures that basic functions work correctly.',
      'group' => 'Media',
    );
  }

  protected function drupalCreateMediaBundle(array $values = array()) {
    if (!isset($values['bundle'])) {
      $id = strtolower($this->randomName(8));
    }
    else {
      $id = $values['bundle'];
    }
    $values += array(
      'bundle' => $id,
      'name' => $id,
    );

    $bundle = entity_create('media_bundle', $values);
    $status = $bundle->save();
    menu_router_rebuild();

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

}
