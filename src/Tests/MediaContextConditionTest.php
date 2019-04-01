<?php

namespace Drupal\media_entity\Tests;

use Drupal\Component\Utility\Xss;
use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media context & media bundle condition work correctly.
 *
 * @group media_entity
 */
class MediaContextConditionTest extends WebTestBase {

  use MediaTestTrait;

  /**
   * The test user.
   *
   * @var \Drupal\User\UserInterface
   */
  protected $adminUser;

  /**
   * A non-admin test user.
   *
   * @var \Drupal\User\UserInterface
   */
  protected $nonAdminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['media_entity', 'media_entity_test_context', 'field_ui', 'views_ui', 'node', 'block', 'entity'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->adminUser = $this->drupalCreateUser([
      'administer media',
      'administer media fields',
      'administer media form display',
      'administer media display',
      'administer media bundles',
      // Media entity permissions.
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      'access media overview',
      // Other permissions.
      'access administration pages',
      'administer blocks',
      'administer views',
      'access content overview',
      'view all revisions',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests the behavior of media context-aware blocks.
   */
  public function testMediaContextAwareBlocks() {
    $bundle = $this->createMediaBundle();
    $media = $this->createMediaItem($bundle);
    $expected_text = 'Media ID: ' . $media['id'];

    $this->drupalGet('');
    $this->assertNoText('Test media context aware block');
    $this->assertNoRaw($expected_text);

    $block_url = 'admin/structure/block/add/test_media_context_block/classy';
    $arguments = array(
      ':title' => 'Test media context aware block',
      ':category' => 'Media entity test context',
      ':href' => $block_url,
    );
    $pattern = '//tr[.//td/div[text()=:title] and .//td[text()=:category] and .//td//a[contains(@href, :href)]]';

    $this->drupalGet('admin/structure/block');
    $this->clickLinkPartialName('Place block');
    $elements = $this->xpath($pattern, $arguments);
    $this->assertTrue(!empty($elements), 'The media context-aware test block appears.');
    $definition = \Drupal::service('plugin.manager.block')->getDefinition('test_media_context_block');
    $this->assertTrue(!empty($definition), 'The media context-aware test block definition exists.');
    $edit = [
      'region' => 'content',
      'visibility[media_bundle][bundles][' . $bundle['id'] . ']' => TRUE
    ];
    $this->drupalPostForm($block_url, $edit, 'Save block');

    $this->drupalGet('');
    $this->assertNoText('Test media context aware block', 'Block not found because condition not met (bundle: ' . $bundle['id'] . ').');

    $this->drupalGet('media/' . $media['id']);
    $this->assertText('Test media context aware block');
    $this->assertRaw($expected_text, 'Block found, with media context (condition met).');

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

}
