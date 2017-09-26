<?php
/*
 * Copyright (c) 2014 by Solve Media, Inc.
 * Author: Ilia Fishbein
 * Created: 2014-07-28 09:34 (EDT)
 * Function: Solve Media Data API PHP Library
 *
 * This is a PHP library that handles calling Solve Media's data collection API.
 *
 * Version: 1.0.11
 */

/**
 * The Solve Media dapi server URL's
 */
define("SOLVEMEDIA_DAPI_SERVER",         "http://data.circulate.com");
define("SOLVEMEDIA_DAPI_SECURE_SERVER",  "https://data-secure.circulate.com");
define("SOLVEMEDIA_DAPI_SESSION_COOKIE", "sm_dapi_session");
define("SOLVEMEDIA_DAPI_SESSION_LENGTH", 14 * 24 * 60 * 60);

/**
 * Gets the dapi collection HTML
 * This is called from the browser, and passes information to Solve Media for logging
 * @param string $site_id - a site identifying key
 * @param array $user_data - key/value pairs of user data
 * @param boolean $use_ssl - should the request be made over ssl? (optional, default is false)
 * @param string $site_domain - the domain name of this website
 * @param string $site_path - the path to the root directory of this website

 * @return string - The HTML to be embedded in the end-user's page.
 */
function solvemedia_get_dapi_html ($site_id, $user_data, $use_ssl = false, $site_path = "/", $site_domain = ""){
    if( $site_id == null || $site_id == '' ){
        trigger_error("A unique site identifier is required to use the Solve Media data API. Contact Solve Media (http://www.solvemedia.com) if you do not have one.", E_USER_WARNING);
        return;
    }

    // we can't cookie users who don't accept cookies, assume that users who don't
    // have a single cookie don't accept cookies
    // we also don't collect data too often
    if( !count($_COOKIE) || solvemedia_get_dapi_session_ck() ){
        return;
    }

    $sm_info = array();

    // remove any key-value pairs with an empty value
    foreach( $user_data as $key => $value ){
        $value = preg_replace('/\s+$/', '', $value);
        $value = preg_replace('/^\s+/', '', $value);

        if( $key == 'email' ){
            $value = strtolower($value);
        }

        if( $value ){
            $sm_info[$key] = $value;
        }
    }

    if( !count($sm_info) ){
        return;
    }

    $sm_info["sid"] = $site_id;

    if( $use_ssl ){
        $server = SOLVEMEDIA_DAPI_SECURE_SERVER;
    } else {
        $server = SOLVEMEDIA_DAPI_SERVER;
    }

    solvemedia_set_dapi_session_ck($site_path, $site_domain);
    
    $html  = '<script type="text/javascript">' . "\n";
    $html .= 'var SMInformation = ' . "\n";
    $html .= json_encode($sm_info) . "\n";
    $html .= ";\n";
    $html .= "</script>\n";
    $html .= '<script type="text/javascript" src="'. $server . '/dapi/collect" async></script>' . "\n";
    
    return $html;
}

/**
 * Gets the Solve Media email hash string
 * @param string $email - email address

 * @return string - email address hashed using Solve Media's prorietary algorithm
 */
function solvemedia_hash_email ($email){
    $email = preg_replace('/\s+$/', '', $email);
    $email = preg_replace('/^\s+/', '', $email);

    list($uname, $domain) = preg_split("/@/", $email);

    $h  =  "H1:" . hash('sha1', strtolower($email));
    $h .= ",H2:" . hash('sha1', strtoupper($email));
    $h .= ",H3:" . hash('sha1', strtolower($domain));
    $h .= ",H4:" . hash('md5', strtolower($email));
    $h .= ",H5:" . hash('md5', strtoupper($email));
    $h .= ",H6:" . hash('sha256', strtolower($email));
    $h .= ",H7:" . hash('sha256', strtoupper($email));

    return $h;
}

/**
 * Sets a cookie indicating a Solve Media DAPI session
 * This cookie is used to prevent passing information to Solve Media too frequently
 * @param string $path - cookie path
 * @param string $domain - cookie domain
 * 
 * @return null
 */
function solvemedia_set_dapi_session_ck ($path, $domain){
    setcookie(SOLVEMEDIA_DAPI_SESSION_COOKIE, 1, time() + SOLVEMEDIA_DAPI_SESSION_LENGTH, $path, $domain);
}

/**
 * Gets the Solve Media DAPI session cookie
 * 
 * @return boolean - cookie value
 */
function solvemedia_get_dapi_session_ck (){
    return isset($_COOKIE[SOLVEMEDIA_DAPI_SESSION_COOKIE]) ? $_COOKIE[SOLVEMEDIA_DAPI_SESSION_COOKIE] : 0;
}

?>