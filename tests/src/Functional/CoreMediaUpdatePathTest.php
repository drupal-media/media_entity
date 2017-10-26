<?php

namespace Drupal\Tests\media_entity\Functional;

use Drupal\Core\Config\Entity\Query\QueryFactory;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * @group media_entity
 */
class CoreMediaUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/drupal-8.4.0-media-entity.php.gz',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // All this can be removed when #2877383 lands.
    $this->config('system.action.media_delete_action')->delete();
    $this->config('system.action.media_publish_action')->delete();
    $this->config('system.action.media_save_action')->delete();
    $this->config('system.action.media_unpublish_action')->delete();

    $this->config('views.view.media')
      ->clear('display.default.display_options.fields.media_bulk_form')
      ->save();
  }

  public function testUpdatePath() {
    $icon_base_uri = $this->config('media_entity.settings')->get('icon_base');

    $this->runUpdates();
    $assert = $this->assertSession();

    // As with all translatable, versionable content entity types, media
    // entities should have the revision_translation_affected base field.
    // This may have been created during the update path by system_update_8402,
    // so we should check for it here.
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager */
    $field_manager = $this->container->get('entity_field.manager');
    $this->assertArrayHasKey('revision_translation_affected', $field_manager->getBaseFieldDefinitions('media'));
    $field_manager->clearCachedFieldDefinitions();

    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/admin/modules');
    $assert->checkboxNotChecked('modules[media_entity_document][enable]');
    $assert->checkboxNotChecked('modules[media_entity_image][enable]');
    $assert->checkboxNotChecked('modules[media_entity][enable]');
    $assert->checkboxChecked('modules[media_entity_generic][enable]');
    // Media is not currently displayed on the Modules page.
    $this->assertArrayHasKey('media', $this->config('core.extension')->get('module'));

    $this->drupalGet('/admin/structure/media/manage/file');
    $assert->statusCodeEquals(200);
    $assert->fieldValueEquals('source', 'file');
    $assert->pageTextContains('File field is used to store the essential information');

    $this->drupalGet('/admin/structure/media/manage/image');
    $assert->statusCodeEquals(200);
    $assert->fieldValueEquals('source', 'image');
    $assert->pageTextContains('Image field is used to store the essential information');

    $this->drupalGet('/admin/structure/media/manage/generic');
    $assert->statusCodeEquals(200);
    $assert->fieldValueEquals('source', 'generic');
    $assert->pageTextContains('Generic media field is used to store the essential information');

    $this->assertFrontPageMedia('Image 3', 'main img');
    $this->assertFrontPageMedia('Generic 1', 'main img[src *= "/media-icons/generic/generic.png"]');
    $this->assertFrontPageMedia('File 2', 'main img[src *= "/media-icons/generic/document.png"]');
    $this->assertFrontPageMedia('File 3', 'main img[src *= "/media-icons/generic/document.png"]');
    $this->assertFrontPageMedia('Image 1', 'main img');
    $this->assertFrontPageMedia('Generic 3', 'main img[src *= "/media-icons/generic/generic.png"]');

    // Assert that Media Entity's config is migrated.
    $this->assertTrue($this->config('media_entity.settings')->isNew());
    $this->assertEquals($icon_base_uri, $this->config('media.settings')->get('icon_base_uri'));
    $this->assertEmpty(
      $this->container->get('config.factory')->listAll('media_entity.bundle')
    );

    $this->activateModule();
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->container
      ->get('entity_type.manager')
      ->getStorage('media_type');

    foreach (['file', 'image', 'generic'] as $type) {
      $config = $this->config("media.type.$type");
      $this->assertFalse($config->isNew());
      $this->assertNull($config->get('type'));
      $this->assertNull($config->get('type_configuration'));
      $this->assertInternalType('string', $config->get('source'));
      $this->assertInternalType('array', $config->get('source_configuration'));
      $this->assertInternalType('string', $config->get('source_configuration.source_field'));

      // Ensure that the media type can be queried by UUID.
      $uuid = $config->get('uuid');
      $this->assertNotEmpty($uuid);
      $result = $storage->getQuery()->condition('uuid', $uuid)->execute();
      $this->assertEquals($result[$type], $type);
    }

    // The UUID map for legacy media bundles should be cleared out.
    $old_uuid_map = $this->container
      ->get('keyvalue')
      ->get(QueryFactory::CONFIG_LOOKUP_PREFIX . 'media_bundle')
      ->getAll();
    $this->assertEmpty($old_uuid_map);
  }

  protected function assertFrontPageMedia($link, $assert_selectors) {
    $this->drupalGet('<front>');
    $this->clickLink($link);

    $assert = $this->assertSession();
    foreach ((array) $assert_selectors as $selector) {
      $assert->elementExists('css', $selector);
    }
  }

  /**
   * Activates the Media module in PHPUnit's memory space.
   */
  protected function activateModule() {
    $this->container
      ->get('module_handler')
      ->addModule('media', 'core/modules/media');

    /** @var \ArrayObject $namespaces */
    $namespaces = $this->container->get('container.namespaces');
    $namespaces['Drupal\\media'] = 'core/modules/media/src';

    $this->container
      ->get('entity_type.manager')
      ->clearCachedDefinitions();
  }

}
