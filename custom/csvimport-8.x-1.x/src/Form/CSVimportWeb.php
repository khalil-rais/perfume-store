<?php
/**
 * @file
 * Contains \Drupal\csvimport\Form\CSVimportForm.
 */

namespace Drupal\csvimport\Form;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

//use  Drupal\parfum\Controller;

//require_once ('C:\xampp\htdocs\parfumerie\web\modules\custom\parfum\src\Controller\ParfumController.php');


/**
 * Implements the import form to upload a file and start the batch on form
 * submit.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class CSVimportWeb extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webimport';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	//$parfum = new Drupal\parfum\Controller::ParfumController ();
	//$parfum = new ParfumController ();
	//$parfum = \Drupal\parfum\Controller\ParfumController::mon_test ();
	
    $form['#attributes'] = [
      'enctype' => 'multipart/form-data',
    ];

$form['start_page'] = array(
  '#type' => 'textfield',
  '#title' => t('Starting Page'),
  '#default_value' => '1',
  '#size' => 60,
  '#maxlength' => 128,
  '#required' => TRUE,
);

$form['ending_page'] = array(
  '#type' => 'textfield',
  '#title' => t('Ending Page'),
  '#default_value' => '2',
  '#size' => 60,
  '#maxlength' => 128,
  '#required' => TRUE,
);



$form['nbr_article'] = array(
  '#type' => 'textfield',
  '#title' => t('Nomre Articles'),
  '#default_value' => '500',
  '#size' => 60,
  '#maxlength' => 128,
  '#required' => TRUE,
);


    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => t('Start Import'),
    ];
	

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
	  //dpm ($form);
	/*$form_state->setValue('csvupload', 'C:\xampp\htdocs\parfumerie\web\modules\custom\csvimport-8.x-1.x\src\Form\example.csv');
    if ($csvupload = $form_state->getValue('csvupload')) {

      if ($handle = fopen($csvupload, 'r')) {

        if ($line = fgetcsv($handle, 4096)) {
          if ($line[0] != 'Example CSV for csvimport.module - http://github.com/xurizaemon/csvimport') {
            $form_state->setErrorByName('csvfile', t('Sorry, this file does not match the expected format.'));
          }
        }
        fclose($handle);
      }
      else {
        $form_state->setErrorByName('csvfile', t('Unable to read uploaded file @filepath', ['@filepath' => $csvupload]));
      }
    }*/
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
	  //dpm ($form_state);
	  
	  
	\Drupal::logger('csv')->notice('Volet Submit');
    $batch = [
      'title'            => t('Importing CSV ...'),
      'operations'       => [],
      'init_message'     => t('Starting Articles Import'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished'         => 'webimport_import_finished',
      'file'             => drupal_get_path('module', 'csvimport') . '/csvimport.batch.inc',
    ];

    if (1) {
		//\Drupal::logger('csv')->notice('File is OK');

      //if ($handle = fopen($csvupload, 'r')) {
		if (1) {
			
			for ($i=$form_state->getValue('start_page');$i<=$form_state->getValue('ending_page');$i++){
				$batch['operations'][] = [
					'_webimport_import_line',
					[$i,$form_state->getValue('nbr_article') ],
				];
			}
          
		  
        /*while ($line = fgetcsv($handle, 4096)) {

          $batch['operations'][] = [
            '_csvimport_import_line',
            [array_map('base64_encode', $line)],
          ];
        }*/

        //fclose($handle);

      } // we caught this in csvimport_form_validate()
    } // we caught this in csvimport_form_validate()
	else{
		\Drupal::logger('csv')->notice('File is NOK');
	}

    batch_set($batch);
  }

}

	
	