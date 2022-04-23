<?php
/**
 * @file
 * Contains \Drupal\csvimport\Form\CSVimportForm.
 */

namespace Drupal\csvimport\Form;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Drupal\Core\Form\FormState ;

class CSVController extends ContainerBuilder {
	
public function batch_example($options1=12, $options2=500) {
	$my_form = new CSVimportForm ();
	\Drupal::logger('csv')->notice('Batch Lancé');
	// register a new user
	$form_state = new FormState();
	//$values['csvfile'] = '';
	//$form_state->setValues($values);
/*	$data = file_get_contents('C:\xampp\htdocs\parfumerie\web\modules\custom\csvimport-8.x-1.x\src\Form\example.csv');
	$file = file_save_data($data, 'public://druplicon.csv', FILE_EXISTS_REPLACE);
	$form_state->setValue('csvfile', $file);*/
	

	
	\Drupal::formBuilder()->submitForm($my_form, $form_state);



	\Drupal::logger('csv')->notice('Batch Configuré');
	
    return array(
      '#type' => 'markup',
      '#markup' => 'Batch is done',
	  //'#markup' => ' produits ajoutés sur 0',
    );
	
}	
	
}