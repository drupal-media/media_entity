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
      // Media entity permissions.
      'view media',
      'create media',
      'update media',
      'delete media',
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
    $this->assertLinkByHref('admin/structure/media/manage/default');
    $this->assertLinkByHref('admin/structure/media/manage/default/fields');
    $this->assertLinkByHref('admin/structure/media/manage/default/form-display');
    $this->assertLinkByHref('admin/structure/media/manage/default/display');

    // Tests bundle add form.
    $bundle = array(
      'id' => strtolower($this->randomName()),
      'label' => $this->randomString(),
      'description' => $this->randomString(),
    );
    $this->drupalPostForm('admin/structure/media/add', $bundle, t('Save media bundle'));

    // Tests bundle edit form.
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $bundle['label'] = $this->randomString();
    $bundle['description'] = $this->randomString();
    $this->drupalPostForm(NULL, $bundle, t('Save media bundle'));
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);

    // Tests media bundle delete form.
    $this->drupalPostForm(NULL, $bundle, t('Delete media bundle'));
    $this->assertUrl('admin/structure/media/manage/' . $bundle['id'] . '/delete');
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertUrl('admin/structure/media');

    // Tests media add form.
    $edit = array(
      'name' => $this->randomString(),
    );
    $this->drupalPostForm('media/add/default', $edit, t('Save'));
    $this->assertTitle($edit['name'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);
    // Tests edit form.
    $this->drupalGet('media/' . $media_id . '/edit');
    $edit['name'] = $this->randomString();
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertTitle($edit['name'] . ' | Drupal');
    // Tests delete form.
    $this->drupalPostForm('media/' . $media_id . '/delete', array(), t('Delete'));
    $media_id = \Drupal::entityQuery('media')->execute();
    $this->assertFalse($media_id);
  }

}
