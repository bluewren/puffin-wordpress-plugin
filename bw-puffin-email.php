<?php
/**
 * @wordpress-plugin
 * Plugin Name:       BlueWren Puffin
 * Plugin URI:        https://www.bluewren.co.uk
 * Version:           0.5
 * Author:            BlueWren
 * Author URI:        https://www.bluewren.co.uk
 * Update URI:        https://github.com/bluewren/puffin-wordpress-plugin
 * Description: 	  Send mail to Puffin.
 * License: 		  GPLv2 or later
 * Text Domain: 	  bw-puffin
 */

use BWPuffin\Admin;
use BWPuffin\Controllers\PuffinController;
use BWPuffin\Puffin;

if ( ! defined( 'WPINC' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PUFFIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Controllers/PuffinController.php';
require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Traits/Bridge.php';
require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Puffin.php';
require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Admin.php';

function bw_rest_transients()
{
	global $wpdb;

	$result = $wpdb->query( 
		$wpdb->prepare( 
			"DELETE FROM wp_options WHERE option_name = '_site_transient_update_plugins'"
		)
	);
}

add_filter( 'update_plugins_github.com', 'self_update', 10, 4 );

function self_update( $update, array $plugin_data, string $plugin_file, $locales ) {

	// only check this plugin
	if ( 'bw-puffin-email/bw-puffin-email.php' !== $plugin_file ) {
		return $update;
	}

	// already completed update check elsewhere
	if ( ! empty( $update ) ) {
		return $update;
	}

	// let's go get the latest version number from GitHub
	$response = wp_remote_get(
		'https://api.github.com/repos/bluewren/puffin-wordpress-plugin/releases/latest',
		array(
			'user-agent' => 'bluewren',
		)
	);

	if ( is_wp_error( $response ) ) {
		return;
	} else {
		$output = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	$new_version_number  = $output['tag_name'];
	$is_update_available = version_compare( 'v'.$plugin_data['Version'], $new_version_number, '<' );

	if ( ! $is_update_available ) {
		return false;
	}

	$new_url     = $output['html_url'];
	$new_package = $output['assets'][0]['browser_download_url']; //zip

	error_log('$plugin_data: ' . print_r( $plugin_data, true ));
	error_log('$new_version_number: ' . $new_version_number );
	error_log('$new_url: ' . $new_url );
	error_log('$new_package: ' . $new_package );

	return array(
		'slug'    		=> $plugin_data['TextDomain'],
		'version' 		=> $plugin_data['Version'],
		'new_version' 	=> str_replace('v', '', $new_version_number),
		'url'     		=> $new_url,
		'package' 		=> $new_package,
	);
}

add_action('admin_menu', 'bw_puffin_admin');

function bw_puffin_admin()
{
    $admin = new Admin();
}    

add_action('init', 'bw_puffin_init');

function bw_puffin_init()
{
    $puffin = new Puffin();
    $admin = new PuffinController($puffin);
}