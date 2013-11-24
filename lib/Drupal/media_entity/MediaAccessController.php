<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaAccessController;
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;


/**
 * Defines an access controller for the media entity.
 */
class MediaAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return $account->hasPermission('view media');
        break;

      case 'update':
        return $account->hasPermission('update media');
        break;

      case 'delete':
        return $account->hasPermission('delete media');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('create media');
  }

}
