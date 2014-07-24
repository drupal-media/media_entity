<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\MediaUITest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Xss;
use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media UI work correctly.
 *
 * @group media
 */
class MediaUITest extends WebTestBase {

  /**
   * The test user.
   *
   * @var string
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity', 'views', 'field_ui');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array(
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
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests a media bundle administration.
   */
  public function testMediaBundles() {
    // Test and create one media bundle.
    $bundle = $this->createMediaBundle();

    // Check if all action links exist.
    $this->assertLinkByHref('admin/structure/media/add');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/fields');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/form-display');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/display');

    // Assert that fields have expected values before editing.
    $this->drupalGet('admin/structure/media');
    $this->clickLink(t('Edit'), 0);
    $this->assertUrl('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $this->assertFieldByName('type', $bundle['type']);

    // Edit and save media bundle form fields with new values.
    $bundle['label'] = $this->randomName();
    $bundle['description'] = $this->randomName();
    $bundle['type'] = $this->randomName();
    $this->drupalPostForm(NULL, $bundle, t('Save media bundle'));

    // Test if edit worked and if new field values have been saved as
    // expected.
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $this->assertFieldByName('type', $bundle['type']);

    // Tests media bundle delete form.
    $this->clickLink(t('Delete'));
    $this->assertUrl('admin/structure/media/manage/' . $bundle['id'] . '/delete');
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertUrl('admin/structure/media');
    $this->assertRaw(t('The media bundle %name has been deleted.', array('%name' => $bundle['label'])));
    $this->assertNoRaw(Xss::filterAdmin($bundle['description']));
  }

  /**
   * Tests the media actions (add/edit/delete).
   */
  public function testMediaWithOnlyOneBundle() {
    // Test and create one media bundle.
    $bundle = $this->createMediaBundle();

    // Assert that media item list is empty.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText('No media items.');

    $this->drupalGet('media/add');
    $this->assertResponse(200);
    $this->assertUrl('media/add/' . $bundle['id']);

    // Tests media item add form.
    $edit = array(
      'name[0][value]' => $this->randomName(),
    );
    $this->drupalPostForm('media/add', $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);

    // Test if the media list contains exactly 1 media bundle.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText($edit['name[0][value]']);

    // Tests media edit form.
    $this->drupalGet('media/' . $media_id . '/edit');
    $edit['name[0][value]'] = $this->randomName();
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');

    // Assert that the media list updates after an edit.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText($edit['name[0][value]']);

    // Tests media delete form.
    $this->drupalPostForm('media/' . $media_id . '/delete', array(), t('Delete'));
    $media_id = \Drupal::entityQuery('media')->execute();
    $this->assertFalse($media_id);

    // Assert that the media list is empty after deleting the media item.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertNoText($edit['name[0][value]']);
    $this->assertText('No media items.');
  }

  /**
   * Tests the "media/add" page.
   *
   * Tests if the "media/add" page gives you a selecting option if there are
   * multiple media bundles available.
   */
  public function testMediaWithMultipleBundles() {
    // Tests and creates the first media bundle.
    $first_media_bundle = $this->createMediaBundle();

    // Test and create a second media bundle.
    $second_media_bundle = $this->createMediaBundle();

    // Test if media/add displays two media bundle options.
    $this->drupalGet('media/add');

    // Checks for the first media bundle.
    $this->assertRaw(String::checkPlain($first_media_bundle['label']));
    $this->assertRaw(Xss::filterAdmin($first_media_bundle['description']));

    // Checks for the second media bundle.
    $this->assertRaw(String::checkPlain($second_media_bundle['label']));
    $this->assertRaw(Xss::filterAdmin($second_media_bundle['description']));

    // Continue testing media bundle filter.
    $this->doTestMediaBundleFilter($first_media_bundle, $second_media_bundle);
  }

  /**
   * Creates and tests a new media bundle.
   *
   * @return array
   *   Returns the media bundle fields.
   */
  public function createMediaBundle() {
    // Generates and holds all media bundle fields.
    $name = $this->randomName();
    $edit = array(
      'id' => strtolower($name),
      'label' => $name,
      'type' => $this->randomName(),
      'description' => $this->randomName(),
    );

    // Create new media bundle.
    $this->drupalPostForm('admin/structure/media/add', $edit, t('Save media bundle'));
    $this->assertText('The media bundle ' . $name . ' has been added.');

    // Check if media bundle is successfully created.
    $this->drupalGet('admin/structure/media');
    $this->assertResponse(200);
    $this->assertRaw(String::checkPlain($edit['label']));
    $this->assertRaw(Xss::filterAdmin($edit['description']));

    return $edit;
  }

  /**
   * Creates a media item in the media bundle that is passed along.
   *
   * @param array $media_bundle
   *   The media bundle the media item should be assigned to.
   *
   * @return array
   *   Returns the
   */
  public function createMediaItem($media_bundle) {
    // Define the media item name.
    $name = $this->randomName();
    $edit = array(
      'name[0][value]' => $name,
    );
    // Save it and retrieve new media item ID, then return all information.
    $this->drupalPostForm('media/add/' . $media_bundle['id'], $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);
    $edit['id'] = $media_id;

    return $edit;
  }

  /**
   * Tests the media list filter functionality.
   */
  public function doTestMediaBundleFilter($first_media_bundle, $second_media_bundle) {
    // Assert that the list is not empty and contains at least 2 media items
    // with each a different media bundle.
    (is_array($first_media_bundle) && is_array($second_media_bundle) ?: $this->assertTrue(FALSE));

    $first_media_item = $this->createMediaItem($first_media_bundle);
    $second_media_item = $this->createMediaItem($second_media_bundle);

    // Go to media item list.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);

    // Assert that all available media items are in the list.
    $this->assertText($first_media_item['name[0][value]']);
    $this->assertText($first_media_bundle['label']);
    $this->assertText($second_media_item['name[0][value]']);
    $this->assertText($second_media_bundle['label']);

    // Filter for each bundle and assert that the list has been updated.
    $this->drupalGet('admin/content/media', array('query' => array('bundle' => $first_media_bundle['id'])));
    $this->assertResponse(200);
    $this->assertText($first_media_item['name[0][value]']);
    $this->assertText($first_media_bundle['label']);
    $this->assertNoText($second_media_item['name[0][value]']);

    $this->drupalGet('admin/content/media', array('query' => array('bundle' => $second_media_bundle['id'])));
    $this->assertResponse(200);
    $this->assertNoText($first_media_item['name[0][value]']);
    $this->assertText($second_media_item['name[0][value]']);
    $this->assertText($second_media_bundle['label']);

    // Filter all and check for all items again.
    $this->drupalGet('admin/content/media', array('query' => array('bundle' => 'All')));
    $this->assertResponse(200);
    $this->assertText($first_media_item['name[0][value]']);
    $this->assertText($first_media_bundle['label']);
    $this->assertText($second_media_item['name[0][value]']);
    $this->assertText($second_media_bundle['label']);
  }
}
