<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaInterface.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a media entity.
 */
interface MediaInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Returns the media bundle.
   *
   * @return string
   *   The media type.
   */
  public function getBundle();

  /**
   * Returns the media name.
   *
   * @return string
   *   Name of the media.
   */
  public function getName();

  /**
   * Sets the media name.
   *
   * @param string $name
   *   The media name.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The called media entity.
   */
  public function setName($name);

  /**
   * Returns the media creation timestamp.
   *
   * @return int
   *   Creation timestamp of the media.
   */
  public function getCreatedTime();

  /**
   * Sets the media creation timestamp.
   *
   * @param int $timestamp
   *   The media creation timestamp.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The called media entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the media publisher user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The author user entity.
   */
  public function getPublisher();

  /**
   * Returns the media publisher user ID.
   *
   * @return int
   *   The author user ID.
   */
  public function getPublisherId();

  /**
   * Sets the media publisher user ID.
   *
   * @param int $uid
   *   The author user id.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The called media entity.
   */
  public function setPublisherId($uid);

  /**
   * Returns the media published status indicator.
   *
   * Unpublished media are only visible to their authors and to administrators.
   *
   * @return bool
   *   TRUE if the media is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a media.
   *
   * @param bool $published
   *   TRUE to set this media to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The called media entity.
   */
  public function setPublished($published);

  /**
   * Returns the media type.
   *
   * @return string
   *   The media type.
   */
  public function getType();

  /**
   * Sets the media type.
   *
   * @param string $type
   *   The media type.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The called media entity.
   */
  public function setType($type);

  /**
   * Returns the media resource ID.
   *
   * @return string
   *   The media resource ID.
   */
  public function getResourceId();

  /**
   * Sets the media resource ID..
   *
   * @param string $id
   *   The media resource ID.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The called media entity.
   */
  public function setResourceId($id);
}
