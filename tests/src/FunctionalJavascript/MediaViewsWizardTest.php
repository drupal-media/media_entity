<?php

namespace Drupal\Tests\media_entity\FunctionalJavascript;

use Drupal\views\Views;

/**
 * Tests the media entity type integration into the wizard.
 *
 * @group media_entity
 *
 * @see \Drupal\media_entity\Plugin\views\wizard\Media
 * @see \Drupal\media_entity\Plugin\views\wizard\MediaRevision
 */
class MediaViewsWizardTest extends MediaEntityJavascriptTestBase {

  /**
   * Tests adding a view of media.
   */
  public function testMediaWizard() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $view_id = strtolower($this->randomMachineName(16));
    $this->drupalGet('admin/structure/views/add');
    $page->fillField('label', $view_id);
    $session->wait(2000);
    $page->selectFieldOption('show[wizard_key]', 'media');
    $assert_session->assertWaitOnAjaxRequest();
    $page->checkField('page[create]');
    $page->fillField('page[path]', $this->randomMachineName(16));
    $page->pressButton('Save and edit');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertEquals($session->getCurrentUrl(), $this->baseUrl . '/admin/structure/views/view/' . $view_id);

    $view = Views::getView($view_id);
    $view->initHandlers();
    $row = $view->display_handler->getOption('row');
    $this->assertEquals($row['type'], 'fields');
    // Check for the default filters.
    $this->assertEquals($view->filter['status']->table, 'media_field_data');
    $this->assertEquals($view->filter['status']->field, 'status');
    $this->assertTrue($view->filter['status']->value);
    // Check for the default fields.
    $this->assertEquals($view->field['name']->table, 'media_field_data');
    $this->assertEquals($view->field['name']->field, 'name');

  }

  /**
   * Tests adding a view of media revisions.
   */
  public function testMediaRevisionWizard() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $view_id = strtolower($this->randomMachineName(16));
    $this->drupalGet('admin/structure/views/add');
    $page->fillField('label', $view_id);
    $session->wait(2000);
    $page->selectFieldOption('show[wizard_key]', 'media_revision');
    $assert_session->assertWaitOnAjaxRequest();
    $page->checkField('page[create]');
    $page->fillField('page[path]', $this->randomMachineName(16));
    $page->pressButton('Save and edit');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertEquals($session->getCurrentUrl(), $this->baseUrl . '/admin/structure/views/view/' . $view_id);

    $view = Views::getView($view_id);
    $view->initHandlers();
    $row = $view->display_handler->getOption('row');
    $this->assertEquals($row['type'], 'fields');

    // Check for the default filters.
    $this->assertEquals($view->filter['status']->table, 'media_field_revision');
    $this->assertEquals($view->filter['status']->field, 'status');
    $this->assertTrue($view->filter['status']->value);

    // Check for the default fields.
    $this->assertEquals($view->field['name']->table, 'media_field_revision');
    $this->assertEquals($view->field['name']->field, 'name');
    $this->assertEquals($view->field['changed']->table, 'media_field_revision');
    $this->assertEquals($view->field['changed']->field, 'changed');

  }

}
