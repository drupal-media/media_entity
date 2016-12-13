<?php

namespace Drupal\Tests\media_entity\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity\Entity\Media;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * Tests creation of Media Bundles and Media Entities.
 *
 * @group media_entity
 */
class BasicCreationTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'media_entity',
    'entity',
    'image',
    'user',
    'field',
    'system',
    'file',
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

    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installConfig(['field', 'system', 'image', 'file']);

    // Create a test bundle.
    $id = strtolower($this->randomMachineName());
    $this->testBundle = MediaBundle::create([
      'id' => $id,
      'label' => $id,
      'type' => 'generic',
      'type_configuration' => [],
      'field_map' => [],
      'new_revision' => FALSE,
    ]);
    $this->testBundle->save();

  }

  /**
   * Tests creating a media bundle programmatically.
   */
  public function testMediaBundleCreation() {
    /** @var \Drupal\media_entity\MediaBundleInterface $bundle_storage */
    $bundle_storage = $this->container->get('entity_type.manager')->getStorage('media_bundle');

    $bundle_exists = (bool) $bundle_storage->load($this->testBundle->id());
    $this->assertTrue($bundle_exists, 'The new media bundle has not been correctly created in the database.');

    // Test default bundle created from default configuration.
    $this->container->get('module_installer')->install(['media_entity_test_bundle']);
    $test_bundle = $bundle_storage->load('test');
    $this->assertTrue((bool) $test_bundle, 'The media bundle from default configuration has not been created in the database.');
    $this->assertEquals($test_bundle->get('label'), 'Test bundle', 'Could not assure the correct bundle label.');
    $this->assertEquals($test_bundle->get('description'), 'Test bundle.', 'Could not assure the correct bundle description.');
    $this->assertEquals($test_bundle->get('type'), 'generic', 'Could not assure the correct bundle plugin type.');
    $this->assertEquals($test_bundle->get('type_configuration'), ['source_field' => 'field_media_generic_1'], 'Could not assure the correct plugin configuration.');
    $this->assertEquals($test_bundle->get('field_map'), [], 'Could not assure the correct field map.');
  }

  /**
   * Tests creating a media entity programmatically.
   */
  public function testMediaEntityCreation() {
    $media = Media::create([
      'bundle' => $this->testBundle->id(),
      'name' => 'Unnamed',
    ]);
    $media->save();

    $media_not_exist = (bool) Media::load(rand(1000, 9999));
    $this->assertFalse($media_not_exist, 'Failed asserting a non-existent media.');

    $media_exists = (bool) Media::load($media->id());
    $this->assertTrue($media_exists, 'The new media entity has not been created in the database.');
    $this->assertEquals($media->bundle(), $this->testBundle->id(), 'The media was not created with the correct bundle.');
    $this->assertEquals($media->label(), 'Unnamed', 'The media was not created with the correct name.');

    // Test the creation of a media without user-defined label and check if a
    // default name is provided.
    $media = Media::create([
      'bundle' => $this->testBundle->id(),
    ]);
    $media->save();
    $expected_name = 'media' . ':' . $this->testBundle->id() . ':' . $media->uuid();
    $this->assertEquals($media->bundle(), $this->testBundle->id(), 'The media was not created with correct bundle.');
    $this->assertEquals($media->label(), $expected_name, 'The media was not created with a default name.');
  }

  /**
   * Tests creating and updating bundles programmatically.
   */
  public function testProgrammaticBundleManipulation() {
    // Creating a bundle programmatically without specifying a source field
    // should create one automagically.
    /** @var FieldConfig $field */
    $field = $this->testBundle->getType()->getSourceField($this->testBundle);
    $this->assertInstanceOf(FieldConfig::class, $field);
    $this->assertEquals('field_media_generic', $field->getName());
    $this->assertFalse($field->isNew());

    // Saving with a non-existent source field should create it.
    $this->testBundle->setTypeConfiguration([
      'source_field' => 'field_magick',
    ]);
    $this->testBundle->save();
    $field = $this->testBundle->getType()->getSourceField($this->testBundle);
    $this->assertInstanceOf(FieldConfig::class, $field);
    $this->assertEquals('field_magick', $field->getName());
    $this->assertFalse($field->isNew());

    // Trying to save without a source field should create a new, de-duped one.
    $this->testBundle->setTypeConfiguration([]);
    $this->testBundle->save();
    $field = $this->testBundle->getType()->getSourceField($this->testBundle);
    $this->assertInstanceOf(FieldConfig::class, $field);
    $this->assertEquals('field_media_generic_1', $field->getName());
    $this->assertFalse($field->isNew());

    // Trying to reuse an existing field should, well, reuse the existing field.
    $this->testBundle->setTypeConfiguration([
      'source_field' => 'field_magick',
    ]);
    $this->testBundle->save();
    $field = $this->testBundle->getType()->getSourceField($this->testBundle);
    $this->assertInstanceOf(FieldConfig::class, $field);
    $this->assertEquals('field_magick', $field->getName());
    $this->assertFalse($field->isNew());
    // No new de-duped fields should have been created.
    $duplicates = FieldConfig::loadMultiple([
      'media.' . $this->testBundle->id() . '.field_magick_1',
      'media.' . $this->testBundle->id() . '.field_media_generic_2',
    ]);
    $this->assertEmpty($duplicates);
  }

}
