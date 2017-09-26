<?php

/**
 * @file
 * Provides the Solve Media administration settings.
 */

/**
 * Form callback; administrative settings for Solve Media.
 */
function solvemedia_admin_settings() {
	// Load the solvemedia library.
	_adcopy_load_library();

	$form = array();
	$form['adcopy_public_key'] = array(
		'#type' => 'textfield',
		'#title' => t('Public Key'),
		'#default_value' => variable_get('adcopy_public_key', ''),
		'#maxlength' => 40,
		'#description' => t('The public key given to you when you registered on the <a href="@url" target="_blank">Solve Media portal</a>.', array('@url' => 'http://portal.solvemedia.com/portal/public/signup')),
		'#required' => TRUE,
	);
	$form['adcopy_private_key'] = array(
		'#type' => 'textfield',
		'#title' => t('Private Key'),
		'#default_value' => variable_get('adcopy_private_key', ''),
		'#maxlength' => 40,
		'#description' => t('The private key given to you when you registered on the <a href="@url" target="_blank">Solve Media portal</a>.', array('@url' => 'http://portal.solvemedia.com/portal/public/signup')),
		'#required' => TRUE,
	);
	$form['adcopy_hash_key'] = array(
		'#type' => 'textfield',
		'#title' => t('Hash Key'),
		'#default_value' => variable_get('adcopy_hash_key', ''),
		'#maxlength' => 40,
		'#description' => t('The hash key given to you when you registered on the <a href="@url" target="_blank">Solve Media portal</a>.', array('@url' => 'http://portal.solvemedia.com/portal/public/signup')),
		'#required' => TRUE,
	);
	$form['adcopy_show_on_comment_focus'] = array(
		'#type' => 'checkbox',
		'#title' => t('Show on Comment Focus'),
		'#default_value' => variable_get('adcopy_show_on_comment_focus', FALSE),
		'#description' => t('In the comments section, only show the Solve Media widget if the user clicks on the comment box.'),
	);
	$form['adcopy_theme_settings'] = array(
		'#type' => 'fieldset',
		'#title' => t('Theme Settings'),
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,
	);
	$form['adcopy_theme_settings']['adcopy_theme'] = array(
		'#type' => 'select',
		'#title' => t('Theme'),
		'#description' => t('Defines which theme to use for Solve Media.'),
		'#options' => array(
			'white' => t('White'),
			'red' => t('Red'),
			'black' => t('Black'),
			'purple' => t('Purple'),
		),
		'#default_value' => variable_get('adcopy_theme', 'white'),
		'#required' => TRUE,
	);
	$form['adcopy_lang_settings']['adcopy_lang'] = array(
		'#type' => 'select',
		'#title' => t('Language'),
		'#description' => t('Defines which language to use for Solve Media.'),
		'#options' => array(
			'en' => t('English'),
			'de' => t('German'),
			'fr' => t('French'),
			'es' => t('Spanish'),
			'it' => t('Italian'),
			'ca' => t('Catalan'),
			'pl' => t('Polish'),
			'hu' => t('Hungarian'),
			'sv' => t('Swedish'),
			'no' => t('Norwegian'),
			'pt' => t('Portuguese'),
			'nl' => t('Dutch'),
			'tr' => t('Turkish'),
			'yi' => t('Yiddish'),
			'ja' => t('Japanese'),
		),
		'#default_value' => variable_get('adcopy_lang', 'en'),
		'#required' => TRUE,
	);
	$form['adcopy_theme_settings']['adcopy_tabindex'] = array(
		'#type' => 'textfield',
		'#title' => t('Tab Index'),
		'#description' => t('Sets a <a href="@tabindex" target="_blank">tabindex</a> for the Solve Media text box. If other elements in the form use a tabindex, this should be set so that navigation is easier for the user.', array('@tabindex' => 'http://www.w3.org/TR/html4/interact/forms.html#adef-tabindex')),
		'#default_value' => variable_get('adcopy_tabindex', ''),
		'#size' => 4,
	);

	return system_settings_form($form);
}

/**
 * Validation function for solvemedia_admin_settings().
 *
 * @see solvemedia_admin_settings()
 */
function solvemedia_admin_settings_validate($form, &$form_state) {
	$tabindex = $form_state['values']['adcopy_tabindex'];
	if (!empty($tabindex) && !is_numeric($tabindex)) {
		form_set_error('adcopy_tabindex', t('The Tab Index must be an integer.'));
	}
}