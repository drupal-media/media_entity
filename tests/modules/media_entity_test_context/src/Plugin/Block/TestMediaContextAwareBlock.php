<?php

namespace Drupal\media_entity_test_context\Plugin\Block;

use Drupal\Core\Annotation\ContextDefinition;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a Media Context Aware block.
 *
 * @Block(
 *   id = "test_media_context_block",
 *   admin_label = @Translation("Test media context aware block"),
 *   context = {
 *     "media" = @ContextDefinition("entity:media")
 *   }
 * )
 */
class TestMediaContextAwareBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var Drupal\media_entity\Entity\Media $media */
    $media = $this->getContextValue('media');
    $build = [];
    // No media context found when not using a condition.
    $build['media_id']['#markup'] = $media ? 'Media ID: ' . $media->id() : 'No media context found.';
    return $build;
  }

}
