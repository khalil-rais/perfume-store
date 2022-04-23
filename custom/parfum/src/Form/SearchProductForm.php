<?php

namespace Drupal\parfum\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SearchProductForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'load_product_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $form['search_field'] = array(
        '#type' => 'textfield',
        '#autocomplete_route_name' => 'searchproduct.autocomplete',
        '#placeholder' => t('Product name'),
      );

      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Search'),
        '#button_type' => 'primary',
      );
      return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $search_field = $form_state->getValue('search_field');
    $val_1 = explode('(', $search_field, 2);
    $val_2 = substr($val_1[1], 0, -1);
    $url_product_page = 'product/'.$val_2;
    $response = new RedirectResponse($url_product_page);
    $response->send();
  }
}