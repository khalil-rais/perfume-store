<?php
 
/**
 * @file
 * Contains \Drupal\parfum\Form\SessionFollowup.
 */
 
namespace Drupal\parfum\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
 
/**
 * Provides a simple example form.
 */
class SessionFollowup extends FormBase {
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'session_followup';
  }
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Use the Form API to define form elements.
    $form['activity'] = array(
      '#title' => t('Activity'),
      '#type' => 'textfield',
	  '#size' => 50,
    );
    $form['pros'] = array(
      '#title' => t('Good Skills'),
      '#type' => 'textarea',
    );	
    $form['cons'] = array(
      '#title' => t('Needs Improvement'),
      '#type' => 'textarea',
    );		

	
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );
    return $form;
  }
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the form values.
  }
 
  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = \Drupal::currentUser();
    db_query("INSERT INTO parfum (uid, message, timestamp) values(:uid, :message, :timestamp)", array(':uid' => $account->id(), ':message' => $form_state->getValue('message'), ':timestamp' => time()));
    drupal_set_message('Your form was submitted successfully, you typed in the body ' . $form_state->getValue('message'));
    drupal_set_message('A new row was entered into the parfum table! ');
  }
}
