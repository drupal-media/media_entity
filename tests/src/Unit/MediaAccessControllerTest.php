<?php

/**
 * @file
 * Contains \Drupal\Tests\media_entity\Unit\MediaAccessControllerTest.
 */

namespace Drupal\Tests\media_entity\Unit;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\media_entity\MediaAccessController;
use Drupal\media_entity\MediaInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * @group media_entity
 */
class MediaAccessControllerTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * @var \Drupal\media_entity\MediaAccessController
   */
  protected $accessController;

  protected function setUp() {
    $this->entityType = $this->prophesize(EntityTypeInterface::class);
    $this->entityType->id()->willReturn('media');
    $this->accessController = new TestMediaAccessController($this->entityType->reveal());

    // MediaAccessController::checkAccess() will call cachePerPermission() on
    // the access result, which in turn will access the non-injected
    // cache_contexts_manager service and assert the result of its
    // assertValidTokens() method. So we set that up to always return TRUE.
    $container = new ContainerBuilder();
    $cache_contexts_manager = $this->prophesize(CacheContextsManager::class);
    $cache_contexts_manager->assertValidTokens(Argument::any())->willReturn(TRUE);
    $container->set('cache_contexts_manager', $cache_contexts_manager->reveal());
    \Drupal::setContainer($container);
  }

  /**
   * Tests
   */
  public function testCreateAccessNotAdministrator() {
    $entity = $this->prophesize(MediaInterface::class);
    $entity->getPublisherId()->willReturn(42);

    $account = $this->prophesize(AccountInterface::class);
    $account->id()->willReturn(42);
    $account->hasPermission('administer media')->willReturn(FALSE);
    $account->hasPermission('create media')->willReturn(TRUE);

    $result = $this->accessController->checkAccess($entity->reveal(), 'create', $account->reveal());
    $this->assertInstanceOf(AccessResultAllowed::class, $result);
  }

}

class TestMediaAccessController extends MediaAccessController {

  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return parent::checkAccess($entity, $operation, $account);
  }

}
