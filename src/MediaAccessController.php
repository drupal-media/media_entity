<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaAccessController;
 */

namespace Drupal\media_entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;


/**
 * Defines an access controller for the media entity.
 */
class MediaAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view media');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'update media');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete media');
    }

    // No opinion.
    return AccessResult::create();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create media');
  }

}
