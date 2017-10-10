<?php

/**
 * @file
 * Drush integration for media_entity.
 */

use \Drush\Log\LogLevel;

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_media_entity_updatedb_validate() {
  // This hook exists because when running DB updates using drush,
  // hook_requirements() is not enforced (see
  // https://github.com/drush-ops/drush/pull/2708 for more info on that).
  // Here we just re-evaluate all the checks from hook_requirements() and abort
  // the "updatedb/updb" command by returning FALSE to this hook if any of them
  // is not met.
  drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
  module_load_include('install', 'media_entity');

  $errors = [];

  // This update only makes sense with core >= 8.4.x.
  $version = explode('.', \Drupal::VERSION);
  if ($version[1] < 4) {
    $errors[] = dt('The Media Entity 2.x upgrade path only works with Drupal core >= 8.4.x');
  }

  // This update can't proceed if there already is an enabled module called
  // "media".
  if (\Drupal::moduleHandler()->moduleExists('media')) {
    $errors[] = dt('In order to run the Media Entity 2.x upgrade, please uninstall and remove from the codebase the contributed "Media" module.');
  }

  // Prevent the updates from running if there is a type-provider that is
  // still on the 1.x branch.
  $incompatible_modules = _media_entity_get_incompatible_modules();
  if (!empty($incompatible_modules)) {
    $provider_modules = !empty($incompatible_modules['providers']) ? implode(", ", $incompatible_modules['providers']) : '';
    $additional_msg_providers = !empty($provider_modules) ? ' ' . t('The following modules provide source plugins and need to be upgraded: @provider_modules.', [
      '@provider_modules' => $provider_modules,
    ]) : '';
    $dependent_modules = !empty($incompatible_modules['modules']) ? implode(", ", $incompatible_modules['modules']) : '';
    $additional_msg_dependent = !empty($dependent_modules) ? ' ' . t('The following modules depend on media entity and need to be either upgraded or uninstalled: @dependent_modules.', [
      '@dependent_modules' => $dependent_modules,
    ]) : '';
    $errors[] = dt('In order to run the Media Entity updates, please make sure all modules that provide plugins for Media Entity (or depend on it) have their code updated to their respective 2.x branches. Note that you will probably need to revert to the 1.x branch of the Media Entity module if you want to uninstall existing plugin modules.') . $additional_msg_providers . $additional_msg_dependent;
  }

  if (empty($errors)) {
    return TRUE;
  }
  else {
    foreach ($errors as $error_msg) {
      // We can't use drush_log() inside this hook.
      drush_print($error_msg);
    }
    return FALSE;
  }
}

/**
 * Implements hook_drush_command().
 */
function media_entity_drush_command() {

  $items['media-entity-check-upgrade'] = array(
    'description' => 'Check upgrade requirements for Media Entity into Media in core.',
    'aliases' => ['mecu'],
    'core' => ['8+'],
    'examples' => [
      "drush mecu" => "Checks upgrade requirements for Media Entity whil upgrading to Media in core.",
    ],
  );

  return $items;
}

/**
 * Callback for drush commmand "media-entity-check-upgrade" (mecu).
 */
function drush_media_entity_check_upgrade() {
  drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
  // This command is useless if the DB updates have already been run.
  if (drupal_get_installed_schema_version('media_entity') >= 8004) {
    drush_log(dt('Your site has already run the media_entity DB updates. If you believe this is not correct, you should consider rolling back your database to a previous backup and try again.'), LogLevel::WARNING);
    return;
  }
  module_load_include('install', 'media_entity');
  $errors = [];
  $passes = [];

  // Check that core version is OK.
  $version = explode('.', \Drupal::VERSION);
  if ($version[1] < 4) {
    $errors[] = dt('The Media Entity 2.x upgrade path only works with Drupal core >= 8.4.x');
  }
  else {
    $passes[] = dt('Drupal core is the correct version (>= 8.4.0). [@version detected]', ['@version' => \Drupal::VERSION]);
  }

  // Check that contrib "Media" is not enabled in the current site.
  if (\Drupal::moduleHandler()->moduleExists('media')) {
    $errors[] = dt('In order to run the Media Entity 2.x upgrade, please uninstall and remove from the codebase the contributed "Media" module.');
  }
  else {
    $passes[] = dt('Successfully checked that the "Media" module is not installed.');
  }

  // If there are any generic media bundles, ensure that media_entity_generic
  // is available.
  $generic_types = _media_entity_get_bundles_by_plugin('generic');
  if ($generic_types) {
    $module_data = system_rebuild_module_data();
    if (isset($module_data['media_entity_generic'])) {
      $passes[] = dt('Successfully located the media_entity_generic module.');
    }
    else {
      $errors[] = dt('One or more of your existing media types use the Generic source, which has been moved into a separate Media Entity Generic module. Please ensure this module is available before continuing.');
    }
  }

  // Check dependent modules and provider plugins.
  $incompatible_modules = _media_entity_get_incompatible_modules();
  if (!empty($incompatible_modules)) {
    $provider_modules = !empty($incompatible_modules['providers']) ? implode(", ", $incompatible_modules['providers']) : '';
    $additional_msg_providers = !empty($provider_modules) ? ' ' . t('The following modules provide source plugins and need to be upgraded: @provider_modules.', [
      '@provider_modules' => $provider_modules,
    ]) : '';
    $dependent_modules = !empty($incompatible_modules['modules']) ? implode(", ", $incompatible_modules['modules']) : '';
    $additional_msg_dependent = !empty($dependent_modules) ? ' ' . t('The following modules depend on media entity and need to be either upgraded or uninstalled: @dependent_modules.', [
      '@dependent_modules' => $dependent_modules,
    ]) : '';
    $errors[] = dt('In order to run the Media Entity updates, please make sure all modules that provide plugins for Media Entity (or depend on it) have their code updated to their respective 2.x branches. Note that you will probably need to revert to the 1.x branch of the Media Entity module if you want to uninstall existing plugin modules.') . $additional_msg_providers . $additional_msg_dependent;
  }
  else {
    $passes[] = dt('Successfully checked that all provider plugins and modules depending on media_entity were updated.');
  }

  if (empty($errors)) {
    drush_log(sprintf("\033[1;32;40m\033[1m%s\033[0m", '✓') . ' ' . dt('SUCCESS: All upgrade requirements are met and you can proceed with the DB updates.'), LogLevel::OK);
  }
  else {
    drush_log(sprintf("\033[31;40m\033[1m%s\033[0m", '✗') . ' ' . dt('ERROR: Your site did not pass all upgrade checks. You can find more information in the error messages below.'), LogLevel::ERROR);
  }
  foreach ($passes as $pass_msg) {
    drush_log($pass_msg, LogLevel::OK);
  }
  foreach ($errors as $error_msg) {
    drush_log($error_msg, LogLevel::ERROR);
  }
}
