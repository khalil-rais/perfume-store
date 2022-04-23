<?php
 
/**
 * @file
 * Contains \Drupal\hello_world\Form\BookSchedule.
 */
 
namespace Drupal\parfum\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime ;
use Drupal\node\Entity\Node;

function arrayUsecases( $usecase) {
	if ($usecase == "duration"){
		return (array (60 => t('1hour'), 90 => t('1hour30'),120 => t('2hours')));
	}
	elseif ($usecase == "activity"){
		return (array (0=>t('Driving'), 1=>t('Theory'),2=>t('Exam')));
	}
}	


/**
 * Provides a simple example form.
 */
class BookSchedule extends FormBase {
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'book_schedule';
  }


  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	  
	$user = \Drupal::currentUser();
ob_start();
/*var_dump($user);
var_dump($user->id());
var_dump($user->getUsername());*/
/*var_dump($my_array);
var_dump($result);
var_dump($overlap);

var_dump($starting_date);

var_dump ($show_time);*/
$result2 = ob_get_clean();
dpm ($result2);	
    // Use the Form API to define form elements.
    $form['date'] = array(
      '#title' => t('Date'),
 '#type' => 'datetime', '#default_value' => $date
    );
    $form['duration'] = array(
      '#title' => t('Duration'),
      '#type' => 'select',
	  '#size' => 3,
	  '#options' => arrayUsecases("duration"),
    );	
	
    $form['activity'] = array(
      '#title' => t('Activity'),
      '#type' => 'radios',
	  '#options' => arrayUsecases("activity"),
    );
    $form['departure'] = array(
      '#title' => t('Departure'),
      '#type' => 'textfield',

    );	
	
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
    );
    return $form;
  }
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the form values.
	$starting_date = $form_state->getValue('date');
	$duration =  $form_state->getValue('duration');
	$ending_date = clone $starting_date;
	$tmp_date = clone $starting_date;
	$ending_date->modify('+'.$duration.' minutes');
	$tmp_date->modify('+1 day');
	
	$query = \Drupal::entityQuery('node')
		->condition('status', 1)
		->condition('type', 'session')
		->condition('field_departure_time.value',  $tmp_date->format('Y-m-d'), '<')
		->condition('field_departure_time.value',  $starting_date->format('Y-m-d'), '>'); 

	$result = $query->execute();
	$my_array = array();
	if (isset ($result)){
		$node_storage = \Drupal::entityManager()->getStorage('node'); 
		$node_list = $node_storage->loadMultiple($result);

		$overlap=false;
		foreach ($node_list as $node_result){
			$entity = $node_storage->load($value);
			$existing_beginning_date = new DrupalDateTime($node_result->field_departure_time->value);
			$existing_ending_date =  clone $existing_beginning_date ;
			$existing_ending_date->modify ("+{$node_result->field_duration->value} minutes");
			
			if (($starting_date <= $existing_ending_date) and ($ending_date >= $existing_beginning_date)){
				$overlap=true;
				break;
			}
		}
		
		if ($overlap){		
			$form_state->setErrorByName('date', t('The selected time is already booked, please select another one.'));
			return FALSE;		
		}	
		else {
			return TRUE;
		}		
	}
			
	
  }
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
/*    $account = \Drupal::currentUser();
    db_query("INSERT INTO hello_world (uid, message, timestamp) values(:uid, :message, :timestamp)", array(':uid' => $account->id(), ':message' => $form_state->getValue('message'), ':timestamp' => time()));
    drupal_set_message('Your form was submitted successfully, you typed in the body ' . $form_state->getValue('message'));
    drupal_set_message('A new row was entered into the hello_world table! ');*/
	
	$starting_date = $form_state->getValue('date');


	$user = \Drupal::currentUser();
	/*dpm ("Ahla Bik");
	ob_start();
	var_dump($form_state->getUserInput());*/
	/*var_dump($my_array);
	var_dump($result);
	var_dump($overlap);

	var_dump($starting_date);

	var_dump ($show_time);*/
	/*$result2 = ob_get_clean();
	dpm ($result2);	*/
		
	// Create node object
	$title = $user->getUsername();
	$query = \Drupal::entityQuery('node')
		->condition('status', 1)
		->condition('type', 'session')
		->condition('field_name_and_surname.value',  $user->id());
		
	$activity=arrayUsecases('activity');
	
	$result = $query->execute();
	$nbr_session = count($result)+1;
	$node = Node::create([
	  'type' => 'session',
	  'title' => $activity[$form_state->getValue('activity')]." | ".$title." | Session n° ".$nbr_session,
	  'field_name_and_surname' => $user->id(),
	  'field_departure_location' => $form_state->getValue('departure'),
	  'field_activity' => $form_state->getValue('activity'),
	  'field_departure_time' => $starting_date->format('Y-m-d\TH:i:s'),	
	  'field_duration' => $form_state->getValue('duration'),
	  
	]);
	$node->save();			
	var_dump($user->id());
	var_dump($user->getUsername());	
		
    /*["field_departure_time"]=>
    array(1) {
      ["x-default"]=>
      array(1) {
        [0]=>
        array(1) {
          ["value"]=>
          string(19) "2017-03-16T14:00:00"
        }
      }
    }
    ["field_duration"]=>
    array(1) {
      ["x-default"]=>
      array(1) {
        [0]=>
        array(1) {
          ["value"]=>
          string(2) "50"
        }
      }
    }*/		
	
	
/*ob_start();
var_dump($ending_date->format('Y-m-d 23:59:59'));
var_dump($my_array);
var_dump($result);
var_dump($overlap);

var_dump($starting_date);

var_dump ($show_time);*/
$result2 = ob_get_clean();
	
	//dpm ($result2);
  }
}
