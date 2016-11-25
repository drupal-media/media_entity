<?php

namespace Drupal\Tests\media_entity\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * Base class for Media Entity Javascript functional tests.
 *
 * @package Drupal\Tests\media_entity\FunctionalJavascript
 */
abstract class MediaEntityJavascriptTestBase extends JavascriptTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'node',
    'field_ui',
    'views_ui',
    'entity',
    'media_entity',
  ];

  /**
   * Permissions for the admin user that will be logged-in for test.
   *
   * @var array
   */
  protected static $adminUserPermissions = [
    // Media entity permissions.
    'administer media',
    'administer media fields',
    'administer media form display',
    'administer media display',
    'administer media bundles',
    'view media',
    'create media',
    'update media',
    'update any media',
    'delete media',
    'delete any media',
    'access media overview',
    // Other permissions.
    'administer views',
    'access content overview',
    'view all revisions',
    'administer content types',
    'administer node fields',
    'administer node form display',
    'bypass node access',
  ];

  /**
   * Permissions for the non-admin user that will be logged-in for test.
   *
   * @var array
   */
  protected static $nonAdminUserPermissions = [
    // Media entity permissions.
    'view media',
    'create media',
    'update media',
    'update any media',
    'delete media',
    'delete any media',
    'access media overview',
    // Other permissions.
    'administer views',
    'access content overview',
  ];

  /**
   * An admin test user account.
   *
   * @var \Drupal\Core\Session\AccountInterface;
   */
  protected $adminUser;

  /**
   * A non-admin test user account.
   *
   * @var \Drupal\Core\Session\AccountInterface;
   */
  protected $nonAdminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Have two users ready to be used in tests.
    $this->adminUser = $this->drupalCreateUser(static::$adminUserPermissions);
    $this->nonAdminUser = $this->drupalCreateUser(static::$nonAdminUserPermissions);
    // Start off logged in as admin.
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Creates a media bundle.
   *
   * @param array $values
   *   The media bundle values.
   * @param string $type_name
   *   (optional) The media type provider plugin that is responsible for
   *   additional logic related to this media).
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Returns newly created media bundle.
   */
  protected function drupalCreateMediaBundle(array $values = [], $type_name = 'generic') {
    if (!isset($values['bundle'])) {
      $id = strtolower($this->randomMachineName());
    }
    else {
      $id = $values['bundle'];
    }
    $values += [
      'id' => $id,
      'label' => $id,
      'type' => $type_name,
      'type_configuration' => [],
      'field_map' => [],
      'new_revision' => FALSE,
    ];

    $bundle = MediaBundle::create($values);
    $status = $bundle->save();

    $this->assertEquals($status, SAVED_NEW, 'Could not create a media bundle of type ' . $type_name . '.');

    return $bundle;
  }

  /**
   * Waits for jQuery to become ready and animations to complete.
   */
  protected function waitForAjaxToFinish() {
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Waits and asserts that a given element is visible.
   *
   * @param string $selector
   *   The CSS selector.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 1000.
   * @param string $message
   *   (Optional) Message to pass to assertJsCondition().
   */
  protected function waitUntilVisible($selector, $timeout = 1000, $message = '') {
    $condition = "jQuery('" . $selector . ":visible').length > 0";
    $this->assertJsCondition($condition, $timeout, $message);
  }

  /**
   * Debugger method to save additional HTML output.
   *
   * The base class will only save browser output when accessing page using
   * ::drupalGet and providing a printer class to PHPUnit. This method
   * is intended for developers to help debug browser test failures and capture
   * more verbose output.
   */
  protected function saveHtmlOutput() {
    $out = $this->getSession()->getPage()->getContent();
    // Ensure that any changes to variables in the other thread are picked up.
    $this->refreshVariables();
    if ($this->htmlOutputEnabled) {
      $html_output = '<hr />Ending URL: ' . $this->getSession()->getCurrentUrl();
      $html_output .= '<hr />' . $out;
      $html_output .= $this->getHtmlOutputHeaders();
      $this->htmlOutput($html_output);
    }
  }

}
