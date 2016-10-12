<?php

namespace Drupal\Tests\lightning_preview\Unit;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lightning_preview\WorkspaceLock;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\multiversion\Entity\WorkspaceTypeInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\lightning_preview\WorkspaceLock
 * @group lightning_preview
 */
class WorkspaceLockTest extends UnitTestCase {

  /**
   * The mocked workspace manager.
   *
   * @var WorkspaceManagerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $workspaceManager;

  /**
   * The mocked entity type manager.
   *
   * @var EntityTypeManagerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityTypeManager;

  /**
   * The mocked active workspace.
   *
   * @var WorkspaceInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $activeWorkspace;

  /**
   * The mocked workspace type.
   *
   * @var WorkspaceTypeInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $workspaceType;

  /**
   * The WorkspaceLock instance under test.
   *
   * @var WorkspaceLock
   */
  protected $workspaceLock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->workspaceManager = $this->prophesize(WorkspaceManagerInterface::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->activeWorkspace = $this->prophesize(WorkspaceInterface::class);
    $this->workspaceType = $this->prophesize(WorkspaceTypeInterface::class);

    $this->workspaceManager->getActiveWorkspace()->willReturn(
      $this->activeWorkspace->reveal()
    );

    $this->workspaceLock = new WorkspaceLock(
      $this->workspaceManager->reveal(),
      $this->entityTypeManager->reveal()
    );
  }

  /**
   * Tests that the live workspace is never considered locked.
   *
   * @covers ::isWorkspaceLocked
   */
  public function testLiveWorkspaceNotLocked() {
    $this->activeWorkspace->getMachineName()->willReturn('live');
    $active_workspace = $this->activeWorkspace->reveal();

    $this->assertFalse($this->workspaceLock->isWorkspaceLocked());
    $this->assertFalse($this->workspaceLock->isWorkspaceLocked($active_workspace));
  }

  /**
   * Tests the influence of moderation states on workspace locking.
   *
   * @covers ::isWorkspaceLocked
   */
  public function testModeratedWorkspaceLock() {
    $this->activeWorkspace->getMachineName()->willReturn('foo');
    $this->activeWorkspace->hasField('moderation_state')->willReturn(TRUE);
    $active_workspace = $this->activeWorkspace->reveal();

    $active_workspace->type = (object) [
      'entity' => $this->workspaceType->reveal(),
    ];
    $this->workspaceType
      ->getThirdPartySetting('workbench_moderation', 'locked_states', Argument::any())
      ->willReturn(['archived', 'published']);

    $active_workspace->moderation_state = (object) [
      'target_id' => 'draft',
    ];
    $this->assertFalse($this->workspaceLock->isWorkspaceLocked($active_workspace));

    $active_workspace->moderation_state->target_id = 'published';
    $this->assertTrue($this->workspaceLock->isWorkspaceLocked($active_workspace));
  }

  /**
   * Tests workspace locking for config entity types.
   *
   * @covers ::isEntityTypeLocked
   * @depends testLiveWorkspaceNotLocked
   * @depends testModeratedWorkspaceLock
   */
  public function testConfigEntityTypeLock() {
    $this->assertFalse($this->workspaceLock->isEntityTypeLocked('workspace'));

    $definition = $this->prophesize(ConfigEntityTypeInterface::class)->reveal();
    $this->entityTypeManager->getDefinition('foo')->willReturn($definition);

    $this->activeWorkspace->getMachineName()->willReturn('live');
    $this->assertFalse($this->workspaceLock->isEntityTypeLocked('foo'));

    $this->activeWorkspace->getMachineName()->willReturn('stage');
    $this->assertTrue($this->workspaceLock->isEntityTypeLocked('foo'));
  }

  /**
   * Tests workspace locking for content entity types.
   *
   * @covers ::isEntityTypeLocked
   * @depends testLiveWorkspaceNotLocked
   * @depends testModeratedWorkspaceLock
   */
  public function testContentEntityTypeLock() {
    $definition = $this->prophesize(ContentEntityTypeInterface::class)->reveal();
    $this->entityTypeManager->getDefinition('foo')->willReturn($definition);

    $this->activeWorkspace->getMachineName()->willReturn('live');
    $this->assertFalse($this->workspaceLock->isEntityTypeLocked('foo'));
  }

}
