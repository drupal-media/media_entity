<?php

namespace Drupal\Tests\media_entity\FunctionalJavascript;

use Drupal\media_entity\Entity\Media;

/**
 * Ensures that media UI work correctly.
 *
 * @group media_entity
 */
class MediaUiTest extends MediaEntityJavascriptTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'media_entity_test_type',
  ];

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
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests a media bundle administration.
   */
  public function testMediaBundles() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Test the creation of a media bundle using the UI.
    $name = $this->randomMachineName();
    $description = $this->randomMachineName();
    $this->drupalGet('admin/structure/media/add');
    $page->fillField('label', $name);
    $session->wait(2000);
    $page->selectFieldOption('type', 'generic');
    $page->fillField('description', $description);
    $page->pressButton('Save media bundle');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains('The media bundle ' . $name . ' has been added.');
    $this->drupalGet('admin/structure/media');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains($name);
    $assert_session->pageTextContains($description);

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle_storage */
    $bundle_storage = $this->container->get('entity_type.manager')->getStorage('media_bundle');
    $this->testBundle = $bundle_storage->load(strtolower($name));

    // Check if all action links exist.
    $assert_session->linkByHrefExists('admin/structure/media/add');
    $assert_session->linkByHrefExists('admin/structure/media/manage/' . $this->testBundle->id());
    $assert_session->linkByHrefExists('admin/structure/media/manage/' . $this->testBundle->id() . '/fields');
    $assert_session->linkByHrefExists('admin/structure/media/manage/' . $this->testBundle->id() . '/form-display');
    $assert_session->linkByHrefExists('admin/structure/media/manage/' . $this->testBundle->id() . '/display');

    // Assert that fields have expected values before editing.
    $page->clickLink('Edit');
    $assert_session->fieldValueEquals('label', $name);
    $assert_session->fieldValueEquals('description', $description);
    $assert_session->fieldValueEquals('type', 'generic');
    $assert_session->fieldValueEquals('label', $name);
    $assert_session->checkboxNotChecked('edit-options-new-revision');
    $assert_session->checkboxChecked('edit-options-status');
    $assert_session->checkboxNotChecked('edit-options-queue-thumbnail-downloads');
    $assert_session->pageTextContains('Create new revision');
    $assert_session->pageTextContains('Automatically create a new revision of media entities. Users with the Administer media permission will be able to override this option.');
    $assert_session->pageTextContains('Download thumbnails via a queue.');
    $assert_session->pageTextContains('Entities will be automatically published when they are created.');
    $assert_session->pageTextContains("This type provider doesn't need configuration.");
    $assert_session->pageTextContains('No metadata fields available.');
    $assert_session->pageTextContains('Media type plugins can provide metadata fields such as title, caption, size information, credits, ... Media entity can automatically save this metadata information to entity fields, which can be configured below. Information will only be mapped if the entity field is empty.');

    // Try to change media type and check if new configuration sub-form appears.
    $page->selectFieldOption('type', 'test_type');
    $this->waitForAjaxToFinish();
    $assert_session->fieldExists('Test config value');
    $assert_session->fieldValueEquals('Test config value', 'This is default value.');
    $assert_session->fieldExists('Field 1');
    $assert_session->fieldExists('Field 2');

    // Test if the edit machine name is not editable.
    $assert_session->fieldDisabled('Machine-readable name');

    // Edit and save media bundle form fields with new values.
    $new_name = $this->randomMachineName();
    $new_description = $this->randomMachineName();
    $page->fillField('label', $new_name);
    $page->fillField('description', $new_description);
    $page->selectFieldOption('type', 'test_type');
    $page->fillField('Test config value', 'This is new config value.');
    $page->selectFieldOption('field_mapping[field_1]', 'name');
    $page->checkField('options[new_revision]');
    $page->uncheckField('options[status]');
    $page->checkField('options[queue_thumbnail_downloads]');
    $page->pressButton('Save media bundle');
    $assert_session->statusCodeEquals(200);

    // Test if edit worked and if new field values have been saved as expected.
    $this->drupalGet('admin/structure/media/manage/' . $this->testBundle->id());
    $assert_session->fieldValueEquals('label', $new_name);
    $assert_session->fieldValueEquals('description', $new_description);
    $assert_session->fieldValueEquals('type', 'test_type');
    $assert_session->checkboxChecked('options[new_revision]');
    $assert_session->checkboxNotChecked('options[status]');
    $assert_session->checkboxChecked('options[queue_thumbnail_downloads]');
    $assert_session->fieldValueEquals('Test config value', 'This is new config value.');
    $assert_session->fieldValueEquals('Field 1', 'name');
    $assert_session->fieldValueEquals('Field 2', '_none');

    /** @var \Drupal\media_entity\MediaBundleInterface $loaded_bundle */
    $loaded_bundle = $this->container->get('entity_type.manager')
      ->getStorage('media_bundle')
      ->load($this->testBundle->id());
    $this->assertEquals($loaded_bundle->id(), $this->testBundle->id());
    $this->assertEquals($loaded_bundle->label(), $new_name);
    $this->assertEquals($loaded_bundle->getDescription(), $new_description);
    $this->assertEquals($loaded_bundle->getType()->getPluginId(), 'test_type');
    $this->assertEquals($loaded_bundle->getType()->getConfiguration()['test_config_value'], 'This is new config value.');
    $this->assertTrue($loaded_bundle->shouldCreateNewRevision());
    $this->assertTrue($loaded_bundle->getQueueThumbnailDownloads());
    $this->assertFalse($loaded_bundle->getStatus());
    $this->assertEquals($loaded_bundle->field_map, ['field_1' => 'name']);

    // Test that a media being created with default status to "FALSE" will be
    // created unpublished.
    /** @var \Drupal\media_entity\MediaInterface $unpublished_media */
    $unpublished_media = Media::create(['name' => 'unpublished test media', 'bundle' => $loaded_bundle->id()]);
    $this->assertFalse($unpublished_media->isPublished());
    $unpublished_media->delete();

    // Tests media bundle delete form.
    $page->clickLink('Delete');
    $assert_session->addressEquals('admin/structure/media/manage/' . $this->testBundle->id() . '/delete');
    $page->pressButton('Delete');
    $assert_session->addressEquals('admin/structure/media');
    $assert_session->pageTextContains('The media bundle ' . $new_name . ' has been deleted.');

    // Test bundle delete prevention when there is existing media.
    $bundle2 = $this->drupalCreateMediaBundle();
    $label2 = $bundle2->label();
    $media = Media::create(['name' => 'lorem ipsum', 'bundle' => $bundle2->id()]);
    $media->save();
    $this->drupalGet('admin/structure/media/manage/' . $bundle2->id());
    $page->clickLink('Delete');
    $assert_session->addressEquals('admin/structure/media/manage/' . $bundle2->id() . '/delete');
    $assert_session->fieldNotExists('edit-submit');
    $assert_session->pageTextContains("$label2 is used by 1 piece of content on your site. You can not remove this content type until you have removed all of the $label2 content.");

  }

  /**
   * Tests the media actions (add/edit/delete).
   */
  public function testMediaWithOnlyOneBundle() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->drupalCreateMediaBundle();

    // Assert that media item list is empty.
    $this->drupalGet('admin/content/media');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains('No content available.');

    $this->drupalGet('media/add');
    $assert_session->statusCodeEquals(200);
    $assert_session->addressEquals('media/add/' . $bundle->id());
    $assert_session->checkboxChecked('edit-revision');

    // Tests media item add form.
    $media_name = $this->randomMachineName();
    $page->fillField('name[0][value]', $media_name);
    $revision_log_message = $this->randomString();
    $page->fillField('revision_log', $revision_log_message);
    $page->pressButton('Save and publish');
    $media_id = $this->container->get('entity.query')->get('media')->execute();
    $media_id = reset($media_id);
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged($media_id);
    $this->assertEquals($media->getRevisionLogMessage(), $revision_log_message);
    $assert_session->titleEquals($media->label() . ' | Drupal');

    // Test if the media list contains exactly 1 media bundle.
    $this->drupalGet('admin/content/media');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains($media->label());

    // Tests media edit form.
    $media_name2 = $this->randomMachineName();
    $this->drupalGet('media/' . $media_id . '/edit');
    $assert_session->checkboxNotChecked('edit-revision');
    $media_name = $this->randomMachineName();
    $page->fillField('name[0][value]', $media_name2);
    $page->pressButton('Save and keep published');
    $assert_session->titleEquals($media_name2 . ' | Drupal');

    // Assert that the media list updates after an edit.
    $this->drupalGet('admin/content/media');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains($media_name2);

    // Test that there is no empty vertical tabs element, if the container is
    // empty (see #2750697).
    // Make the "Publisher ID" and "Created" fields hidden.
    $this->drupalGet('/admin/structure/media/manage/' . $bundle->id . '/form-display');
    $page->selectFieldOption('fields[created][parent]', 'hidden');
    $page->selectFieldOption('fields[uid][parent]', 'hidden');
    $page->pressButton('Save');
    // Assure we are testing with a user without permission to manage revisions.
    $this->drupalLogout();
    $this->drupalLogin($this->nonAdminUser);
    // Check the container is not present.
    $this->drupalGet('media/' . $media_id . '/edit');
    // An empty tab container would look like this.
    $raw_html = '<div data-drupal-selector="edit-advanced" data-vertical-tabs-panes><input class="vertical-tabs__active-tab" data-drupal-selector="edit-advanced-active-tab" type="hidden" name="advanced__active_tab" value="" />' . "\n" . '</div>';
    $assert_session->responseNotContains($raw_html);
    // Continue testing as admin.
    $this->drupalLogout();
    $this->drupalLogin($this->adminUser);

    // Enable revisions by default.
    $bundle->setNewRevision(TRUE);
    $bundle->save();
    $this->drupalGet('media/' . $media_id . '/edit');
    $assert_session->checkboxChecked('edit-revision');
    $page->fillField('name[0][value]', $media_name);
    $page->fillField('revision_log', $revision_log_message);
    $page->pressButton('Save and keep published');
    $assert_session->titleEquals($media_name . ' | Drupal');
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged($media_id);
    $this->assertEquals($media->getRevisionLogMessage(), $revision_log_message);

    // Tests media delete form.
    $this->drupalGet('media/' . $media_id . '/edit');
    $page->clickLink('Delete');
    $assert_session->pageTextContains('This action cannot be undone');
    $page->pressButton('Delete');
    $media_id = \Drupal::entityQuery('media')->execute();
    $this->assertFalse($media_id);

    // Assert that the media list is empty after deleting the media item.
    $this->drupalGet('admin/content/media');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextNotContains($media_name);
    $assert_session->pageTextContains('No content available.');

  }

  /**
   * Tests the "media/add" and "admin/content/media" pages.
   *
   * Tests if the "media/add" page gives you a selecting option if there are
   * multiple media bundles available.
   */
  public function testMediaWithMultipleBundles() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Test access to media overview page.
    $this->drupalLogout();
    $this->drupalGet('admin/content/media');
    $assert_session->statusCodeEquals(403);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/content');

    // Test there is a media tab in the menu.
    $page->clickLink('Media');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains('No content available.');

    // Tests and creates the first media bundle.
    $first_media_bundle = $this->drupalCreateMediaBundle(['description' => $this->randomMachineName(32)]);

    // Test and create a second media bundle.
    $second_media_bundle = $this->drupalCreateMediaBundle(['description' => $this->randomMachineName(32)]);

    // Test if media/add displays two media bundle options.
    $this->drupalGet('media/add');

    // Checks for the first media bundle.
    $assert_session->pageTextContains($first_media_bundle->label());
    $assert_session->pageTextContains($first_media_bundle->description);
    // Checks for the second media bundle.
    $assert_session->pageTextContains($second_media_bundle->label());
    $assert_session->pageTextContains($second_media_bundle->description);

    // Continue testing media bundle filter.
    $first_media_item = Media::create(['bundle' => $first_media_bundle->id()]);
    $first_media_item->save();
    $second_media_item = Media::create(['bundle' => $second_media_bundle->id()]);
    $second_media_item->save();

    // Go to media item list.
    $this->drupalGet('admin/content/media');
    $assert_session->statusCodeEquals(200);
    $assert_session->linkExists('Add media');

    // Assert that all available media items are in the list.
    $assert_session->pageTextContains($first_media_item->label());
    $assert_session->pageTextContains($first_media_bundle->label());
    $assert_session->pageTextContains($second_media_item->label());
    $assert_session->pageTextContains($second_media_bundle->label());

    // Filter for each bundle and assert that the list has been updated.
    $this->drupalGet('admin/content/media', ['query' => ['provider' => $first_media_bundle->id()]]);
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains($first_media_item->label());
    $assert_session->pageTextNotContains($second_media_item->label());

    // Filter all and check for all items again.
    $this->drupalGet('admin/content/media', ['query' => ['provider' => 'All']]);
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains($first_media_item->label());
    $assert_session->pageTextContains($first_media_bundle->label());
    $assert_session->pageTextContains($second_media_item->label());
    $assert_session->pageTextContains($second_media_bundle->label());

  }

}
