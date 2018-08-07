<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\comment\Entity\CommentType;
use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning
 */
class DefaultCommentTypeTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
  ];

  /**
   * Tests that the default comment type is available after
   * installing the comment module.
   */
  public function testDefaultCommentType() {
    $comment_types = CommentType::loadMultiple();
    $this->assertCount(1, $comment_types);
    /** @var \Drupal\Core\Entity\EntityInterface $comment_type */
    $comment_type = reset($comment_types);
    $this->assertEquals('Default comments', $comment_type->label());
  }

}
