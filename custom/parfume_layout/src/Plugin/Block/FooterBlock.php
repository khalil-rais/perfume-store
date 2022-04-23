<?php

namespace Drupal\parfume_layout\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'FooterBlock' block.
 *
 * @Block(
 *  id = "footer_block",
 *  admin_label = @Translation("Footer block"),
 * )
 */
class FooterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $variables['images_path'] =  $base_url.'/'.drupal_get_path('module', 'parfume_layout').'/assets/images/';
    $build = [];
    //$build['footer_block']['#markup'] = 'Footer Block.';
    $build['footer_block']['#theme'] = 'theme_layout_footer_block';
    $build['footer_block']['#variables'] = $variables;

    return $build;
  }

}
