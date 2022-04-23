<?php
/**
 * @file
 * Contains \Drupal\hello_world\HelloWorldController.
 */
 
namespace Drupal\parfum\Controller;
 

use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;
use \Drupal\commerce_product\Entity\ProductVariationType;
//use Drupal\parfum\Controller\PDF;
require(drupal_get_path('module', 'parfum') . '/fpdf17/pdf.php');

class ParfumController extends ContainerBuilder {

	private $user = ""; private $password = "";

	private $max_parfum_price = 160;
	private $min_parfum_price = 15;
	private $seuil_limite = 5;
	
	
	public function check_changed_stock ($from,$to,$use_case){
		$varaition_entity = 'commerce_product_variation';
		$varation_bundle = 'parfum';
		$i = 0; //varations parcourues
		$j = 0; //varations désactivés pour prix
		$k = 0; //varations désactivés pour stock
				
		$query = \Drupal::entityQuery($varaition_entity)
		->condition('type', $varation_bundle)
		->range($from, $to)	;
		
		
		
		$vid = $query->execute();
		//Rechercher les articles
		$product_list = array();
		foreach ($vid as $key => $value){
			$variation = \Drupal\commerce_product\Entity\ProductVariation::load($value);
			if ( $variation->isActive() ){//Article Actifs
				$product_list [$variation->get('field_engelid')->getValue()[0]["value"]]= array ('variation_id' => $value, 'title' => $variation->getTitle(), 'engel_unit_price' => $variation->get('field_prix_engel')->getValue()[0]["number"], 'qte' => 0);
				$i++;
			}
			
		}
		
		//Récupérer les articles à désactiver
		$to_be_updated = $this->_detect_changed_stock($product_list);
		$to_be_removed = $to_be_updated ['Stock'];
		$to_be_updated_price = $to_be_updated ['Price'];
		//dpm ("Liste initiale de produits");
		//dpm ($product_list);
		//dpm ("Liste des produits à modifier/supprimer");
		//dpm ($to_be_updated );
		
		foreach ($to_be_removed as $key => $value){
			$variation = \Drupal\commerce_product\Entity\ProductVariation::load($product_list [$key]['variation_id']);
			if ($variation){
				$variation->setActive(false);
				$variation->set("field_prix_engel", new \Drupal\commerce_price\Price(strval (0.01), 'EUR'));
				$variation->save();
				$k++;
				//dpm ($key." was deactivated");
			}
			else{
				dpm ($key." could not be found");
			}			
		}
		
		foreach ($to_be_updated_price as $key => $value){
			$variation = \Drupal\commerce_product\Entity\ProductVariation::load($product_list [$key]['variation_id']);
			if ($variation){
				$variation->setActive(false);
				$variation->set("field_prix_engel", new \Drupal\commerce_price\Price(strval (0.01), 'EUR'));
				$variation->save();
				$j++;
				//dpm ($product_list [$key]['title']." (".$key.")a été désactivé à cause d'un changement de prix engel. Ancien:".$product_list [$key]['engel_unit_price']."/Nouveau:".$value."€.");
				//\Drupal::logger('parfum')->notice($product_list [$key]['title']." (".$key.")a été désactivé à cause d'un changement de prix engel. Ancien:".$product_list [$key]['engel_unit_price']."/Nouveau:".$value."€.");
			}
		}
						
		return array(
		  '#type' => 'markup',
		  '#markup' => 
			"Articles parcourus:".$i . 
			'|Articles désactivés hors prix:'.$j. 
			'|Articles désactivés hors stock:'.$k		  
		);		
	}
	
	function _generation_pdf($order) {
		/*$order = array();
		$order ['id']= '180430-001';
		$order ['order_time']= '30-04-2018';
		$order['lines'] = array("00014|Parfum CK|5|30|150", "00125|Parfum Hugo Boss|5|20|100", "08226|Parfum Coco Channel|4|15|60");
		
		$order ['nom_client']= 'Mme/Mr Dupont';
		$order ['adresse_client']= 'Rue des Jasmins';

		$order ['Total HT'] = '5,00';
		$order ['Frais de Port'] = '5,00';
		$order ['Total TTC'] = '5,00';

		$order ['message']= "Nous procèderons à l'envoi du colis dès la réception de votre virement sur notre compte. Vous pouvez consulter nos conditions générales de vente sur cette adresse http://parfumstreet.fr/cgv Pour toute demande ou réclamation, nous sommes à votre disposition au 06 17 87 76 80 ou par e-mail contact@parfumstreet.fr";*/

		date_default_timezone_set('Europe/Berlin');
		

		//$date = mktime(12, 0, 0, $date['month'], $date['day'], $date['year']);
		//Drupal\parfum\Controller\PDF
		//$pdf = new PDF();
		$pdf = new PDF\PDF;
		//$pdf = new PDF('L');
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetLineWidth(0.05);
		
		$pdf->Image('sites/default/files/logo_parfum_street.png', 5, 5);
		$pdf->SetFont('arial', 'B', 12);
		$pdf->SetTextColor(163, 2, 7);
		$pdf->Text(130, 15, utf8_decode($order ['nature'].' n°: '. $order ['id']));
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->Text(155, 28, utf8_decode('Paris le '.date('d/m/Y',strtotime($order ['order_time'])).',' ));
		 
		$pdf->SetFont('arial', '', 10);
		//start from here http://www.fpdf.org/en/script/script92.php		
		//$pdf->Text(23, 50, utf8_decode('Rapport de Visite Chaufferie et Station - Relevé du '.date('d/m/Y', $node->created) ));	
		$pdf->Text(5, 35, utf8_decode('Émetteur'));	
		$pdf->SetFillColor(230,230,230);
		$pdf->Rect(5,37,82,45,'F');
		$pdf->SetFont('arial', 'B', 12);
		//$pdf->SetXY(5, 39);
		$pdf->Text(7, 42, utf8_decode('Barbouch Imed'));	
		$pdf->SetFont('arial', '', 12);
		$pdf->Text(7, 48, utf8_decode('3 Allée Belle Croix'));	
		$pdf->Text(7, 54, utf8_decode('94200 Ivry Sur Seine'));	
		$pdf->SetFont('arial', 'B', 12);
		$pdf->Text(7, 60, utf8_decode('Téléphone :'));
		$pdf->Text(7, 66, utf8_decode('Numéro de SIREN :'));
		
		$pdf->SetFont('arial', '', 12);
		$pdf->Text(32, 60, utf8_decode('0950013177'));	
		$pdf->Text(9, 72, utf8_decode('538010356  R.C.S. Créteil'));	
		

		$pdf->SetFillColor(255,255,255);
		
		
		$pdf->SetFont('arial', '', 10);
		$pdf->Text(112, 35, utf8_decode('Adressée à'));	
		$pdf->Rect(112,37,85,45);
		$pdf->SetFont('arial', 'B', 12);
		$pdf->Text(114, 42, utf8_decode('Client:' ));
		$pdf->Text(114, 48, utf8_decode('Forme Juridique:' ));
		$pdf->Text(114, 54, utf8_decode('Téléphone:' ));		
		$pdf->Text(114, 60, utf8_decode('Adresse:' ));
		

		$pdf->SetFont('arial', '', 12);
		$pdf->Text(129, 42, utf8_decode($order ['nom_client']));
		$pdf->Text(150, 48, utf8_decode('Client Particulier'));	
		$pdf->Text(150, 54, utf8_decode($order ['telephone']));	
		
		
		$initial_address_line =  66;
		foreach ($order ['adresse_client'] as $key_a => $value_a){
			$pdf->Text(114, $initial_address_line, utf8_decode(' '.$value_a));
			$initial_address_line += 6;
		}
		
				
		
		$pdf->SetFont('arial', '', 10);
		$pdf->Text(159, 100, utf8_decode('Montants exprimés en Euros'));
		$pdf->SetFont('arial', '', 12);
		
		
		$pdf->SetXY(5, 102);
		
		//$pdf->MultiCell(170, 10, utf8_decode("Suite à notre visite à votre usine le ").date ('d/m/Y').utf8_decode(", nous avons effectué des analyses d'eau concernant la chaufferie.Vous trouverez ci-dessous les valeurs mesurées le jour de notre visite ainsi que nos recommandations."),0);
		
		/*$pdf->SetFont('arial', 'BU', 12);
		$pdf->Ln(2);
		$pdf->MultiCell(170, 10, utf8_decode("Relevé des mesures prises:"),0);	*/
		
		// Largeurs des colonnes
		$pdf->SetFillColor(230,230,230);
		//$pdf->SetDrawColor(30,30,30);
		$w = array(35, 100, 25, 20,20);
			
		$header = array(utf8_decode("Code"),utf8_decode("Désignation"), utf8_decode("P.U. HT"), utf8_decode("Qté"),utf8_decode("Total HT"));
		// En-tête
		for($i=0;$i<count($header);$i++){
			$pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
		}
		$pdf->Ln();
		
		
		//Code|Designation|PUHT|Qte|TotalHT
		
		
		 
		//$pdf->SetXY(5, 122);
		


		//if (isset ($node->field_presentation_pdf['und'])){//We will draw the taken paramters.
		if (isset ($order['lines'])){//We will draw the taken paramters.
			foreach ($order['lines'] as $key_l=> $value_l){
				$parameter_list = explode ('|',$value_l);
				$pdf->SetX(5);
				/*if (count ($parameter_list)==2){//we have here a section
					$pdf->SetFont('arial', 'B', 12);
					$pdf->Cell(10,7,$parameter_list[0],1,0,'C',false);
					$pdf->Cell(175,7,utf8_decode($parameter_list[1]),1,0,'L',false);
					$pdf->Ln();
				}*/
				//elseif (count ($parameter_list)==5){
				if (count ($parameter_list)==5){
								/*ob_start();
			var_dump(count ($parameter_list));
			$dumpy = ob_get_clean();
			\Drupal::logger('parfum')->notice('Le nombre de paramètres est :'.$dumpy);*/

			
					//$parameter_data =  array("1","Température (T)", "50 °C", "<60","Valeur conforme.");
					$pdf->SetFont('arial', '', 12);
					
					for($i=0;$i<count($parameter_list);$i++){
						if ($i==2 or $i==3 or $i==4 ){
							$pdf->Cell($w[$i],7,utf8_decode($parameter_list[$i]),0,0,'R',false);
						}
						else{
							$pdf->Cell($w[$i],7,utf8_decode('  '.$parameter_list[$i]),0,0,'L',false);
						}
					}
					$pdf->Ln();
				}
			}
			
			$w_totaux = array ('Sous Total H.T','Frais de Port H.T', 'TOTAL H.T');
			$w_fillcolor = array (255,248,230);
			
			for ($j=0;$j<3;$j++){
				
				if ($j==2){//Juste avant le total ht on mettra la mention.
					$pdf->SetFillColor(255,255,255);
					$pdf->SetX(60);
					$pdf->Cell(75,7,utf8_decode("TVA non applicable,article 293 B du CGI."),0,0,'L','F');
				}
				/*else{
					$pdf->SetX(140);
				}*/
				$pdf->SetX(140);
				$pdf->SetFillColor($w_fillcolor[$j],$w_fillcolor[$j],$w_fillcolor[$j]);
				/*$pdf->Cell(30,7,utf8_decode($w_totaux[$j]),0,0,'L','F');
				$pdf->Cell(35,7,utf8_decode($order [$w_totaux[$j]]),0,0,'R','F');*/
				$pdf->Cell(35,7,utf8_decode($w_totaux[$j]),0,0,'L','F');
				$pdf->Cell(30,7,utf8_decode($order [$w_totaux[$j]]),0,0,'R','F');
				$pdf->Ln();				

			}
			$table_height = 7 * count($order['lines']);
			
			$pdf->Rect(5,109,200,$table_height);
			$starting_position = 5;
			for($i=0;$i<count($header);$i++){
				$pdf->Rect($starting_position,109,$w[$i],$table_height);
				$starting_position += $w[$i];
			}
			/*$w = array(35, 100, 25, 20,20);
			$pdf->Rect(5,109,200,$table_height);*/
		}
		
		/*if (isset($order['sum'])){
			foreach ($order['sum'] as $key_l=> $value_l){
				$parameter_list = explode ('|',$value_l);
				if (count ($parameter_list)==2){
					
				}
				$pdf->SetFont('arial', 'B', 12);
				$pdf->Cell(10,7,$parameter_list[0],1,0,'C',false);
				$pdf->Cell(175,7,utf8_decode($parameter_list[1]),1,0,'L',false);
				$pdf->Ln();				
			}
		}*/
		
		$pdf->Ln();
		$pdf->SetFont('arial', '', 12);

		$pdf->MultiCell(190, 5, utf8_decode($order ['message']),0);
		$pdf->Ln();
		
		//$pdf->WriteHTML(utf8_decode($node->body['und'][0]['value']));
		
		$pdf->Ln();
		$pdf->Ln();
		
		
		

		//$file_name = "AE_" . ucfirst($societe_user->name).'_'.date ('d-m-Y', strtotime($node->field_period['und'][0]['value'])) . ".pdf";
		$file_name = $order ['file_name'];
		
		
		
		$file_path = 'sites/default/files/private/'.$order ['subdirectory'].'/'.$file_name;
		//$file_path = $file_name;
		
		$pdf->Output($file_path, "F");
			
		$file = file_save_data(file_get_contents($file_path), 'private://'.$order ['subdirectory'].'/'.$file_name,FILE_EXISTS_REPLACE );
		 
		
		$order_ui = \Drupal\commerce_order\Entity\Order::load($order ['uid']);
		//$order->get('field_facture')->setValue(set('target_id',$file->id());
		//$order_ui->get('field_facture')->set('target_id',$file->id());
		//$order->get('field_facture')->setValue('target_id');
		//$order->set("field_facture", new \Drupal\commerce_price\Price(strval ($value['PVR']), 'EUR'));
		//$order->set("field_facture", $file->id());
		//$order_ui->get('field_facture')->setValue('target_id');
		$order_ui->get('field_'.$order ['subdirectory'])->setValue($file->id());
		$order_ui->save();
		
		//$file->display = 1;
		//$file->uid = $node->uid;		
		//$node->field_rapport['und'][0] = (array)$file;
		
		return array(
		  '#type' => 'markup',
		  '#markup' => 'La facture est générée',
		);
		
	}

	
	
	public function send_engel_order ($order_number, $order_items,$address,$telephone){
		$token = $this->_webservice_login ();
		
		if (isset ($token)){
			$order_url = 'http://drop.novaengel.com/api/orders/send/'.$token ;
			$lines = array();
			foreach ($order_items as $key => $value){
				$variation = $value->getPurchasedEntity();
				$Units  = $value->getQuantity();
				$ProductId = $variation->get('field_engelid')->getValue()[0]["value"];
				$lines []= array("ProductId" => (int)$ProductId,"Units" => (int)$Units);
				//$ProductId = $variation->get('field_engelid')->getValue()[0];
				//$lines [] = $ProductId;
				
			}
			//ob_start();
			//var_dump($address);
			//$dumpy = ob_get_clean();
			//\Drupal::logger('parfum')->notice('Address information details:'.$dumpy);
			$OrderNumber = date ("ymdHis");
			$OrderNumber .=  strval ($order_number);
			
			$cedex= !empty($address["sorting_code"])?" Cedex ".$address["sorting_code"]:"";
			$Street = isset($address["address_line1"])?$address["address_line1"]:"";
			$Street .= isset($address["address_line2"])?'---'.$address["address_line2"]:"";
			$order_data = [array(
				"OrderNumber" => $OrderNumber, 
				"Valoration" => 0,
				"CarrierNotes" => "NA",
				"Lines" => $lines,
				"Name" => isset($address["family_name"])?$address["family_name"]:"NA",
				"SecondName" => isset($address["given_name"])?$address["given_name"]:"NA",
				"Telephone" => $telephone,
				"Mobile" => $telephone,
				"Street" => $Street,
				"City" => isset($address["locality"])?$address["locality"]:"NA",
				"County" => "France",
				"PostalCode" => isset($address["postal_code"])?$address["postal_code"].$cedex:"NA",
				"Country" => "FR"	
			)];			
			
		
			$url = $order_url;    
			$content = json_encode($order_data);

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER,
					array("Content-type: application/json"));
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

			$json_response = curl_exec($curl);

			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$this->_webservice_logout ($token);
			
			return ($OrderNumber);
		}
	}
	public function _detect_changed_stock ($product_list){
		
		$stock_status = $this->_get_stock_status();
		//dpm ($stock_status);
		$i = 0;
		
		$product_engel_indexes = array_keys ($product_list);
		$to_be_removed_articles = array();
		$to_be_updated_prices = array();
		while ($i < count ($stock_status) and count ($product_engel_indexes)>0){
			$key = array_search($stock_status[$i]['Id'], $product_engel_indexes); 
			if (is_numeric($key) ){
				//Vérifier si la quantité est disponible.
				if ($stock_status[$i]['Stock'] < $product_list [$product_engel_indexes[$key]]['qte']+$this->seuil_limite){
					$to_be_removed_articles [$product_engel_indexes[$key]] = $stock_status[$i]['Stock'] - $this->seuil_limite;
				}
				elseif ($stock_status[$i]['Price'] != $product_list [$product_engel_indexes[$key]]['engel_unit_price']){//Vérifier si le prix n'a pas changé
					$to_be_updated_prices [$product_engel_indexes[$key]] = $stock_status[$i]['Price'];
					
				}
				unset ($product_engel_indexes[$key]);
			}
			$i++;
		}
		return (array ('Stock' => $to_be_removed_articles, 'Price'=> $to_be_updated_prices ));
	}
	
	public function removeAllPerfumeExtraPrices($from, $offset,$use_case=1) {
		
		if ($use_case==1){//Getting Parfum Id to be removed

			$query = \Drupal::entityQuery('commerce_product')
			->condition('type',array('type_parfum'),'IN')
			->range($from, $offset);
			$nids = $query->execute();		

			$nids_list=0;
			$produits_parcourus=0;
			foreach ($nids as $key => $value){
				$produits_parcourus++;
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				//dpm ("Product will be inspected: ".$product->getTitle());
				$variations = $product->getVariations();
				
				foreach ($variations as $keyv => $valuev){
					$variation = $valuev;
					$variation_price = $variation->getPrice()->getNumber();
					
					if ($variation_price > $this->max_parfum_price or  $variation_price < $this->min_parfum_price){//Article hors intervalle prix.
						$my_title = $variation->getTitle();
						dpm ("Variation ".$my_title." will be removed: pice is:".$variation_price); 
						$nids_list++;
					}
				}
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Articles à supprimer:'.$nids_list,
			);
		}		
		elseif ($use_case==2){//Looping through articles and removing the ones that are too expensive or too cheap.

			$query = \Drupal::entityQuery('commerce_product')
			->condition('type',array('type_parfum'),'IN')
			->range($from, $offset);
			$nids = $query->execute();		

			$nids_list=0;
			$produits_parcourus=0;
			foreach ($nids as $key => $value){
				$produits_parcourus++;
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				//dpm ("Product will be inspected: ".$product->getTitle());
				$variations = $product->getVariations();
				
				foreach ($variations as $keyv => $valuev){
					$variation = $valuev;
					$variation_price = $variation->getPrice()->getNumber();
					
					if ($variation_price > $this->max_parfum_price or  $variation_price < $this->min_parfum_price){//Article hors intervalle prix.
						$my_title = $variation->getTitle();
						dpm ("Variation ".$my_title." will be removed: pice is:".$variation_price); 
						$product->removeVariation($valuev);
						$nids_list++;
					}
				}
				//If all variations were removed, we remove the product; else we just save it.
				$variations = $product->getVariations();
				if (count ($variations)==0){
					dpm ("Product is removed");
					$product->delete();
					$produits_parcourus++;
				}
				else{
					$product->save();
				}
			}		
		
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Articles supprimés: '.$produits_parcourus.'. Variations Supprimées:'.$nids_list,
			);
		}
	}
	
   public function _get_webservice_check_image ($pages = 10, $elements = 20){   
		$token = $this->_webservice_login ();

		if (isset ($token)){
			// Accès aux produits
			$collect_options = array(
				'http' => array(
					'method'  => 'GET',
				)
			);		
			
			$context = stream_context_create($collect_options);

			//10
			//http://drop.novaengel.com/api/products/paging/token/pages/elements/languaje
						
			$response = file_get_contents('http://drop.novaengel.com/api/products/paging/'.$token.'/'.$pages.'/'.$elements.'/fr', false, $context);
			$product_data_fr = json_decode($response, true);
			
			$response = file_get_contents('http://drop.novaengel.com/api/products/paging/'.$token.'/'.$pages.'/'.$elements.'/es', false, $context);
			$product_data_es = json_decode($response, true);
			
			$targetted_products = array (88620,74704,54875,77250,75150,93155,93031,93032,93033,93034,93035,93036,93037,93038,93040,93041,93042,93043,93044,93045,93046,93047,93048,93049,93049,93050,93051,67914,67928,67932,93025,74849,91556,91557,75329,75331,75333,75333,83242,83249,83253,83256,83260,83260,83261,83261,83263,83263,83269,83269,83279,83280,83281,83286,83290,83291,83297,83301,83304,83316,83317,83345,62464,62466,62468,62471,62472,63763,63764,63765,63767,63768,63048,63865,73979,37851,62791,81107,81108,83965,87427,64938);
			
			foreach ($product_data_fr as $key => $value){
				if (in_array ($value['Id'], $targetted_products)){
					dpm ('Case found');
					$image_url = $this->_get_product_image ($value['Id']);
					if (filter_var($image_url, FILTER_VALIDATE_URL)) {
						$image_data = file_get_contents($image_url);
						if ($image_data){
							\Drupal::logger('parfum')->notice('Succès téléchargement image:'.$value['Id'].'-'.$EANs.'-'.$value['Description']);							
						}
						else{
							\Drupal::logger('parfum')->notice('Impossible de télécharger l\'image :'.$value['Id'].'-'.$EANs.'-'.$value['Description']);
						}
					}
				}
				
			}
			//Logout
			$this->_webservice_logout ($token);			
		}
		return array(
		  '#type' => 'markup',
		  '#markup' => 
			"Vérficiation Image effectuées:".$pages."|".$elements,
		);
   }

	private function _webservice_login (){
		$url = 'http://drop.novaengel.com/api/login';		
		$data = array("user" => $this->user, "password" => $this->password);

		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$response = file_get_contents($url, false, $context);	
		$response_data = json_decode($response, true);


		//print_r ($response_data);
		if (isset ($response_data['Token'])){
			//\Drupal::logger('parfum')->notice('Access with token '.$response_data['Token']);
			return ($response_data['Token']);
		}
		else {
			return null;
		}
	}
	public function mon_test (){
		return (1);
	}
	private function _webservice_logout ($token){
		//Logout
		$url = 'http://drop.novaengel.com/api/logout/'.$token;
		$data =array();
		error_reporting(E_ALL ^ E_WARNING); 
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		//\Drupal::logger('parfum')->notice('Access logout with token '.$token.'.');
		$context  = stream_context_create($options);		
		file_get_contents($url, false, $context);	
		error_reporting(E_ALL);    		
		// create a new cURL resource
		/*$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// grab URL and pass it to the browser
		curl_exec($ch);

		// close cURL resource, and free up system resources
		curl_close($ch);*/

	}	
	private function _get_product_image ($product_id){
		$token = $this->_webservice_login ();
		$collect_options = array(
			'http' => array(
				'method'  => 'GET',
			)
		);		
		
		$context = stream_context_create($collect_options);		
		$response = file_get_contents('http://drop.novaengel.com/api/products/image/'.$token.'/'.$product_id, false, $context);
		$product_data = json_decode($response, true);
		$this->_webservice_logout ($token);
		return ($product_data);
	}
	
	
	
  /**
   * Page Callback Method to Print a summary output that will be used for translation issue.
   */
   public function _get_webservice_page_test ($pages = 10, $elements = 20){
		$token = $this->_webservice_login ();

		if (isset ($token)){
			// Accès aux produits
			$collect_options = array(
				'http' => array(
					'method'  => 'GET',
				)
			);		
			
			$context = stream_context_create($collect_options);

			//10
			//http://drop.novaengel.com/api/products/paging/token/pages/elements/languaje
						
			$response = file_get_contents('http://drop.novaengel.com/api/products/paging/'.$token.'/'.$pages.'/'.$elements.'/fr', false, $context);
			$product_data_fr = json_decode($response, true);
			
			$response = file_get_contents('http://drop.novaengel.com/api/products/paging/'.$token.'/'.$pages.'/'.$elements.'/es', false, $context);
			$product_data_es = json_decode($response, true);
			
			foreach ($product_data_fr as $key => $value){
				$Families = implode ('_',$value ['Families']);
				if ($value['Stock']>$this->seuil_limite and $value['PVR'] != 0){
					dpm ($value['Id'].'|'.$Families.'|'.$value['Description'].'|'.$product_data_es[$key]['Description']);
				}
			}
			//Logout
			$this->_webservice_logout ($token);			
		}
		return array(
		  '#type' => 'markup',
		  '#markup' => 
			"Comparaisons effectuées:".$pages."|".$elements,
		);
   }



   //Get stock status.
   public function _get_stock_status (){
		$token = $this->_webservice_login ();
		

		if (isset ($token)){
			// Accès aux produits
			$collect_options = array(
				'http' => array(
					'method'  => 'GET',
				)
			);		
			
			$context = stream_context_create($collect_options);

			//10
			//http://drop.novaengel.com/api/products/paging/token/pages/elements/languaje
						
			
			$response = file_get_contents('http://drop.novaengel.com/api/stock/update/'.$token, false, $context);
			$product_data = json_decode($response, true);

			//Logout
			$this->_webservice_logout ($token);			
			return ($product_data);
		}
		else{
			return (null);
		}
   }
   
  /**
   * Page Callback Method to Print out Hello World Text to the Screen.
   */
   private function _get_webservice_page ($pages = 10, $elements = 20){
		$token = $this->_webservice_login ();
		

		if (isset ($token)){
			// Accès aux produits
			$collect_options = array(
				'http' => array(
					'method'  => 'GET',
				)
			);		
			
			$context = stream_context_create($collect_options);

			//10
			//http://drop.novaengel.com/api/products/paging/token/pages/elements/languaje
						
			$response = file_get_contents('http://drop.novaengel.com/api/products/paging/'.$token.'/'.$pages.'/'.$elements.'/fr', false, $context);
			$product_data = json_decode($response, true);

			//Logout
			$this->_webservice_logout ($token);			
			return ($product_data);
		}
		else{
			return (null);
		}
   }
   
  private function _addMarque($engel_id, $libelle) {
	// Create node object with attached file.
	$node = Node::create([
	  'type'        => 'marques',
	  'title'       => $libelle,
	  /*'field_image' => [
		'target_id' => $file->GHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHGHG,
		'alt' => 'Hello world',
		'title' => 'Goodbye world'
	  ],*/
	  'field_identifiant_engel' => $engel_id,
	]);
	$node->save();
	
	return ( $node->id());
  }
	public function createTestVariation (){
		$use_case = 2;
		if ($use_case ==1){
			$store = \Drupal\commerce_store\Entity\Store::load(1);
			
			$variant_description = array (
				'title' => "Ma Première Variation",
				'type' => 'parfum',
				'sku' => 2802182358,
				'status' => TRUE,
				'price' => new \Drupal\commerce_price\Price(strval (50), 'EUR'),
				'attribute_volume_flacon' => 7 ,
				'field_engelid' => 280218,
				'field_marque2' => 124,
			);

			
			$variation = \Drupal\commerce_product\Entity\ProductVariation::create($variant_description);
			//$variation->save();
			
			$variations = [$variation,];
				
			$product = \Drupal\commerce_product\Entity\Product::create([
			  'uid' => 1,
			  'type' => 'type_parfum',
			  'title' => "Mon Premier Produit",
			  'stores' => [$store],
			  'variations' => $variations,
			  'field_engel_product_id' => 180219,					  
			  'field_article_reference' => $variation,
			]);
			
			$product->save();
			//$stores = $entity->getProduct()->getStores();
			
			//Ajout du second produit
			$variant_description2 = array (
				'title' => "Ma Deuxième Variation",
				'type' => 'parfum',
				'sku' => 2802182359,
				'status' => TRUE,
				'price' => new \Drupal\commerce_price\Price(strval (60), 'EUR'),
				'attribute_volume_flacon' => 8 ,
				'field_engelid' => 290219,
				'field_marque2' => 124,
			);
			$variation2 = \Drupal\commerce_product\Entity\ProductVariation::create($variant_description2);
			//$variation2->save();

			$query = \Drupal::entityQuery('commerce_product')
			->condition('type', 'type_parfum')
			->condition('field_engel_product_id', '180219');
			$nids = $query->execute();

			
			if (count ($nids)==1){
				$product_id = reset ($nids);
			
				$product = \Drupal\commerce_product\Entity\Product::load($product_id);
				$product->variations[]= $variation2;
				$product->save();
			}
			else{
				$product_id = 0;
			}			
			return array(
				'#type' => 'markup',
				'#markup' => "Produit de test ajouté:".$product_id,
			);			
		}
		elseif ($use_case == 2){		
			$result = \Drupal::entityQuery('commerce_product')
				->condition('type', 'type_parfum')
				->execute();
				
			//$result = implode (" ",$result);
			foreach ($result as $resultk => $resultv){
				$variant_description2 = array (
					'title' => "Ma Deuxième Variation",
					'type' => 'parfum',
					'sku' => $resultv,
					'status' => TRUE,
					'price' => new \Drupal\commerce_price\Price(strval (60), 'EUR'),
					'attribute_volume_flacon' => 8 ,
					'field_engelid' => 290219,
					'field_marque2' => 124,
				);
				$variation2 = \Drupal\commerce_product\Entity\ProductVariation::create($variant_description2);
				$variation2->save();
			
				$product = \Drupal\commerce_product\Entity\Product::load($resultv);
				$product->addVariation($variation2);
				//$product->save();
				/*$product->variations[]= $variation2;
				$product->save();				*/
			}
			
			return array(
				'#type' => 'markup',
				'#markup' => "Liste des projets: ".$result,
			);
			
		}


	}

	public function removeAllExceptPerfume($from, $offset,$use_case=1) {
		
		if ($use_case==1){//Getting Marques Id

			$query = \Drupal::entityQuery('commerce_product')
			//->condition('status', 1)
			//('entity_id', array(17, 21,422), 'IN')
			//('entity_id', array(17, 21,422), 'IN')
			->condition('type',array('type_cosmetique','type_hygiene','type_maquillage','type_soins_corporels','type_soins_cheveux','type_soins_visage','type_solaire'),'IN')
			//->condition('type','type_cosmetique')
			->range($from, $offset);
			$nids = $query->execute();		

			$nids_list=0;
			$produits_parcourus=0;
			foreach ($nids as $key => $value){
				$produits_parcourus++;
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				dpm ("Product will be deleted: ".$product->getTitle());
				//$product->delete();
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Articles extraits: '.$produits_parcourus,
			);
		}
		elseif ($use_case==2){//Getting Marques Id

			$query = \Drupal::entityQuery('commerce_product')
			//->condition('status', 1)
			//('entity_id', array(17, 21,422), 'IN')
			//('entity_id', array(17, 21,422), 'IN')
			//->condition('type',array('type_cosmetique','type_hygiene','type_maquillage','type_soins_corporels','type_soins_cheveux','type_soins_visage','type_solaire'),'IN')
			//->condition('type','type_cosmetique')
			->range($from, $offset);
			$nids = $query->execute();		

			$nids_list=0;
			$produits_parcourus=0;
			foreach ($nids as $key => $value){
				$produits_parcourus++;
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				dpm ("Product will be deleted: ".$product->getTitle());
				$product->delete();
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Articles extraits: '.$produits_parcourus,
			);
		}
	}
	
	public function managing_Marques($from, $to,$use_case=1) {
		
		if ($use_case==1){//Getting Marques Id
			$marques_liste = array (
				'Abercrombie & Fitch',
				'Abril Et Nature',
				'Acca Kappa',
				'Agrocosmetic',
				'Aigner Parfums',
				'Aina De Mô',
				'Air-wick',
				'Aire Sevilla',
				'Alaïa',
				'Alexandre Cosmetics',
				'Algasiv',
				'All Sins 18k',
				'Alvarez Gomez',
				'Alyssa Ashley',
				'Ambi Pur',
				'American Crew',
				'Angel Schlesser',
				'Anne Möller',
				'Antidotpro',
				'Aquolina',
				'Arganour',
				'Armand Basi',
				'Artdeco',
				'Artero',
				'Aseptine',
				'Astor',
				'Botanicals',
				'Brillantina Profesional',
				'Camomila Intea',
				'Crusellas',
				'Dunhill',
				'Eau Jeune',
				'El Niño',
				'Emanuel Ungaro',
				'Ermenegildo Zegna',
				'Flor De Almendro',
				'Globo',
				'Gold Tree Barcelona',
				'Gorjuss',
				'Grafic',
				'Heno De Pravia',
				'Herra',
				'Imedia',
				'Innoatek',
				'Instituto Español',
				'Jesus Castro',
				'Jesus Del Pozo',
				'Karicia',
				'Kemphor',
				'Maderas',
				'Magno',
				'Maja',
				'Miniaturas',
				'Monster High',
				'Monster High',
				'Mosser',
				'Moussel',
				'Nasomatto',
				'Pood',
				'Posseidon',
				'Pranarôm',
				'Puig',
				'S3',
				'Sabien',
				'Salerm',
				'Shunga',
				'Tabac',
				'Tot Herba',
				'Tragoncete',
				'Tulipán Negro',
				'Valmont',
				'Verdimill',
				'Victor',
				'Vida',
				'Vitesse',
				'Voland Nature',
			);
			//Récupérer les marques.
			$query = \Drupal::entityQuery('node')
				->condition('status', 1)
				->condition('type', 'marques');
			$nids = $query->execute();	
			$nodes = entity_load_multiple('node', $nids);
			
			$nids_list=0;
			$marques_disabled_list = "";
			foreach ($nodes as $nd_key => $nd_value){
				$nid = $nd_value->get('nid')->getValue()[0]['value'];
				
				$title = $nd_value->getTitle();
				if (in_array($title, $marques_liste)){
					dpm ("I have found: ".$title);
					$field_identifiant_engel = $nd_value->get('field_identifiant_engel')->getValue()[0]['value'];
					dpm ("Engel id of ".$title." is :".$field_identifiant_engel);
					$marques_disabled_list .= ','.$title;
					$nids_list++;
					$nd_value->set('status',0);
					$nd_value->save();
				}
				/*else{
					dpm ("Not found:".$title);
					
				}*/
				
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Articles extraits: '.$nids_list." : ".$marques_disabled_list,
			);
		}
		 
		elseif ($use_case==2){//Removing products whose vendor is disbaled.
			//Récupérer les marques désactivées.
			$query = \Drupal::entityQuery('node')
				->condition('status', 0)
				->condition('type', 'marques');
			$marques_nids = $query->execute();	
			$marques_nodes = entity_load_multiple('node', $marques_nids);
			

			$marques_list = array();
			foreach ($marques_nodes as $nd_key => $nd_value){
				//$marques_list [] = $nd_value->get('field_identifiant_engel')->getValue()[0]['value'];
				$marques_list [] = $nd_value->get('nid')->getValue()[0]['value'];
			}
			//dpm ($marques_list);
			//#Récupérer les marques désactivées.
			
			
			$query = \Drupal::entityQuery('commerce_product')
			->condition('status', 1)
			->range($from, $to);
			$nids = $query->execute();
			
			$nids_list=0;
			$produits_parcourus=0;
			foreach ($nids as $key => $value){
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				$variations = $product->getVariations();
				//dpm ("We are in proudct: ".$product->getTitle());
				$produits_parcourus++;
				foreach ($variations as $keyv => $valuev){
					$variation = $valuev;
					$field_marque2 = $variation->get("field_marque2")->getValue()[0]['target_id'];
					if (in_array($field_marque2, $marques_list)){
						$my_title = $variation->getTitle();
						dpm ("Variation ".$my_title." is removed."); 
						$product->removeVariation($valuev);
						$nids_list++;
					}
					//else{
					//	$my_title = $variation->getTitle();
					//	dpm ("Variation ".$my_title." (".$field_marque2.") is kept.");
					//}
				}
				
				//If all variations were removed, we remove the product; else we just save it.
				$variations = $product->getVariations();
				if (count ($variations)==0){
					dpm ("Product is removed");
					$product->delete();
				}
				else{
					$product->save();
				}
				
			}
			//dpm ($nids);
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Produits parcourus: '.$produits_parcourus.'.Articles supprimés: '.$nids_list,
			);			
		}
	}


	
	public function checkVariationCriteria($from, $to, $use_case=1) {
		if ($use_case==1){
			$query = \Drupal::entityQuery('commerce_product')
			->condition('status', 1)
			->range($from, $to);
			$nids = $query->execute();
			
			$nids_list = implode (' - ',$nids );
			$i=0;
			
			/*ob_start();
			var_dump($nids);
			$dumpy = ob_get_clean();
			\Drupal::logger('parfum')->notice('Check Node:'.$dumpy);*/
			
			$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
			$list_of_values = $size_attribute->getValues();
			foreach ($list_of_values as $key => $value){
				$volume_array [$key]= $value->getName();
			}
			//dpm ($volume_array);

			foreach ($nids as $key => $value){
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				/*if ($i==0){
					ob_start();
					var_dump($product);
					$dumpy = ob_get_clean();
					\Drupal::logger('parfum')->notice('Check Variation:'.$dumpy);				
					$i++;
				}*/

				//$title = $product->get('field_engel_product_id');
				$title = $product->getTitle();
				$variations = $product->getVariations();
				//\Drupal::logger('parfum')->notice('Product Title:'.$title);
				
				
				//$variations = $product->variations;
				if (count ($variations)>1){
					//if ($i==0){
					dpm ("New Case");
					foreach ($variations as $keyv => $valuev){
						//$variation = $variations[0];
						$variation = $valuev;
						$i++;
						//$my_price = $variations[0]->getPrice()->getNumber();
						$my_title = $variation->getTitle();
						$transformed_title = $this->_explode_title($my_title);
						//dpm ($variation_structure);
						//dpm ($transformed_title);
						$transformed_title_list = implode ('|',$transformed_title);
						dpm ($my_title."**".$transformed_title_list);
						//\Drupal::logger('parfum')->notice('Mon Titre:'.$transformed_title_list);
						
						if ($transformed_title[2] != ""){//Une variation a été exraite
							$index = array_search($transformed_title[2], $volume_array); 
							if ($index > 0){
								$attribute_volume_flacon = $index;
							}
							else{					
								//Ajout de l'attribut manquant dans la liste des volumes
								
								$volume_attribut = \Drupal\commerce_product\Entity\ProductAttributeValue::create([
								  'attribute' => 'volume_flacon',
								  'name' => $transformed_title[2],
								]);
								$volume_attribut->save();
								$attribute_volume_flacon= $volume_attribut->id();
								$volume_array[$attribute_volume_flacon] = $transformed_title[2];
							}
						}
					}				
				}
				return array(
				  '#type' => 'markup',
				  '#markup' => 'Articles extraits: '.$nids_list,
				);			
			}
		
		}		
		elseif ($use_case == 2){//Unification des doublons
			$query = \Drupal::entityQuery('commerce_product')
			->condition('status', 1)
			->range($from, $to);
			$nids = $query->execute();
			
			$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
			$list_of_values = $size_attribute->getValues();
			$volume_array_unique = array();
			$volume_array = array();
			foreach ($list_of_values as $key => $value){				
				$value_getName = $value->getName();
				
				if (!in_array($value_getName, $volume_array_unique )){//Elimination des doublons
					$volume_array_unique [$key]= $value_getName;
				}
				$volume_array [$key]= $value_getName;
			}
			dpm ("Tableaux flacon");
			dpm ($volume_array_unique);
			dpm ($volume_array);
			
			foreach ($nids as $key => $value){
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				$variations = $product->getVariations();
				foreach ($variations as $keyv => $valuev){
					$variation = $valuev;
					//$attribute_volume_flacon = $variation->get("attribute_volume_flacon");
					
					$attribute_volume_flacon = $variation->get("attribute_volume_flacon")->getValue()[0]['target_id'];
					$unique_index = array_search($volume_array[$attribute_volume_flacon], $volume_array_unique);
					$my_title = $variation->getTitle();
					
					if ($attribute_volume_flacon != $unique_index){
						dpm ("Here we are:".$my_title."|".$attribute_volume_flacon);
						
						$subject = $my_title;
						$pattern = '/(ml)$/u';
						$matches = array();
						preg_match($pattern, $subject, $matches );							
						//if (count ($matches)>0 and -1>1){
						if (count ($matches)>0 or 1){	
							$attribute_volume_flacon = array (array('target_id' => $unique_index));
							$variation->get("attribute_volume_flacon")->setValue($attribute_volume_flacon);
							$new_index = $variation->get("attribute_volume_flacon")->getValue()[0]['target_id'];
							dpm ('After:'.$new_index);
							$variation->save();
							//$product->setVariations($variations);
						}
						//dpm ($attribute_volume_flacon.'|'.$volume_array[$attribute_volume_flacon].'|'.array_search($volume_array[$attribute_volume_flacon], $volume_array_unique));						
					}

				}
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Traitement finalisé.',
			);			
		}
	
		elseif ($use_case ==3){
			$query = \Drupal::entityQuery('commerce_product')
			->condition('status', 1)
			->range($from, $to);
			$nids = $query->execute();
			
			
			$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
			$list_of_values = $size_attribute->getValues();
			foreach ($nids as $key => $value){
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				$variations = $product->getVariations();
				foreach ($variations as $keyv => $valuev){
					$variation = $valuev;
					$my_title = $variation->getTitle();
					$subject = $my_title;
					$pattern = '/(ml)$/u';
					$matches = array();
					preg_match($pattern, $subject, $matches );
					
					if (count ($matches)>0){
						$attribute_volume_flacon = $variation->get("attribute_volume_flacon")->getValue()[0]['target_id'];
						//dpm ("check if to be changed: ".$my_title);
						//$tmp_volume = $list_of_values [$attribute_volume_flacon]->getName();
						 $red = \Drupal\commerce_product\Entity\ProductAttributeValue::load($attribute_volume_flacon);
						 $matches_attribute2 = array();
						 preg_match($pattern, $red->getName(), $matches_attribute2 );
						 if (count ($matches_attribute2)<=0){
							 dpm ("change is confirmed for : ".$my_title);
							 $red->setName($red->getName().' ml');
							 $red->save();							 
						 }
						 else{
							// dpm ("change is already done for : ".$red->getName());
						 }

						 //dpm ($red);
						 //$red->setValue($tmp_volume.' ml');

						//$size_attribute->get($attribute_volume_flacon)->setValue($tmp_volume.' ml');
						//$size_attribute->save();
					}
				}
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Traitement finalisé.',
			);						
		}
		elseif ($use_case ==4){
			$query = \Drupal::entityQuery('commerce_product')
			->condition('status', 1)
			->range($from, $to);
			$nids = $query->execute();
			
			
			$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
			$list_of_values = $size_attribute->getValues();
			foreach ($nids as $key => $value){
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				$variations = $product->getVariations();
				if (count ($variations)>1){
					dpm ("Multivariations for ".$value);
					foreach ($variations as $keyv => $valuev){
						$variation = $valuev;
						$my_title = $variation->getTitle();
						dpm ($my_title);
						/*$subject = $my_title;
						$pattern = '/(ml)$/u';
						$matches = array();
						preg_match($pattern, $subject, $matches );
						
						if (count ($matches)>0){
							$attribute_volume_flacon = $variation->get("attribute_volume_flacon")->getValue()[0]['target_id'];
							//dpm ("check if to be changed: ".$my_title);
							//$tmp_volume = $list_of_values [$attribute_volume_flacon]->getName();
							 $red = \Drupal\commerce_product\Entity\ProductAttributeValue::load($attribute_volume_flacon);
							 $matches_attribute2 = array();
							 preg_match($pattern, $red->getName(), $matches_attribute2 );
							 if (count ($matches_attribute2)<=0){
								 dpm ("change is confirmed for : ".$my_title);
								 $red->setName($red->getName().' ml');
								 $red->save();							 
							 }
							 else{
								// dpm ("change is already done for : ".$red->getName());
							 }

							 //dpm ($red);
							 //$red->setValue($tmp_volume.' ml');

							//$size_attribute->get($attribute_volume_flacon)->setValue($tmp_volume.' ml');
							//$size_attribute->save();
						}*/
					}
				}

			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Traitement finalisé.',
			);						
		}
		elseif ($use_case ==5){//Mise à jour des variations
			$query = \Drupal::entityQuery('commerce_product')
			->condition('status', 1)
			->range($from, $to);
			$nids = $query->execute();			
			
			foreach ($nids as $key => $value){
				$product = \Drupal\commerce_product\Entity\Product::load($value);
				$variations = $product->getVariations();
				if (count ($variations)>1){
					dpm ("Multivariations for ".$value);			
					$flag1_marque = true; $tableau_marque = array();
					$flag2_quantite = false; $tableau_quantite = array();
					$flag3_spec = true; $tableau_spec = array();
					foreach ($variations as $keyv => $valuev){
						$variation = $valuev;
						$my_title = $variation->getTitle();
						dpm ("Original Title: ".$my_title);

						$transformed_title = $this->_explode_title($variation->getTitle());
						if ($value=="3911"){
							dpm ("Trans title");
							dpm ($transformed_title);
						}						
						if ($flag1_marque == true and count ($tableau_marque)>0){
							$flag1_marque = in_array ($transformed_title[0],$tableau_marque);

						}
						if ($flag1_marque == false){
							dpm ("La marque change".$transformed_title[1]);
						}
						$tableau_marque [] = $transformed_title [0];

						if ($flag2_quantite == false  and count ($tableau_quantite)>0){
							$flag2_quantite = in_array ($transformed_title[2],$tableau_quantite);
						}
						$tableau_quantite [] = $transformed_title [2];

						if ($flag3_spec == true  and count ($tableau_spec)>0){
							$flag3_spec = in_array ($transformed_title[1],$tableau_spec);
						}
						$tableau_spec [] = $transformed_title [1];
					}
					dpm ("Mise a jour des transformations");
					dpm ($tableau_marque );
					dpm ($tableau_quantite );
					dpm ($tableau_spec );


					if ($flag1_marque == true){
						dpm ("Flag marque");
						if ($flag2_quantite == false){
							dpm ("Flag qt");
							$this->_update_variation ($variations,$tableau_quantite );
						}
						elseif ($flag3_spec == false){
							$this->_update_variation ($variations,$tableau_spec );
						}
					}
				}
			}
			return array(
			  '#type' => 'markup',
			  '#markup' => 'Traitement finalisé.',
			);			
		}
	}

private function _update_variation (&$variations,$tableau_critere){
	dpm ("Intégration de la variation");
	$vvv = 0;
	$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
	$list_of_values = $size_attribute->getValues();
	foreach ($list_of_values as $key => $value){				
		$value_getName = $value->getName();
		$volume_array [$key]= $value_getName;
	}

	foreach ($variations as $keyv => $valuev){
		if ($tableau_critere[$vvv] !=""){
			dpm ("Recherche critere:". $tableau_critere[$vvv]);
			$index = array_search($tableau_critere[$vvv], $volume_array);
			if ($index <= 0){
				dpm ("Critere not found, ajout dans les attributs");
				$volume_attribut = \Drupal\commerce_product\Entity\ProductAttributeValue::create([
				  'attribute' => 'volume_flacon',
				  'name' => $tableau_critere[$vvv],
				]);
				dpm ("Le critere suivant sera ajouté: ".$tableau_critere[$vvv]);
				$volume_attribut->save();
				$index = $volume_attribut->id();
				$volume_array[$index] = $tableau_critere[$vvv];
			}
			else{
				dpm ("Le critere:". $tableau_critere[$vvv]." a été retrouvé en posisiton ".$index);
			}
			$valuev->get("attribute_volume_flacon")->setValue($index);
			$valuev->save();
		}
		$vvv++;
	}
}
	
	private function _explode_title($subject){
		//$subject = "INTENSIVE CARE aloe calmante loción 400 ml";
		//$pattern = '/(?:[[:upper:]]+\s)+/u';
		//$pattern = '/^(?:[[:upper:]]+(\s)*[[:digit:]]*)+/u';
		//$pattern = '/^(?:[[:upper:]]+(&)?[[:upper:]]+(\s)*[[:digit:]]*)+/u';
		//$pattern = '/^(?:([[:upper:]]|(&)|(\s)|[[:digit:]]))+/u';
		//$pattern = '/^(?:([[:upper:]]|(&|!)|(\s)|[[:digit:]]))+/u';
		$pattern = '/^(?:([[:upper:]]|(&|!|\')|(\s)|[[:digit:]]))+/u';

		//$quantity = '/(?:[[:digit:]]+(,[[:digit:]]+)?\s)[[:lower:]]+$/u';
		//$quantity = '/(?:[[:digit:]]+((,|\.)[[:digit:]]+)?\s?)[[:lower:]]+$/u';
		//$quantity = '/(?:[[:digit:]]+((,|\.)[[:digit:]]+)?(\s|\+)*)+[[:lower:]]+$/u';
		$quantity = '/(?:([[:digit:]]+((,|\.)[[:digit:]]+)?(\s)?([[:lower:]]{2,3}|\%)?(\s)?(\+)?(\s)?)+)$/u';
		//$quantity_value = '/^(?:[[:digit:]]+((,|\.)[[:digit:]]+)?)/u';
		//$quantity_value = '/^(?:[[:digit:]]+((,|\.)[[:digit:]]+)?(\s|\+)*)+)?)/u';
		//$quantity_unit = '/(?:[[:lower:]]+$)/u';		
		$quantity_value  = '/^(?:([[:digit:]]+((,|\.)[[:digit:]]+)?(\s)?)+)/u';
		$quantity_unit = '/(?:[[:lower:]]{2,3}$)/u';
		
		
		//$subject = str_replace("´", " ", $subject);
		//$subject = str_replace("/^&/", "", $subject);

		preg_match($pattern, $subject, $matches );
		preg_match($quantity, $subject, $qte );

		if (isset ($matches[0])){
			$result = str_replace($matches[0], "", $subject);
		}
		
		
		if (isset ($qte[0]) and isset ($result) ){
			$result = str_replace($qte[0], "", $result);
			preg_match ($quantity_value, $qte[0], $qte_val );
			preg_match ($quantity_unit, $qte[0], $qte_unit );
			preg_match ('/\+/', $qte[0], $plus ); 
			if (isset ($qte_val[0]) and isset ($qte_unit[0]) and !isset($plus[0])){				
				$qte[0] = rtrim($qte_val[0])." ".rtrim($qte_unit[0]);
			}
			
			
		}


		/*echo "Model name: ".$matches[0]."</br>";
		echo "Quantity: ".$qte[0]."</br>";
		echo "Variations: ".utf8_decode($result);*/
		
		$variation_structure = array();
		if (isset($matches[0])){
			$variation_structure [] = rtrim ($matches[0]);
		}
		else{
			
			$variation_structure [] = "";
		}

		if (isset($result)){
			//$variation_structure [] = utf8_decode($result);
			$variation_structure [] = rtrim ($result);
		}
		else{
			$variation_structure [] = "";
		}

		if (isset($qte[0])){
			$variation_structure [] = rtrim($qte[0]);
		}
		else{
			$variation_structure [] = "";
		}
		//\Drupal::logger('parfum')->notice('Mon Titre2:'.utf8_decode($result));
		return ($variation_structure);	
	}
	public function get_order_status($order_id, $getCreatedTime) {

		date_default_timezone_set('Europe/Berlin');
		$OrderNumber = date ("ymdHis", $getCreatedTime).$order_id;
		$TrackingURL = "https://parfumstreet.fr";

		$token = $this->_webservice_login ();		
		if (isset ($token)){
			$date_debut= date('Y-n-j', strtotime ( '-1 day', $getCreatedTime));
			$date_fin= date('Y-n-j', strtotime ( '+5 week', $getCreatedTime));			
			$response = file_get_contents('http://drop.novaengel.com/api/orders/betweendates/'.$token.'/'.$date_debut.'/'.$date_fin, false, $context);
			$order_status_data = json_decode($response, true);			
			foreach ($order_status_data as $key => $value){
				if ( $value['OrderNumber'] == $OrderNumber ){
				//if ( $value['OrderNumber'] == '18050917234556' ){					
					$TrackingURL = $value['SendInfo']['TrackingURL'];
				}
			}
			$this->_webservice_logout ($token);
		}
		return($TrackingURL);

		//ob_start();
		//var_dump($order_status_data);
		//$dumpy = ob_get_clean();				
		//\Drupal::logger('parfum')->notice('order_status_data: '.$dumpy);		
		
			
		/*return array(
		  '#type' => 'markup',
		  '#markup' => 'Date de creation: '.$TrackingURL.'|'.$date_debut.'|'.$date_fin
		);*/		
	}
	public function removeAllNode($article_type) {	
	
		/*$query = \Drupal::entityQuery('commerce_product')
		->condition('type', 'type_parfum')
		->condition('field_engel_product_id', '180219');
		$nids = $query->execute();
		
		$query = \Drupal::entityQuery('commerce_product')
		->condition('type', 'type_parfum')
		->condition('field_engel_product_id', '180219');
		$nids = $query->execute();
		
		
		//Recherche 
		$query = \Drupal::entityQuery('commerce_product_variation')
		->condition('type', $varation_bundle)
		->condition('field_engelid', $value['Id']);
		$vid = $query->execute();*/
		
		$usecase = -1  ;
		if ($usecase == 1){			
			$result  = array();
			$result = \Drupal::entityQuery('commerce_product_variation')
				->condition('type', $article_type)
				->range(0, 200)
				->execute();
			foreach ($result as $key_res => $value_res){
				$variation = $reference_variation = \Drupal\commerce_product\Entity\ProductVariation::load($value_res);
				$variation->delete();
				//dpm ($value_res);
			}
		}
		

		/*$variation = \Drupal::entityQuery('commerce_product_variation')
			->condition('type', 'parfum')
			->range(0, 50)
			->execute();
		entity_delete_multiple('commerce_product_variation', $variation);	*/
		elseif ($usecase==2){
			$result = \Drupal::entityQuery('commerce_product')
				->condition('type', 'type_parfum')
				->range(0, 50)
				->execute();
			foreach ($result as $key_res => $value_res){
				$product = \Drupal\commerce_product\Entity\Product::load($value_res);
				//dpm ($value_res);
				$product->delete();			
			}			
		}

		//entity_delete_multiple('commerce_product', $result);
	return array(
      '#type' => 'markup',
      '#markup' => count($result).' articles supprimés.'
    );
		
	}

	public function update_parfum_articles($page=12, $product_quantity=500) {		  
		//Récupérer les marques.
		$query = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', 'marques');
		$nids = $query->execute();	
		$nodes = entity_load_multiple('node', $nids);
		$marques_list =array();
		foreach ($nodes as $nd_key => $nd_value){
			$nid = $nd_value->get('nid')->getValue()[0]['value'];
			$field_identifiant_engel = $nd_value->get('field_identifiant_engel')->getValue()[0]['value'];
			$marques_list[$nid] = $field_identifiant_engel;
		}
		//#Récupérer les marques.
		
		
		//$size_attribute = 20;
		$product_list = $this->_get_webservice_page ($page, $product_quantity);//360/20=18 => 360/40=9;
		
		//dpm ($product_list);
		$i=0;
		$j=0;//Articles non ajoutés car hors prix.
		$k=0;//Articles non ajoutés car hors stock.
		$produit_hors_stock=0;
		$produit_hors_categories=0;
		$free_product = 0;
		$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
		$list_of_values = $size_attribute->getValues();
		foreach ($list_of_values as $key => $value){
			$volume_array [$key]= $value->getName();
		}


		$total_par_page = count ($product_list);
		foreach ($product_list as $key => $value){
			if ($value['PVR']<= $this->max_parfum_price and $value['PVR'] >= $this->min_parfum_price and $value['Stock'] > $this->seuil_limite and in_array ('Parfums',$value ['Families'] ) and $value['PVR'] != 0){
				$this->integration_article ($value,$volume_array,$marques_list);
				$i++;
			}
			else{
				if ($value['PVR']> $this->max_parfum_price or $value['PVR'] < $this->min_parfum_price  and in_array ('Parfums',$value ['Families'] ) and $value['PVR'] != 0){
					$j++;
				}
				if ($value['Stock'] > $this->seuil_limite and  $value['PVR'] != 0){
					$k++;
				}
			}
			
			
			//Diminuer la taille du tableau pour libérer la mémoire
			unset ($product_list [$key]);
		}
				
		return array(
		  '#type' => 'markup',
		  '#markup' => 
			"Articles parcourus:".$total_par_page . 
			'|Articles parfums ajoutés:'.$i. 
			'|Articles parfums hors prix:'.$j. 
			'|Articles parfums hors stock:'.$k 
		);
	}	
  //Structure de l'appel /{page}/{product_quantity}
  public function myCallbackMethod($page=12, $product_quantity=500) {
	  
	//Récupérer les marques.
	$query = \Drupal::entityQuery('node')
		->condition('status', 1)
		->condition('type', 'marques');
	$nids = $query->execute();	
	$nodes = entity_load_multiple('node', $nids);
	$marques_list =array();
	foreach ($nodes as $nd_key => $nd_value){
		$nid = $nd_value->get('nid')->getValue()[0]['value'];
		$field_identifiant_engel = $nd_value->get('field_identifiant_engel')->getValue()[0]['value'];
		$marques_list[$nid] = $field_identifiant_engel;
	}
	//#Récupérer les marques.
	
	
	//$size_attribute = 20;
	$product_list = $this->_get_webservice_page ($page, $product_quantity);//360/20=18 => 360/40=9;
	//dpm ($product_list);
	$unkown=0;$i=0;$j=0;$k=0;$l=0;$m=0;$n=0;$o=0;$p=0;
	$produit_hors_stock=0;
	$produit_hors_categories=0;
	$free_product = 0;
	$size_attribute = \Drupal\commerce_product\Entity\ProductAttribute::load('volume_flacon');
	$list_of_values = $size_attribute->getValues();
	foreach ($list_of_values as $key => $value){
		$volume_array [$key]= $value->getName();
	}

	 
	//$my_product = \Drupal\commerce_product\Entity\Product::load();
	//$chekc_product = \Drupal\commerce_product\Entity\ProductType::load('type_parfum');
	//dpm ($my_product);

	
		
	/*$foo = \Drupal::entityManager()->getFieldMap();
	dpm ($foo);		*/
		
		

	//dpm ($product_list );
	$hors_categorie = array ();
	$total_par_page = count ($product_list);
	foreach ($product_list as $key => $value){

		//if ($value['Stock']>0 and in_array ('Parfum',$value ['Families']) and ($value['Id'] =='93287' or $value['Id'] =='93288')){
		//if ($value['Stock'] > $this->seuil_limite and in_array ('Parfum',$value ['Families'] and -1 >0)){
		if ($value['Stock'] > $this->seuil_limite and in_array ('Parfums',$value ['Families'] ) and $value['PVR'] != 0){
			/*$foo = \Drupal::entityManager()->getFieldDefinitions($varaition_entity, $varation_bundle);
			dpm ($foo);*/
			
			//\Drupal::logger('parfum')->notice('Identifiant Engel'.$value['Id']);
		
			$this->integration_article ($value,$volume_array,$marques_list);
			$i++;
		}
		elseif ($value['Stock'] > $this->seuil_limite and in_array ('Cosmétique',$value ['Families'] ) and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);			
			$j++;		
		}
		elseif ($value['Stock'] > $this->seuil_limite and in_array ('Hygiène',$value ['Families'] ) and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);			
			$k++;		
		}
		elseif ($value['Stock'] > $this->seuil_limite and (in_array ('Corporal',$value ['Families'] ) or in_array ('Cosmétique Corps',$value ['Families'] ))  and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);
			
			$l++;
		}
		elseif ($value['Stock'] > $this->seuil_limite and in_array ('Cheveux',$value ['Families'] ) and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);
			
			$m++;
		}
		elseif ($value['Stock'] > $this->seuil_limite and (in_array ('Facial',$value ['Families'] ) or  in_array ('Cosmétique Visage',$value ['Families'] )) and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);
			
			$n++;
		}
		elseif ($value['Stock'] > $this->seuil_limite and in_array ('Maquillage',$value ['Families'] ) and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);
			
			$o++;
		}
		elseif ($value['Stock'] > $this->seuil_limite and in_array ('Solaires',$value ['Families'] ) and $value['PVR'] != 0){
			//dpm ($value);
			$traitement = 1;
			$this->integration_article ($value,$volume_array,$marques_list);
			
			$p++;
		}
		else{
			$categorized = false;
			if ($value['Stock']<=  $this->seuil_limite){
				$produit_hors_stock++;
				$categorized = true;
			}
			if (
				!in_array ('Parfums',$value ['Families']) and 
				!in_array ('Cosmétique',$value ['Families']) and
				!in_array ('Hygiène',$value ['Families']) and 
				!in_array ('Facial',$value ['Families']) and 
				!in_array ('Cosmétique Visage',$value ['Families']) and 
				!in_array ('Cheveux',$value ['Families']) and 
				!in_array ('Maquillage',$value ['Families']) and 
				!in_array ('Solaires',$value ['Families']) and 
				!in_array ('Cosmétique Corps',$value ['Families']) and 				
				!in_array ('Corporal',$value ['Families'])){
				$produit_hors_categories++;
				$hors_categorie [] = $value;
				$categorized = true;
			}
			if ($value['PVR'] == 0){
				$free_product ++;
				$categorized = true;
			}
			if (! $categorized){
				$unkown++;
			}
		}
		//Diminuer la taille du tableau pour libérer la mémoire
		unset ($product_list [$key]);
	}
	if (count($hors_categorie)>0){
		dpm ($hors_categorie);
	}
    
	return array(
      '#type' => 'markup',
      '#markup' => 
		"Articles parcourus:".$total_par_page . 
		'|Articles parfums ajoutés:'.$i.
		'|Articles cosmétiques:'.$j.
		'|Hors stock:'.$produit_hors_stock.
		'|Hors catégories:'.$produit_hors_categories.
		'|Articles gratuits:'.$free_product.
		'|Articles hygiène:'.$k.
		'|Articles soins corporels:'.$l.
		'|Articles soins des cheveux:'.$m.
		'|Articles soins du visage:'.$n.
		'|Articles maquillage:'.$o.
		'|Articles solaires:'.$p.
		'|Articles inconnu:'.$unkown,
		
	  /*'}produit_hors_stock' => $produit_hors_stock,
	  '}produit_hors_categories' => $produit_hors_categories,
	  '}added' => $i,*/
	  
	  //'#markup' => ' produits ajoutés sur 0',
    );
    /*return array(
      '#theme' => 'parfum_primary_page',
      '#items' => 100,
    );*/
}
//Renvoie l'intersection de deux chaines.
private function _string_intersection ($first_word , $second_word ){
	//dpm ($first_word);
	
	$separate_sous_categorie = preg_split("/ - /",$first_word);
	if (isset ($separate_sous_categorie [1])){
		$first_word = $separate_sous_categorie [0];	
	}
	//\Drupal::logger('parfum')->notice('Intersection1 :'.$first_word);
//\Drupal::logger('parfum')->notice('Intersection2 :'.$second_word);	
	
	$common_word ="";
	$common_length = min (strlen ($first_word),strlen ($second_word));
	$i=0;
	$adequation = true;

	while ($i<$common_length and $adequation == true){
		$i++;
		if (substr ( $first_word,0, $i) == substr ( $second_word,0, $i)){
			$common_word = substr ( $first_word,0, $i);
		}
		else{
			$adequation = false;
		}
	}
	if (isset ($separate_sous_categorie [1])){
		$common_word .= ' - '.$separate_sous_categorie [1];	
	}
	\Drupal::logger('parfum')->notice('Intersection :'.$common_word	);
	return ($common_word);
}
public function batch_example($options1, $options2) {
  $batch = array(
    'operations' => array(
      array('batch_parfum', array(10, 200)),
	  array('batch_parfum', array(11, 200)),
	  array('batch_parfum', array(12, 200)),
	  array('batch_parfum', array(13, 200)),
      ),
    'finished' => 'batch_example_finished',
    'title' => t('Processing Example Batch'),
    'init_message' => t('Example Batch is starting.'),
    'progress_message' => t('Processed @current out of @total.'),
    'error_message' => t('Example Batch has encountered an error.'),
    'file' => drupal_get_path('module', 'parfum') . '/parfum.batch.inc',
	'progressive' => true,
  );
  batch_set($batch);

  // If this function was called from a form submit handler, stop here,
  // FAPI will handle calling batch_process().

  // If not called from a submit handler, add the following,
  // noting the url the user should be sent to once the batch
  // is finished.
  // IMPORTANT: 
  // If you set a blank parameter, the batch_process() will cause an infinite loop

  batch_process('node/3');
  return array(
      '#type' => 'markup',
      '#markup' => '55',
    );
}

//Paramètres: $varation_bundle='cosmetique'
private function integration_article ($value,$volume_array,&$marques_list){
	$varaition_entity = 'commerce_product_variation';
	$sous_categorie_matches = array (
		1 => 'Eau de Parfum',
		2 => 'Eau de Cologne',
		3 => 'Eau de Toilette',
		4 => 'Fragnance Corporelle',
		5 => 'Coffret',
		6 => 'Gel Douche',
		7 => 'Déodorant',
		8 => 'Crème Régénérante',
		9 => 'Gel à Raser',
		10 => 'Crème Illuminatrice',
		11 => 'Coffret',
		12 => 'Crème anti-Âge',
		13 => 'Soins du Jour',
		14 => 'Crème mains sèches',
		15 => 'Lotion Corporelle',
		16 => 'Crème Corporelle',
		17 => 'Huile Corporelle',
		18 => 'Crème Anti-cellulite',
		19 => 'Crème pour mains',
		20 => 'Crème hydratante',
		21 => 'Crème de couche-culotte',
		22 => 'Crème relaxante',
		23 => 'Crème réparatrice',
		24 => 'Crème de soin intégrale',
		25 => 'Crème régénératrice',
		26 => 'Lotion pour jambes fatiguée',
		27 => 'Crème protectrice',
		28 => 'Baume protecteur de couche-culotte',
		29 => 'Déodorant pour pieds',
		30 => 'Alcool de romarin',
		31 => 'Lait nettoyant',
		32 => 'Gommage visage',
		33 => 'Mousse à raser',
		34 => 'Crème à raser',
		35 => 'Contour des yeux',
		36 => 'Savon de beauté',
		37 => 'Fluide anti-taches',
		38 => 'Crème pour visage',
		39 => 'Démaquillant',
		40 => 'Crème Solaire',
	);

	$reverse_matching = array ();
	
	$termes_separateurs = array ();
	
	
	//Catégorie
	$field_categorie_list = array (
	'Facial' => 7,
	'Cosmétique Visage' => 7,
	'Hygiène' => 2,
	'Cheveux' => 6,
	'Corporal' => 1,
	'Cosmétique Corps' => 1,	
	'Solaires' => 5,
	'Maquillage' => 3,
	'Parfums' => 4,
	);	

	foreach (  array_keys ($reverse_matching) as $key_matching => $value_matching){
		$termes_separateurs [] = '('.$value_matching.')';
	}

	if (in_array ('Parfums',$value ['Families'] )){
		$varation_bundle = 'parfum';
		$bundle = 'type_parfum';
		$pattern = '/(COFFRET)|(body spray)|(ed[t|p] vaporisateur)|(agua de eau de cologne)/i';
	}
	elseif (in_array ('Hygiène',$value ['Families'] )){		
		$varation_bundle = 'hygiene';
		$bundle = 'type_hygiene';
	}
	elseif (in_array ('Corporal',$value ['Families'] ) or in_array ('Cosmétique Corps',$value ['Families'] )){		
		$varation_bundle = 'cosmetique';
		$bundle = 'type_cosmetique';
		$criteria = implode ("|",$termes_separateurs);
		$pattern = '/'.$criteria.'/i';
	}	
	elseif (in_array ('Cheveux',$value ['Families'] )){		
		$varation_bundle = 'soins_cheveux';
		$bundle = 'type_soins_cheveux';
	}	
	elseif (in_array ('Facial',$value ['Families'] ) or in_array ('Cosmétique Visage',$value ['Families'] )){
		$varation_bundle = 'soins_visage';
		$bundle = 'type_soins_visage';
	}
	elseif (in_array ('Maquillage',$value ['Families'] )){		
		$varation_bundle = 'maquillage';
		$bundle = 'type_maquillage';
	}
	elseif (in_array ('Solaires',$value ['Families'] )){		
		$varation_bundle = 'solaire';
		$bundle = 'type_solaire';
	}	
	//Recherche 
	$query = \Drupal::entityQuery($varaition_entity)
	->condition('type', $varation_bundle)
	->condition('field_engelid', $value['Id']);
	$vid = $query->execute();
	
	if (count ($vid)>=1){
		$vid = reset ($vid);
	}
	else{
		$vid = 0;
	}
	//\Drupal::logger('parfum')->notice('VID :'.$vid);
	
	if ($vid !=0){//La variante existe
		// et elle n'a pas été désactivée 
		//par la direction.
		$variation = \Drupal\commerce_product\Entity\ProductVariation::load($vid);
		if ( !$variation->isActive() ){//Article Désactivé 
			if ($variation->get('field_prix_engel')->getValue()[0]["number"]==0.01){//Désactivé automatiquement
				$variation->set("price", new \Drupal\commerce_price\Price(strval ($value['PVR']), 'EUR'));
				$variation->set("field_prix_engel", new \Drupal\commerce_price\Price(strval ($value['Price']), 'EUR'));
				//On active car elle a été désactivé automatiquement
				$variation->setActive(true);
				$variation->save();
				dpm ("L'article ".$variation->getTitle()." est de nouveau actif automatiquement");
			}
			else{//Désactivé par la direction
				$variation->set("price", new \Drupal\commerce_price\Price(strval ($value['PVR']), 'EUR'));
				$variation->set("field_prix_engel", new \Drupal\commerce_price\Price(strval ($value['Price']), 'EUR'));
				//On enregistre sans activer mais on informe la direction.
				\Drupal::logger('parfum')->notice('Veuillez valider le nouveau parfum "'.$variation->getTitle().'"('.$variation->get('field_engelid')->getValue()[0]["value"].')');
				$variation->save();				
				//dpm ("L'article ".$variation->getTitle()." a été mis à jour sans être activé");
			}
		}
	}
	else{//La varaiante n'exsite pas.	
		//Creation d'une nouvelle variante
		//$volume = preg_replace('/ ml/', '', $value['Contenido']);					
		$volume = $value['Contenido'];
		$volume_check = preg_replace('/ ml/', '', $value['Contenido']);					
		if (!is_numeric ($volume_check)){
			//$volume = "En lot";
			$volume = "En Coffret";
		}
		$index = array_search($volume, $volume_array); 
		if ($index > 0){
			$attribute_volume_flacon = $index;
		}
		else{					
			//Ajout de l'attribut manquant dans la liste des volumes
			$volume_attribut = \Drupal\commerce_product\Entity\ProductAttributeValue::create([
			  'attribute' => 'volume_flacon',
			  'name' => $volume,
			]);
			$volume_attribut->save();
			$attribute_volume_flacon= $volume_attribut->id();
			$volume_array[$attribute_volume_flacon] = $volume;
		}
		//\Drupal::logger('parfum')->notice('volume is '.$value['Description'].'|'.$attribute_volume_flacon.'|'.$volume);
		
		//Check if the brand exist
		//$marque_key = array_search(strval ( $value['BrandId'] ), $marques_list);
		$marque_key = array_search($value['BrandId'] , $marques_list);
		
		if (!$marque_key){
			/*ob_start();
			var_dump($marques_list);
			$dumpy = ob_get_clean();				
			\Drupal::logger('parfum')->notice('marques_list: '.$dumpy);		
			\Drupal::logger('parfum')->notice('Ajout de la marque '.$value['BrandId'].'-'.$value['BrandName']);*/
			
			
			$marque_key = $this->_addMarque($value['BrandId'], $value['BrandName']);
			$marques_list [$marque_key] = $value['BrandId'];
		}
		$EANs = array();
		$EANs = implode ('/',$value['EANs']);
		$image_url = $this->_get_product_image ($value['Id']);
		
		
		//Détermination de la catégories
		
		$field_categorie = array ();
		foreach ($value['Families'] as $kefam => $vefam){
			$field_categorie [] = $field_categorie_list [$vefam];
		}
			//ob_start();
			//var_dump($value['Families']);
			//var_dump($field_categorie);
			//$dumpy = ob_get_clean();				
			//\Drupal::logger('parfum')->notice('value_Families: '.$dumpy);	

		
		//dpm ($EANs);
		//dpm ($value);
		$variant_description = array (
			'title' => $value['Description'],
			'type' => $varation_bundle,
			//'sku' => $value['EANs'],
			'sku' => $EANs,
			'status' => TRUE,
			'price' => new \Drupal\commerce_price\Price(strval ($value['PVR']), 'EUR'),
			//'field_marque' => 20,
			'field_marque2' => $marque_key,
			'attribute_volume_flacon' => $attribute_volume_flacon,
			'field_engelid' => $value['Id'],
			//'field_reference' => $value['Referencia'],
			'field_reference' => $value['Gama'],
			'field_prix_engel' => new \Drupal\commerce_price\Price(strval ($value['Price']), 'EUR'),
			'field_categorie' => $field_categorie,
			'field_contenu_lot' => array('value' => "Nouveau", 'format' => 'basic_html'),
			//'field_contenu_lot' => array('value' => "Bonjour", 'format' => 'basic_html'),
			
		);
		
		//Déterminer le chemin de l'image
		if (filter_var($image_url, FILTER_VALIDATE_URL)) {
			$image_data = file_get_contents($image_url);
			if ($image_data){
				$file = file_save_data($image_data, 'public://'.date ('Y-m').'/'.$value['Id'].'.jpg',FILE_EXISTS_REPLACE );
				$variant_description ['field_photo_detail']= array (
					'target_id' => $file->id(),
					'alt' => $value['Description'],
					'title' => $value['Description']
				);				
			}
			else{//Problème lors du téléchargement de l'image
				\Drupal::logger('parfum')->notice('Impossible de télécharger l\'image :'.$value['Id'].'-'.$EANs.'-'.$value['Description']);
			}
		}
		
		
		$matches=array();
		if ($varation_bundle=='parfum'){//Déterminer le type de parfum
			preg_match($pattern, $value['Description'], $matches);
			if (count ($matches)>0){
				$matches = strtolower($matches [0]);
				/*
				1|Eau de Parfum
				2|Eau de Cologne
				3|Eau de Toilette
				4|Fragnance Corporelle
				5|Coffret
				6|Gel Douche
				7|Déodorant
				*/
				if ($matches=='coffret'){
					$matches= 5;
				}
				elseif ($matches=='edt vaporisateur'){
					$matches= 3;
				}
				elseif ($matches=='edp vaporisateur'){
					$matches= 1;
				}
				elseif ($matches=='body spray'){
					$matches= 7;
				}
				elseif ($matches=='agua de eau de cologne'){
					$matches= 2;
				}										
				else{
					$matches= -1;
				}
				
				if ($matches > -1){
					$variant_description ['field_substance']= $matches;
				}
			}
		}
		elseif ($varation_bundle=='cosmetique'){//Déterminer les sous catégories de cosmétiques
			//Désactivation TEMPORAIRE
			//if (preg_match($pattern, strtolower($value['Description']), $matches)){
			//	//$message = "corp";dpm ($message);
			//	$matches = strtolower($matches [0]);
			//	$matches = $reverse_matching [$matches];
			//	$variant_description ['field_categorie']= $matches;				
			//}
			//else{
			//	//$message = "uscule";dpm ($message);
			//	$matches= -1;
			//}
		}
		//Déterminer l'usage de destination
		/*
		1|Pour Femmes
		2|Pour Hommes
		3|Hommes et Femmes
		4|Pour Enfants
		*/
		if ($value['Gender']=='Man'){
			$variant_description ['field_usage'] = 2;
		}
		elseif ($value['Gender']=='Woman'){
			$variant_description ['field_usage'] = 1;
		}
		elseif ($value['Gender']=='Unisex'){
			$variant_description ['field_usage'] = 3;
		}
		
		//Déterminer le contenu du pack en cas de lot
		if (count($value['SetContent']) >0){
			$variant_description ['field_contenu_lot'] = array('value' => $value['SetContent'], 'format' => 'basic_html');

		}
		//Préparation du libellé produit

		//Extraire le nom du produit
		$criteria = implode ("|[\s,]+",$termes_separateurs);
		$product_name = preg_split("/[\s,]+".  $criteria."/",$value['Description']);
		
		if (!is_array($matches) ){
			if ($matches!=-1){
				$sous_categorie = ' - '.$sous_categorie_matches [$matches];
			}
			else{
				$sous_categorie = '';
			}
		}
		else{
			$sous_categorie = '';
		}
		
			
		//$variant_description ['field_libelle_produit'] = isset($product_name[0])?$product_name[0].$sous_categorie:$value['Description'];
		$field_libelle_produit = isset($product_name[0])?$product_name[0].$sous_categorie:$value['Description'];
		//$variant_description['status'] = false; //The direction will validate the product if it is OK.
		$variant_description['status'] = true; //Choix de la direction pour activer les produits.
		$variation = \Drupal\commerce_product\Entity\ProductVariation::create($variant_description);
		dpm ("Le nouveau article ".$variant_description['title']." vient d'être créé");
		$nids = array();
		$entity_type = 'commerce_product';
		
		
		$query = \Drupal::entityQuery($entity_type)
		->condition('type', $bundle)
		->condition('field_engel_product_id', $value['Gama']);
		$nids = $query->execute();

		
		if (count ($nids)==1){
			$product_id = reset ($nids);
		}
		else{
			$product_id = 0;
		}
		//\Drupal::logger('parfum')->notice('Product_id: '.$product_id);
		if ($product_id != 0){//Un produit (gamme de produit) existe déjà.
			$product = \Drupal\commerce_product\Entity\Product::load($product_id);
			

			//Vérifier si l'on doit changer l'article de référence
			$field_article_reference= $product->get('field_article_reference')->getValue();
			if (isset ($field_article_reference[0]['target_id'])){
				$reference_variation = \Drupal\commerce_product\Entity\ProductVariation::load($field_article_reference[0]['target_id']);
				$tell_price = $reference_variation->getPrice()->getNumber();
				if ($tell_price > $value['PVR']){
					$product->field_article_reference = $variation;
					//dpm ("Article de référence mis à jour pour ".$value['Description']);
				}
				
				//Génération du nom du protuit comme intersection des noms des variantes
				//$product_title = $product->title->getValue();
				//$product->title->setValue (  $this->_string_intersection ( $product_title [0]['value'], $value['Description'])) ;
			}
			
			//Ajouter la vraiation au produit.
			$product->variations[]= $variation;					
			$product->save();

			/*ob_start();
			var_dump($variations);
			$dumpy = ob_get_clean();
			\DBVFupal::logger('parfum')->notice('Liste des variations'.$dumpy);*/
		}
		else{//Un nouveau produit est à créer avec une variante.
			$variations = [
			  $variation,
			];
			
			$store = \Drupal\commerce_store\Entity\Store::load(1);

			$product = \Drupal\commerce_product\Entity\Product::create([
			  'uid' => 1,
			  'type' => $bundle,
			  //'title' => $variant_description ['field_libelle_produit'],
			  'title' => $field_libelle_produit,
			  'stores' => [$store],
			  'variations' => $variations,
			  'field_engel_product_id' => $value['Gama'],					  
			  'field_article_reference' => $variation,
			]);
			
			//Désactiver temporairement
			$product->save();

			/*ob_start();
			var_dump($product->title);
			$dumpy = ob_get_clean();
			\Drupal::logger('parfum')->notice('Informations sur le titre'.$dumpy);*/
			
		}
		//dpm ($product_id);	
	}
}
}

