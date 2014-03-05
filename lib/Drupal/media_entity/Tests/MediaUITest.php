<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\MediaUITest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Sets up page and article content types.
 */
class MediaUITest extends WebTestBase {

  /**
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $media_bundle;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity', 'field_ui');

  public static function getInfo() {
    return array(
      'name' => 'Media UI tests',
      'description' => 'Ensures that media UI work correctly.',
      'group' => 'Media',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();
    $this->admin_user = $this->drupalCreateUser(array(
      'administer media',
      'administer media fields',
      'administer media form display',
      'administer media display',
    ));
    $this->drupalLogin($this->admin_user);
    $this->media_bundle = entity_create('media_bundle', array(
      'id' => 'default',
      'label' => 'Unnamed',
    ));
    $this->media_bundle->save();
  }

  /**
   * Tests a media bundle administration.
   */
  public function testMediaBundles() {
    $this->drupalGet('admin/structure/media');
    $this->assertResponse(200);

    $this->assertText($this->media_bundle->label());
    $this->assertLinkByHref('admin/structure/media/add');
    $this->assertLinkByHref('structure/media/manage/default');
    $this->assertLinkByHref('structure/media/manage/default/fields');
    $this->assertLinkByHref('structure/media/manage/default/form-display');
    $this->assertLinkByHref('structure/media/manage/default/display');
  }

}
