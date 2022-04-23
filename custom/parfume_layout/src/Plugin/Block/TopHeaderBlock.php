<?php

namespace Drupal\parfume_layout\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'HeaderBlock' block.
 *
 * @Block(
 *  id = "top_header_block",
 *  admin_label = @Translation("Top Header block"),
 * )
 */
class TopHeaderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $variables['images_path'] =  $base_url.'/'.drupal_get_path('module', 'parfume_layout').'/assets/images/';
    $build = [];
    //$build['header_block']['#markup'] = 'Footer Block.';
    $build['header_block']['#theme'] = 'theme_layout_top_header_block';
    $build['header_block']['#variables'] = $variables;

    return $build;
  }

}
