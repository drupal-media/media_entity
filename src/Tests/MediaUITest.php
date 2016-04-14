<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\MediaUITest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\Component\Utility\Xss;
use Drupal\media_entity\Entity\Media;
use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media UI work correctly.
 *
 * @group media_entity
 */
class MediaUITest extends WebTestBase {

  use MediaTestTrait;

  /**
   * The test user.
   *
   * @var \Drupal\User\UserInterface
   */
  protected $adminUser;

  /**
   * The test media bundle.
   *
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $testBundle;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['media_entity', 'field_ui', 'views_ui', 'node', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->testBundle = $this->drupalCreateMediaBundle();
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->adminUser = $this->drupalCreateUser([
      'administer media',
      'administer media fields',
      'administer media form display',
      'administer media display',
      // Media entity permissions.
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      // Other permissions.
      'administer views',
      'access content overview',
      'view all revisions',
    ]);
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
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $this->assertFieldByName('type', $bundle['type']);

    // Edit and save media bundle form fields with new values.
    $bundle['label'] = $this->randomMachineName();
    $bundle['description'] = $this->randomMachineName();
    $bundle['type'] = 'generic';
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
    $this->drupalPostForm(NULL, [], t('Delete'));
    $this->assertUrl('admin/structure/media');
    $this->assertRaw(t('The media bundle %name has been deleted.', ['%name' => $bundle['label']]));
    $this->assertNoRaw(Xss::filterAdmin($bundle['description']));
  }

  /**
   * Tests the media actions (add/edit/delete).
   */
  public function testMediaWithOnlyOneBundle() {
    // Assert that media item list is empty.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText('No content available.');

    $this->drupalGet('media/add');
    $this->assertResponse(200);
    $this->assertUrl('media/add/' . $this->testBundle->id());

    // Tests media item add form.
    $edit = [
      'name[0][value]' => $this->randomMachineName(),
    ];
    $this->drupalPostForm('media/add', $edit, t('Save and publish'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);

    // Test if the media list contains exactly 1 media bundle.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText($edit['name[0][value]']);

    // Tests media edit form.
    $this->drupalGet('media/' . $media_id . '/edit');
    $edit['name[0][value]'] = $this->randomMachineName();
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));
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
    $this->assertText('No content available.');
  }

  /**
   * Tests the views wizards provided by the media module.
   */
  public function testMediaViewsWizard() {

    $data = [
      'name' => $this->randomMachineName(),
      'bundle' => $this->testBundle->id(),
      'type' => 'Unknown',
      'uid' => $this->adminUser->id(),
      'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      'status' => Media::PUBLISHED,
    ];
    $media = Media::create($data);
    $media->save();

    // Test the Media wizard.
    $this->drupalPostForm('admin/structure/views/add', [
      'label' => 'media view',
      'id' => 'media_test',
      'show[wizard_key]' => 'media',
      'page[create]' => 1,
      'page[title]' => 'media_test',
      'page[path]' => 'media_test',
    ], t('Save and edit'));

    $this->drupalGet('media_test');
    $this->assertText($data['name']);

    user_role_revoke_permissions('anonymous', ['access content']);
    $this->drupalLogout();
    $this->drupalGet('media_test');
    $this->assertResponse(403);

    $this->drupalLogin($this->adminUser);

    // Test the MediaRevision wizard.
    $this->drupalPostForm('admin/structure/views/add', [
      'label' => 'media revision view',
      'id' => 'media_revision',
      'show[wizard_key]' => 'media_revision',
      'page[create]' => 1,
      'page[title]' => 'media_revision',
      'page[path]' => 'media_revision',
    ], t('Save and edit'));

    $this->drupalGet('media_revision');
    // Check only for the label of the changed field as we want to only test
    // if the field is present and not its value.
    $this->assertText($data['name']);

    user_role_revoke_permissions('anonymous', ['view revisions']);
    $this->drupalLogout();
    $this->drupalGet('media_revision');
    $this->assertResponse(403);
  }

  /**
   * Tests the "media/add" and "admin/content/media" pages.
   *
   * Tests if the "media/add" page gives you a selecting option if there are
   * multiple media bundles available.
   */
  public function testMediaWithMultipleBundles() {
    // Test access to media overview page.
    $this->drupalLogout();
    $this->drupalGet('admin/content/media');
    $this->assertResponse(403);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/content');

    // Test there is a media tab in the menu.
    $this->clickLink('Media');
    $this->assertResponse(200);
    $this->assertText('No content available.');

    // Tests and creates the first media bundle.
    $first_media_bundle = $this->createMediaBundle();

    // Test and create a second media bundle.
    $second_media_bundle = $this->createMediaBundle();

    // Test if media/add displays two media bundle options.
    $this->drupalGet('media/add');

    // Checks for the first media bundle.
    $this->assertRaw($first_media_bundle['label']);
    $this->assertRaw(Xss::filterAdmin($first_media_bundle['description']));

    // Checks for the second media bundle.
    $this->assertRaw($second_media_bundle['label']);
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
    $name = $this->randomMachineName();
    $edit = [
      'id' => strtolower($name),
      'label' => $name,
      'type' => 'generic',
      'description' => $this->randomMachineName(),
    ];

    // Create new media bundle.
    $this->drupalPostForm('admin/structure/media/add', $edit, t('Save media bundle'));
    $this->assertText('The media bundle ' . $name . ' has been added.');

    // Check if media bundle is successfully created.
    $this->drupalGet('admin/structure/media');
    $this->assertResponse(200);
    $this->assertRaw($edit['label']);
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
    $name = $this->randomMachineName();
    $edit = [
      'name[0][value]' => $name,
    ];
    // Save it and retrieve new media item ID, then return all information.
    $this->drupalPostForm('media/add/' . $media_bundle['id'], $edit, t('Save and publish'));
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
    $this->assertLink('Add media');

    // Assert that all available media items are in the list.
    $this->assertText($first_media_item['name[0][value]']);
    $this->assertText($first_media_bundle['label']);
    $this->assertText($second_media_item['name[0][value]']);
    $this->assertText($second_media_bundle['label']);

    // Filter for each bundle and assert that the list has been updated.
    $this->drupalGet('admin/content/media', ['query' => ['provider' => $first_media_bundle['id']]]);
    $this->assertResponse(200);
    $this->assertText($first_media_item['name[0][value]']);
    $this->assertText($first_media_bundle['label']);
    $this->assertNoText($second_media_item['name[0][value]']);

    $this->drupalGet('admin/content/media', ['query' => ['provider' => $second_media_bundle['id']]]);
    $this->assertResponse(200);
    $this->assertNoText($first_media_item['name[0][value]']);
    $this->assertText($second_media_item['name[0][value]']);
    $this->assertText($second_media_bundle['label']);

    // Filter all and check for all items again.
    $this->drupalGet('admin/content/media', ['query' => ['provider' => 'All']]);
    $this->assertResponse(200);
    $this->assertText($first_media_item['name[0][value]']);
    $this->assertText($first_media_bundle['label']);
    $this->assertText($second_media_item['name[0][value]']);
    $this->assertText($second_media_bundle['label']);
  }

}
