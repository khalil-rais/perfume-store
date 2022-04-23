<?php
 
/**
 * @file
 * Contains \Drupal\parfum\Plugin\Block\HelloWorldBlock1
 */

namespace Drupal\parfum\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block\Annotation\Block;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a simple block.
 *
 * @Block(
 *   id = "hello_world_block1",
 *   admin_label = @Translation("Hello World Block1"),
 *   module = "hello_world"
 * )
 */
class ParfumBlock extends BlockBase {
 
  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  public function build() {
    $this->configuration['label'] = t('Parfum Block1');
    return array(
      '#children' => t('Hello from a custom block'),
    );
  }
}
