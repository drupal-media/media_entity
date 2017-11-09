<?php

/**
 * @file
 * Drush integration for media_entity.
 */

use Drush\Log\LogLevel;

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

  // Normally users should remove Media Entity from the codebase after the
  // upgrade. However, if they don't do so, this function will be called on
  // subsequent DB upgrades, even if ME is not enabled. Don't proceed on those
  // circumstances.
  if (!\Drupal::moduleHandler()->moduleExists('media_entity')) {
    return TRUE;
  }

  $checks = \Drupal::service('media_entity.cli')->validateDbUpdateRequirements();
  if (empty($checks['errors'])) {
    return TRUE;
  }
  else {
    foreach ($checks['errors'] as $error_msg) {
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

  $items['media-entity-check-upgrade'] = [
    'description' => 'Check upgrade requirements for Media Entity into Media in core.',
    'aliases' => ['mecu'],
    'core' => ['8+'],
    'examples' => [
      "drush mecu" => "Checks upgrade requirements for Media Entity while upgrading to Media in core.",
    ],
  ];

  return $items;
}

/**
 * Callback for drush commmand "media-entity-check-upgrade" (mecu).
 */
function drush_media_entity_check_upgrade() {
  // This command is useless if the DB updates have already been run.
  if (drupal_get_installed_schema_version('media_entity') >= 8004) {
    drush_log(dt('Your site has already run the media_entity DB updates. If you believe this is not correct, you should consider rolling back your database to a previous backup and try again.'), LogLevel::WARNING);
    return;
  }

  drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
  $checks = \Drupal::service('media_entity.cli')->validateDbUpdateRequirements();

  if (empty($checks['errors'])) {
    drush_log(sprintf("\033[1;32;40m\033[1m%s\033[0m", '✓') . ' ' . dt('SUCCESS: All upgrade requirements are met and you can proceed with the DB updates.'), LogLevel::OK);
  }
  else {
    drush_log(sprintf("\033[31;40m\033[1m%s\033[0m", '✗') . ' ' . dt('ERROR: Your site did not pass all upgrade checks. You can find more information in the error messages below.'), LogLevel::ERROR);
  }
  foreach ($checks['passes'] as $pass_msg) {
    drush_log($pass_msg, LogLevel::OK);
  }
  foreach ($checks['errors'] as $error_msg) {
    drush_log($error_msg, LogLevel::ERROR);
  }
}
