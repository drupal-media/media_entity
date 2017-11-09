<?php

namespace Drupal\media_entity\Commands;

use Drush\Commands\DrushCommands as DrushCommandsBase;
use Drupal\media_entity\CliService;

/**
 * Add commands for Drush 9.
 */
class DrushCommands extends DrushCommandsBase {

  /**
   * The cli service.
   *
   * @var \Drupal\media_entity\CliService
   */
  protected $cliService;

  /**
   * MediaEntityCommands constructor.
   *
   * @param \Drupal\media_entity\CliService $cli_service
   *   The CLI service which allows interoperability.
   */
  public function __construct(CliService $cli_service) {
    $this->cliService = $cli_service;
  }

  /**
   * Check upgrade requirements for Media Entity into Media in core.
   *
   * @command media_entity:check-upgrade
   * @usage drush mecu
   *   Checks upgrade requirements for Media Entity while upgrading to Media in
   *   core.
   * @aliases mecu,media-entity-check-upgrade
   */
  public function mediaEntityCheckUpgrade() {
    drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
    $logger = $this->logger();
    // This command is useless if the DB updates have already been run.
    if (drupal_get_installed_schema_version('media_entity') >= 8201) {
      $logger(dt('Your site has already run the media_entity DB updates. If you believe this is not correct, you should consider rolling back your database to a previous backup and try again.'));
      return;
    }

    $checks = $this->cliService->validateDbUpdateRequirements();

    if (empty($checks['errors'])) {
      $logger->success(sprintf("\033[1;32;40m\033[1m%s\033[0m", '✓') . ' ' . dt('All upgrade requirements are met and you can proceed with the DB updates.'));
    }
    else {
      $logger->error(sprintf("\033[31;40m\033[1m%s\033[0m", '✗') . ' ' . dt('Your site did not pass all upgrade checks. You can find more information in the error messages below.'));
    }
    foreach ($checks['passes'] as $pass_msg) {
      $logger->success($pass_msg);
    }
    foreach ($checks['errors'] as $error_msg) {
      $logger->error($error_msg);
    }
  }

}
