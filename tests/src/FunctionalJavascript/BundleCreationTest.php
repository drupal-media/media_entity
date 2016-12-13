<?php

namespace Drupal\Tests\media_entity\FunctionalJavascript;

/**
 * Tests the media bundle creation.
 *
 * @group media_entity
 */
class BundleCreationTest extends MediaEntityJavascriptTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'media_entity_test_type',
  ];

  /**
   * Tests the media bundle creation form.
   */
  public function testBundleCreationFormWithDefaultField() {
    $label = 'Bundle with Default Field';
    $bundleMachineName = str_replace(' ', '_', strtolower($label));

    $this->drupalGet('admin/structure/media/add');
    $page = $this->getSession()->getPage();

    // Fill in a label to the bundle.
    $page->fillField('label', $label);
    // Wait for machine name generation. Default: waitUntilVisible(), does not
    // work properly.
    $this->getSession()
      ->wait(5000, "jQuery('.machine-name-value').text() === '{$bundleMachineName}'");

    // Select our test bundle type.
    $this->assertSession()->fieldExists('Type provider');
    $this->assertSession()->optionExists('Type provider', 'test_type');
    $page->selectFieldOption('Type provider', 'test_type');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->pressButton('Save media bundle');

    // Check whether the source field was correctly created.
    $this->drupalGet("admin/structure/media/manage/{$bundleMachineName}/fields");

    // Check 2nd column of first data row, to be machine name for field name.
    $this->assertSession()
      ->elementContains('xpath', '(//table[@id="field-overview"]//tr)[2]//td[2]', 'field_media_test_type');
    // Check 3rd column of first data row, to be correct field type.
    $this->assertSession()
      ->elementTextContains('xpath', '(//table[@id="field-overview"]//tr)[2]//td[3]', 'Text (plain)');

    // Check that the source field is correctly assigned to media bundle.
    $this->drupalGet("admin/structure/media/manage/{$bundleMachineName}");

    $this->assertSession()
      ->fieldValueEquals('type_configuration[test_type][source_field]', 'field_media_test_type');
  }

  /**
   * Test creation of media bundle, reusing an existing source field.
   */
  public function testBundleCreationReuseSourceField() {
    // Create a new bundle, which should create a new field we can reuse.
    $this->drupalGet('/admin/structure/media/add');
    $page = $this->getSession()->getPage();
    $page->fillField('label', 'Pastafazoul');
    $this->getSession()
      ->wait(5000, "jQuery('.machine-name-value').text() === 'pastafazoul'");
    $page->selectFieldOption('Type provider', 'generic');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('Save media bundle');

    $label = 'Bundle reusing Default Field';
    $bundleMachineName = str_replace(' ', '_', strtolower($label));

    $this->drupalGet('admin/structure/media/add');
    $page = $this->getSession()->getPage();

    // Fill in a label to the bundle.
    $page->fillField('label', $label);

    // Wait for machine name generation. Default: waitUntilVisible(), does not
    // work properly.
    $this->getSession()
      ->wait(5000, "jQuery('.machine-name-value').text() === '{$bundleMachineName}'");

    // Select our test bundle type.
    $this->assertSession()->fieldExists('Type provider');
    $this->assertSession()->optionExists('Type provider', 'test_type');
    $page->selectFieldOption('Type provider', 'test_type');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Select the existing field for re-use.
    $page->selectFieldOption('type_configuration[test_type][source_field]', 'field_media_generic');
    $page->pressButton('Save media bundle');

    // Check that there are not fields created.
    $this->drupalGet("admin/structure/media/manage/{$bundleMachineName}/fields");
    // The reused field should be present...
    $this->assertSession()->pageTextContains('field_media_generic');
    // ...not a new, unique one.
    $this->assertSession()->pageTextNotContains('field_media_generic_1');
  }

}
