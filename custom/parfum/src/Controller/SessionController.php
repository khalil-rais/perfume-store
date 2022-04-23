<?php
/**
 * @file
 * Contains \Drupal\test_twig\Controller\TestTwigController.
 */
 
namespace Drupal\parfum\Controller;
 
use Drupal\Core\Controller\ControllerBase;
 
class SessionController extends ControllerBase {
  public function content() {
    
    dpm('coco');
	\Drupal::logger('parfum')->notice('Cookies Accepted for session id :'.session_id());
	$_SESSION['afficher_cookies_message'] =  false;
    /*return [
      '#theme' => 'html',
      //'#afficher_cookies_message' => 'mimi',
    ];*/
	return array(
	  '#type' => 'markup',
	  '#markup' => '',
	);	
 
  }
}