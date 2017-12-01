<?php

namespace Drupal\media_entity;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class CliService.
 *
 * @internal
 */
class CliService {

  use StringTranslationTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * CliService constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Verify if the upgrade to Media in core is possible.
   *
   * @return array
   *   An associative array with two keys:
   *   - errors: An array of error messages, one per requirement that failed.
   *     An empty array here means that the site can proceed with the upgrade
   *     path.
   *   - passes: An array of success messages, one per requirement verified.
   */
  public function validateDbUpdateRequirements() {
    $checks = [
      'errors' => [],
      'passes' => [],
    ];

    module_load_install('media_entity');

    // This update only makes sense with core >= 8.4.x.
    $version = explode('.', \Drupal::VERSION);
    if ($version[1] < 4) {
      $checks['errors'][] = $this->t('The Media Entity 2.x upgrade path only works with Drupal core >= 8.4.x');
    }
    else {
      $checks['passes'][] = $this->t('Drupal core is the correct version (>= 8.4.0). [@version detected]', ['@version' => \Drupal::VERSION]);
    }

    // This update can't proceed if there already is an enabled module called
    // "media".
    if ($this->moduleHandler->moduleExists('media')) {
      $checks['errors'][] = $this->t('In order to run the Media Entity 2.x upgrade, please uninstall and remove from the codebase the contributed "Media" module.');
    }
    else {
      $checks['passes'][] = $this->t('The contributed "Media" module is not installed.');
    }

    // Prevent the updates from running if there is a type-provider that is
    // still on the 1.x branch.
    $incompatible_modules = _media_entity_get_incompatible_modules();
    if (!empty($incompatible_modules)) {
      $provider_modules = !empty($incompatible_modules['providers']) ? implode(", ", $incompatible_modules['providers']) : '';
      $additional_msg_providers = !empty($provider_modules) ? ' ' . $this->t('The following modules provide source plugins and need to be upgraded: @provider_modules.', [
        '@provider_modules' => $provider_modules,
      ]) : '';
      $dependent_modules = !empty($incompatible_modules['modules']) ? implode(", ", $incompatible_modules['modules']) : '';
      $additional_msg_dependent = !empty($dependent_modules) ? ' ' . $this->t('The following modules depend on media entity and need to be either upgraded or uninstalled: @dependent_modules.', [
        '@dependent_modules' => $dependent_modules,
      ]) : '';
      $checks['errors'][] = $this->t('Before continuing, please make sure all modules that provide plugins for Media Entity (or depend on it) have their code updated to their respective 2.x branches. Note that you will probably need to revert to the 1.x branch of the Media Entity module if you want to uninstall existing plugin modules.') . $additional_msg_providers . $additional_msg_dependent;
    }
    else {
      $checks['passes'][] = $this->t('All provider plugins and modules depending on media_entity are up-to-date.');
    }

    $module_data = system_rebuild_module_data();

    // Generic media types should now live in the contrib Media Entity Generic
    // module, which should be available at the codebase.
    $generic_types = _media_entity_get_bundles_by_plugin('generic');
    if ($generic_types) {
      if (!isset($module_data['media_entity_generic'])) {
        $checks['errors'][] = $this->t('One or more of your existing media types are using the Generic source, which has been moved into a separate "Media Entity Generic" module. You need to download this module to your codebase before continuing.');
      }
      else {
        $checks['passes'][] = $this->t('The "Media Entity Generic" module is available.');
      }
    }

    // Actions now live in the contributed media_entity_actions, until generic
    // entity actions are part of Drupal core (2916740).
    if (!isset($module_data['media_entity_actions']) && !file_exists(\Drupal::root() . '/core/modules/media/src/Plugin/Action/PublishMedia.php')) {
      $checks['errors'][] = $this->t('Media Actions (for example, for bulk operations) have been moved into a separate "Media Entity Actions" module. You need to download this module to your codebase before continuing.');
    }
    else {
      $checks['passes'][] = $this->t('The "Media Entity Actions" module is available.');
    }

    return $checks;
  }

}
