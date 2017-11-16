<?php
/*
Plugin Name: WP Clear File Cache
Plugin URI: https://beseismic.com
Description: Clear WP4.9 file cache so page templates and other files are recognized.
Author: Be Seismic
Version: 1.0.0

DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
                    Version 2, December 2004 

 Copyright (C) 2004 Sam Hocevar <sam@hocevar.net> 

 Everyone is permitted to copy and distribute verbatim or modified 
 copies of this license document, and changing it is allowed as long 
 as the name is changed. 

            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION 

  0. You just DO WHAT THE FUCK YOU WANT TO.
*/

if( ! defined( 'ABSPATH' ) ) exit;
if( !class_exists( 'bs_clear_template' ) ) {
	class bs_clear_template {
		function __construct() {
			add_action( 'admin_init', array( $this, 'plugin_setup' ), 10 );
		}
		
		function plugin_setup() {
			add_action( 'admin_bar_menu', array($this, 'add_cache_button'), 999 );
			add_action( 'admin_footer', array($this, 'output_button_script') );
			add_action( 'wp_ajax_bs_bust_file_cache', array( $this, 'handle_cache_bust' ) );
		}
		
		function handle_cache_bust() {
			check_ajax_referer( 'handle_cache_bust', 'nonce' );
			
			global $wpdb;
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE '_transient_files_%' LIMIT 1" );
			
			wp_die();
		}
		
		function output_button_script() {
			echo '
			<script>
			jQuery(function($) {
				$("#wp-admin-bar-bust_file_cache>a").on("click", function(e) {
					e.preventDefault();
					
					$(this).text("Please wait...");
					$.post(ajaxurl, {"action": "bs_bust_file_cache", "nonce": $(this).attr("href").replace("#", "")}, function() {
						window.location.reload();
					});
				});
			});
			</script>';
		}
		
		function add_cache_button( $wp_admin_bar ) {
			if( is_admin() ) {
				$nonce = wp_create_nonce( 'handle_cache_bust' );
				$wp_admin_bar->add_node( array(
					'id'		=>	'bust_file_cache',
					'title'		=>	'Clear File Cache',
					'href'		=>	'#' . $nonce,
				) );
			}
		}
	}
	
	new bs_clear_template();
}