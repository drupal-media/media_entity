<?php

namespace Drupal\media_entity\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an action to reset the thumbnail on a media entity.
 *
 * @Action(
 *   id = "media_reset_thumbnail_action",
 *   label = @Translation("Reset media thumbnail"),
 *   type = "media"
 * )
 */
class ResetMediaThumbnail extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $entity->automaticallySetThumbnail();
    // We need to change at least one value, otherwise the changed timestamp
    // will not be updated.
    $entity->changed = 0;
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\media_entity\MediaInterface $object */
    return $object->access('update', $account, $return_as_object);
  }

}
