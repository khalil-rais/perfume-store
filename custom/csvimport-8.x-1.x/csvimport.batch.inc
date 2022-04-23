<?php

use \Drupal\node\Entity\Node;
use  \Drupal\parfum\Controller;


//function _parfum_update_import_line($page,$amount, &$context) {
function _Parfumcheck_import_line($page,$amount, &$context) {
		
	$parfum = new \Drupal\parfum\Controller\ParfumController;
	$progress_result = $parfum->check_changed_stock ($page * $amount,$page * $amount + $amount,0);
	if (!isset($context['results']['rows_imported'])){
		$context['results']['rows_imported']=0;
		$context['results']['produit_hors_prix'] = 0;		
		$context['results']['produit_hors_stock'] = 0;
	}
	//\Drupal::logger('parfum')->notice('Avancement: '.$progress_result['#markup']);		
	$progress_result = explode ( '|' , $progress_result['#markup']);
	$progress = array();
	foreach ($progress_result as $key_pg => $value_pg){
		$progress []= explode ( ':' , $value_pg);
	}
	/*
		"Articles parcourus:".$i . 
		'|Articles désactivés hors prix:'.$j. 
		'|Articles désactivés hors stock:'.$k		  
	*/	
	//\Drupal::logger('import ')->notice('Webservice hors stock'.$progress['produit_hors_stock']);
	$context['results']['rows_imported']+= $progress[0][1];;
	$context['results']['produit_hors_prix'] += $progress[1][1];
	$context['results']['produit_hors_stock'] += $progress[2][1];
		
	$context['message'] = t('Sur un total de %c articles, %h hors prix, %i hors stock.', 
							[
								'%c' => $progress[0][1], 
								'%h' => $progress[1][1],
								'%i' => $progress[2][1]
							]
						);		
	usleep(2500);	
}



//function _webimport_import_line($page,$amount, &$context) {
function _parfum_update_import_line($page,$amount, &$context) {
		
	$parfum = new \Drupal\parfum\Controller\ParfumController;
	$progress_result = $parfum->update_parfum_articles ($page,$amount);
	if (!isset($context['results']['rows_imported'])){
		$context['results']['rows_imported']=0;
		$context['results']['parfum_added']=0;
		$context['results']['produit_hors_prix'] = 0;		
		$context['results']['produit_hors_stock'] = 0;
	}
	//\Drupal::logger('parfum')->notice('Avancement: '.$progress_result['#markup']);		
	$progress_result = explode ( '|' , $progress_result['#markup']);
	$progress = array();
	foreach ($progress_result as $key_pg => $value_pg){
		$progress []= explode ( ':' , $value_pg);
	}
	/*
		"Articles parcourus:".$total_par_page . 
		'|Articles parfums ajoutés:'.$i.
		'|Articles hors prix:'.$j.
		'|Hors stock:'.$k.
	*/	
	//\Drupal::logger('import ')->notice('Webservice hors stock'.$progress['produit_hors_stock']);
	$context['results']['rows_imported']+= $progress[0][1];;
	$context['results']['parfum_added'] += $progress[1][1];
	$context['results']['produit_hors_prix'] += $progress[2][1];
	$context['results']['produit_hors_stock'] += $progress[3][1];
		
	$context['message'] = t('Sur un total de %c articles, %d créés, %h hors prix, %i hors stock.', 
							[
								'%c' => $progress[0][1], 
								'%d' => $progress[1][1],
								'%h' => $progress[2][1],
								'%i' => $progress[3][1]
							]
						);		
	usleep(2500);
	
}

function Parfumcheck_finished($success, $results, $operations) {
		drupal_set_message(t('%a articles ont été parcourus, %c hors prix et %d hors stock.', 
					[
						'%a' => $results['rows_imported'] ,
						'%c' => $results['produit_hors_prix'], 
						'%d' => $results['produit_hors_stock']
					]
				)
			);
		
		return(t('%a articles ont été parcourus, %c hors prix et %d hors stock.', 
					[
						'%a' => $results['rows_imported'] ,
						'%c' => $results['produit_hors_prix'], 
						'%d' => $results['produit_hors_stock']
					]
				)
		);
}



function parfum_update_finished($success, $results, $operations) {
		drupal_set_message(t('%a articles ont été parcourus, %b ajoutés, %c hors prix et %d hors stock.', 
					[
						'%a' => $results['rows_imported'] ,
						'%b' => $results['parfum_added'] , 
						'%c' => $results['produit_hors_prix'], 
						'%d' => $results['produit_hors_stock']
					]
				)
			);
		
		return(t('%a articles ont été parcourus, %b ajoutés, %c hors prix et %d hors stock.', 
					[
						'%a' => $results['rows_imported'] ,
						'%b' => $results['parfum_added'] , 
						'%c' => $results['produit_hors_prix'], 
						'%d' => $results['produit_hors_stock']
					]
				)
		);		
}

/**
 * Handle batch completion.
 *
 *   Creates a new CSV file containing all failed rows if any.
 */
function csvimport_import_finished($success, $results, $operations) {
\Drupal::logger('csv')->notice('Traitement en cours');
  if (!empty($results['failed_rows'])) {
	  \Drupal::logger('csv')->notice('Quelques Erreur');

    $dir = 'public://csvimport';
    if (file_prepare_directory($dir, FILE_CREATE_DIRECTORY)) {
		\Drupal::logger('csv')->notice('Sauvegarde des erreurs');

      $csv_filename = 'failed_rows-' . basename($results['uploaded_filename']); // we validated extension on upload
      $csv_filepath = $dir . '/' . $csv_filename;

      $targs = [
        ':csv_url'      => file_create_url($csv_filepath),
        '@csv_filename' => $csv_filename,
        '@csv_filepath' => $csv_filepath,
      ];

      if ($handle = fopen($csv_filepath, 'w+')) {

        foreach ($results['failed_rows'] as $failed_row) {
          fputcsv($handle, $failed_row);
        }

        fclose($handle);
        drupal_set_message(t('Some rows failed to import. You may download a CSV of these rows: <a href=":csv_url">@csv_filename</a>', $targs), 'error');
      }
      else {
        drupal_set_message(t('Some rows failed to import, but unable to write error CSV to @csv_filepath', $targs), 'error');
      }
    }
    else {
      drupal_set_message(t('Some rows failed to import, but unable to create directory for error CSV at @csv_directory', $targs), 'error');
    }
  }

  return t('The CSV import has completed.');
}

/**
 * Remember the uploaded CSV filename.
 *
 * @TODO Is there a better way to pass a value from inception of the batch to
 * the finished function?
 */
function _csvimport_remember_filename($filename, &$context) {

  $context['results']['uploaded_filename'] = $filename;
}

/**
 * Process a single line.
 */
function _webimport_import_line($page,$amount, &$context) {
	//$parfum = new \Drupal\parfum\Controller\ParfumController::ParfumController ();
//$parfum = \Drupal\parfum\Controller\ParfumController::myCallbackMethod ($page,$amount);
	//$parfum = new \Drupal\parfum\Controller\ParfumController::ParfumController ();
	$parfum = new \Drupal\parfum\Controller\ParfumController;
	$progress_result = $parfum->myCallbackMethod ($page,$amount);
	if (!isset($context['results']['rows_imported'])){
		$context['results']['rows_imported']=0;
		$context['results']['produit_hors_stock'] = 0;
		$context['results']['produit_hors_categories'] = 0;		
		$context['results']['cosmetique_added'] = 0;
		$context['results']['hygiene_added'] = 0;
		$context['results']['maquillage_added'] = 0;
		$context['results']['parfum_added'] = 0;
		$context['results']['soins_corporel_added'] = 0;
		$context['results']['soins_cheveux_added'] = 0;
		$context['results']['soins_visage_added'] = 0;
		$context['results']['solaire_added'] = 0;
		$context['results']['unkown'] = 0;
		
		$context['results']['free'] = 0;
	}
	//\Drupal::logger('parfum')->notice('Avancement: '.$progress_result['#markup']);		
	$progress_result = explode ( '|' , $progress_result['#markup']);
	$progress = array();
	foreach ($progress_result as $key_pg => $value_pg){
		$progress []= explode ( ':' , $value_pg);
	}
	/*
		"Articles parcourus:".$total_par_page . 
		'|Articles parfums ajoutés:'.$i.
		'|Articles cosmétiques:'.$j.
		'|Hors stock:'.$produit_hors_stock.
		'|Hors catégories:'.$produit_hors_categories
		'|Articles gratuits:'.$free_product,	
	*/	
	//\Drupal::logger('import ')->notice('Webservice hors stock'.$progress['produit_hors_stock']);
	$context['results']['rows_imported']++;
	$context['results']['produit_hors_stock'] += $progress[3][1];
	$context['results']['produit_hors_categories'] += $progress[4][1];
	
	$context['results']['parfum_added'] += $progress[1][1];
	$context['results']['cosmetique_added'] += $progress[2][1];
	$context['results']['hygiene_added'] += $progress[6][1];
	$context['results']['soins_corporel_added'] += $progress[7][1];
	$context['results']['soins_cheveux_added'] += $progress[8][1];
	$context['results']['soins_visage_added'] += $progress[9][1];
	$context['results']['maquillage_added'] += $progress[10][1];
	$context['results']['solaire_added'] += $progress[11][1];
	$context['results']['unkown'] += $progress[12][1];
	
	$context['results']['free'] += $progress[5][1];
	
	$context['message'] = t('Importing %c products, %d created, %h parfum created, %i cosmétique created, %j hyigiène, %k Soins corporels, %l soins des cheveux, %m soins du visage, %e out of stock, %f other categories, %g gratuit', [
						'%c' => $progress[0][1], 
						'%d' => $progress[1][1] + $progress[2][1]+$progress[6][1]+$progress[7][1]+$progress[8][1]+$progress[9][1],
						'%h' => $progress[1][1],
						'%i' => $progress[2][1],
						'%e' => $progress[3][1], 
						'%f' => $progress[4][1],
						'%g' => $progress[5][1],
						'%j' => $progress[6][1],
						'%k' => $progress[7][1],
						'%l' => $progress[8][1],
						'%m' => $progress[9][1]]
						);	
	
	usleep(2500);
	
	
}
function _csvimport_import_line($line, &$context) {
	\Drupal::logger('csv')->notice('Fancy');

  $context['results']['rows_imported']++;
  $line = $cleaned_line = array_map('base64_decode', $line);

  /**
   * Simply show the import row count.
   */
  $context['message'] = t('Importing row !c', ['!c' => $context['results']['rows_imported']]);

  /**
   * Alternatively, our example CSV happens to have the title in the
   * third column, so we can uncomment this line to display "Importing
   * Blahblah" as each row is parsed.
   *
   * You can comment out the line above if you uncomment this one.
   */
  $context['message'] = t('Importing %title', ['%title' => $line[2]]);

  /**
   * In order to slow importing and debug better, we can uncomment
   * this line to make each import slightly slower.
   */
  usleep(2500);

  /**
   * Convert the line of the CSV file into a new node.
   *
   */
  // if ($context['results']['rows_imported'] > 1) { // Skip header line.
  //   $node = Node::create([
  //     'type'  => 'article',
  //     'title' => $line[2],
  //     'body'  => $line[0],
  //   ]);
  //
  //   $node->save();
  // }

  /**
   * If the first two columns in the row are "ROW", "FAILS" then we
   * will add that row to the CSV we'll return to the importing person
   * after the import completes.
   */
  if ($line[1] == 'ROW' && $line[2] == 'FAILS') {
    $context['results']['failed_rows'][] = $line;
  }

}
