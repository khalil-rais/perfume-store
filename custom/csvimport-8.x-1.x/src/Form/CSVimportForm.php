<?php
/**
 * @file
 * Contains \Drupal\csvimport\Form\CSVimportForm.
 */

namespace Drupal\csvimport\Form;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the import form to upload a file and start the batch on form
 * submit.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class CSVimportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csvimport';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes'] = [
      'enctype' => 'multipart/form-data',
    ];


    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => t('Start Import'),
    ];
	

    return $form;
  }

  /**
   * Validate the file upload. It must be a CSV, and we must
   * successfully save it to our import directory.
   */
  public static function csvimport_validate_fileupload(&$element, FormStateInterface $form_state, &$complete_form) {
	  dpm ($complete_form);
	  \Drupal::logger('csv')->notice('Element Validate');

    $validators = [
      'file_validate_extensions' => ['csv CSV'],
    ];

    if ($file = file_save_upload('csvfile', $validators, FALSE, 0, FILE_EXISTS_REPLACE)) {
		\Drupal::logger('csv')->notice('Validation OK');

      // The file was saved using file_save_upload() and was added to the
      // files table as a temporary file. We'll make a copy and let the
      // garbage collector delete the original upload.
      $csv_dir          = 'temporary://csvfile';
      $directory_exists = file_prepare_directory($csv_dir, FILE_CREATE_DIRECTORY);

      if ($directory_exists) {
        $destination = $csv_dir . '/' . $file->getFilename();
        if (file_copy($file, $destination, FILE_EXISTS_REPLACE)) {
          $form_state->setValue('csvupload', $destination);
        }
        else {
          $form_state->setErrorByName('csvimport', t('Unable to copy upload file to @dest', ['@dest' => $destination]));
        }
      }
    }
	else{
		\Drupal::logger('csv')->notice('Validation NOK');
	}
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
	  //dpm ($form);
	$form_state->setValue('csvupload', 'C:\xampp\htdocs\parfumerie\web\modules\custom\csvimport-8.x-1.x\src\Form\example.csv');
    if ($csvupload = $form_state->getValue('csvupload')) {

      if ($handle = fopen($csvupload, 'r')) {

        if ($line = fgetcsv($handle, 4096)) {

          /**
           * Validate the uploaded CSV here.
           *
           * The example CSV happens to have cell A1 ($line[0]) as
           * below; we validate it only.
           *
           * You'll probably want to check several headers, eg:
           *   if ( $line[0] == 'Index' || $line[1] != 'Supplier' || $line[2] != 'Title' )
           */
          if ($line[0] != 'Example CSV for csvimport.module - http://github.com/xurizaemon/csvimport') {
            $form_state->setErrorByName('csvfile', t('Sorry, this file does not match the expected format.'));
          }
        }
        fclose($handle);
      }
      else {
        $form_state->setErrorByName('csvfile', t('Unable to read uploaded file @filepath', ['@filepath' => $csvupload]));
      }
    }
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
      'init_message'     => t('Commencing'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished'         => 'csvimport_import_finished',
      'file'             => drupal_get_path('module', 'csvimport') . '/csvimport.batch.inc',
    ];

    if ($csvupload = $form_state->getValue('csvupload')) {
		\Drupal::logger('csv')->notice('File is OK');

      if ($handle = fopen($csvupload, 'r')) {

        $batch['operations'][] = [
          '_csvimport_remember_filename',
          [$csvupload],
        ];

        while ($line = fgetcsv($handle, 4096)) {

          /**
           * Use base64_encode to ensure we don't overload the batch
           * processor by stuffing complex objects into it.
           */
          $batch['operations'][] = [
            '_csvimport_import_line',
            [array_map('base64_encode', $line)],
          ];
        }

        fclose($handle);

      } // we caught this in csvimport_form_validate()
    } // we caught this in csvimport_form_validate()
	else{
		\Drupal::logger('csv')->notice('File is NOK');
	}

    batch_set($batch);
  }

}

	
	
