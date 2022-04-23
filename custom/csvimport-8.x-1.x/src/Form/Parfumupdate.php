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
class Parfumupdate extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'parfum-update';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes'] = [
      'enctype' => 'multipart/form-data',
    ];

	$form['start_page'] = array(
	'#type' => 'textfield',
	'#title' => t('Page de Début'),
	'#default_value' => '1',
	'#size' => 60,
	'#maxlength' => 128,
	'#required' => TRUE,
	);
	
	$form['ending_page'] = array(
	'#type' => 'textfield',
	'#title' => t('Page de Fin'),
	'#default_value' => '2',
	'#size' => 60,
	'#maxlength' => 128,
	'#required' => TRUE,
	);
	
	
	
	$form['nbr_article'] = array(
	'#type' => 'textfield',
	'#title' => t('Nomre d\'Articles'),
	'#default_value' => '500',
	'#size' => 60,
	'#maxlength' => 128,
	'#required' => TRUE,
	);


    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => t('Démarrer la mise à jour'),
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
	  
	  
	//\Drupal::logger('csv')->notice('Volet Submit');
    $batch = [
      'title'            => t('Mise à jour des articles parfums ...'),
      'operations'       => [],
      'init_message'     => t('Démarrage de la mise à jour'),
      'progress_message' => t(' @current lot traité sur @total.'),
      'error_message'    => t('Un problème est survenu lors du traitement'),
      //'finished'         => 'webimport_import_finished',
	  'finished'         => 'parfum_update_finished',
      'file'             => drupal_get_path('module', 'csvimport') . '/csvimport.batch.inc',
    ];

    if (1) {
		//\Drupal::logger('csv')->notice('File is OK');

      //if ($handle = fopen($csvupload, 'r')) {
		if (1) {
			
			for ($i=$form_state->getValue('start_page');$i<=$form_state->getValue('ending_page');$i++){
				$batch['operations'][] = [
					'_parfum_update_import_line',
					[$i,$form_state->getValue('nbr_article') ],
				];
			}

      } // we caught this in csvimport_form_validate()
    } // we caught this in csvimport_form_validate()
	else{
		\Drupal::logger('csv')->notice('File is NOK');
	}

    batch_set($batch);
  }

}

	
	
