<?php

namespace Drupal\parfum\Controller;

use Drupal\commerce_product\Entity\Product; 
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Unicode;

class SearchProductController extends ControllerBase
{

  /**
   * Returns response for the autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */

  public function autocomplete(request $request) {
    $string = $request->query->get('q');
    if ($string) {
      $results = \Drupal::entityQuery('commerce_product')
        ->condition("title",db_like($string), 'CONTAINS')
        ->execute();
      $products = array();

      if (isset($results)) {
        foreach ($results as $result) {
          $product = Product::load($result);
          $products[] = ['value'=>$product ->getTitle().' ('.$result.')','label'=>$product ->getTitle()];
        }
      }
    }
    return new JsonResponse($products);
  }
}