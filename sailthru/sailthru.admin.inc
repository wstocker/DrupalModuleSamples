<?php
/**
 * Form callback; administrative settings for Sailthru.
 */
function sailthru_api_config($form, &$form_state) {
	$default_key = variable_get('sailthru_api_key'); 
	$default_secret = variable_get('sailthru_api_secret');
	$default_uri = variable_get('sailthru_client_api_uri', 'https://api.sailthru.com');

	$form = array();
	$form['sailthru_api_key'] = array(
		'#type' => 'textfield',
		'#prefix' => '<fieldset id="personal-info" id="donate-by-credit-card"><legend>Saithru API Key & Secret</legend><span class="d-t"><span class="d-tc">',
		'#required' => TRUE,
		'#attributes' =>array('placeholder' => t('API Key'), 'required' => t('required')),
		'#default_value' => $default_key,
		'#suffix' => '</span>'
	);
	$form['sailthru_api_secret'] = array(
		'#type' => 'textfield',
		'#prefix'=>'<span class="d-tc"><span class="messages error">' . $message . '</span>',
		'#required' => TRUE,
		'#attributes' =>array('placeholder' => t('API Secret'), 'required' => t('required')),
		'#default_value' => $default_secret,
		'#suffix' => '</span></span>'
	);
	$form['sailthru_api_uri'] = array(
		'#type'        => 'textfield',
		'#title'       => t('Sailthru URI'),
		'#description' => t('The URI to use for the Sailthru API'),
		'#required'    => FALSE,
		'#default_value' => $default_uri,
	);

	return system_settings_form($form);
}

function sailthru_form_sailthru_api_config_alter(&$form, &$form_state) {
	$form['#submit'][] = 'sailthru_form_submit';
}

function sailthru_form_submit($form, &$form_state) {
	//encrypt api secret
	variable_set('sailthru_api_key', $form_state['values']['sailthru_api_key']);
	variable_set('sailthru_api_uri', $form_state['values']['sailthru_api_uri']);
	variable_set('sailthru_api_secret',$form_state['values']['sailthru_api_secret']);
}