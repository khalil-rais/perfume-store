<?php


namespace Drupal\parfum\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "search_product_block",
 *   admin_label = @Translation("Search product block"),
 *   category = @Translation("Search product block")
 * )
 */
class SearchProductBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\parfum\Form\SearchProductForm');
    return $form;
   }
}