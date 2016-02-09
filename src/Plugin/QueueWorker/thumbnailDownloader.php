<?php

/**
 * @file
 * Contains \Drupal\media_entity\Plugin\QueueWorker\thumbnailDownloader.
 */

namespace Drupal\media_entity\Plugin\QueueWorker;

use Drupal\media_entity\Entity\Media;
use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Download images.
 *
 * @QueueWorker(
 *   id = "media_entity_thumbnail",
 *   title = @Translation("Thumbnail downloader"),
 *   cron = {"time" = 60}
 * )
 */
class thumbnailDownloader extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $entity = Media::load($data['id']);
    // Indicate that the entity is being processed from a queue and that
    // thumbnail images should be downloaded.
    $entity->setQueuedThumbnailDownload();
    $entity->save();
  }

}
