<?php
/**
 * Plugin Name: Pending Email SMTP
 * Description: Pending Email SMTP, delay email smtp, please turn on your cronjob to get the working
 * Version: 1.0.0
 * License: A "Slug" license name e.g. GPL2
 * Author: binmaocom
 * Tags: mail, smtp, phpmailer, pending email, mailing queue, wp_mail, email, fast email smtp, delay email
 * Author URI: https://www.sanwp.com
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'WPC_PendingEmail' ) ) {
	include_once  'functions.php';
}

register_activation_hook( __FILE__, 'wp_pending_email_plugin_activate' );
function wp_pending_email_plugin_activate() {
     do_action( 'wp_pending_email_plugin_activate' );
}