<?php
/*
Plugin Name: GigPress
Plugin URI: http://gigpress.com
Description: An easy way for bands to list and manage tour dates on their WordPress-powered website.
Version: 1.4.3
Author: Derek Hogue
Author URI: http://amphibian.info

Copyright 2008 DEREK HOGUE

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') ) {
	define( 'WP_CONTENT_URL', get_bloginfo('wpurl') . '/wp-content');
}
if ( !defined('WP_CONTENT_DIR') ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( !defined('WP_PLUGIN_URL') ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
}

global $wpdb;

global $now; $now = mysql2date('Y-m-d', current_time('mysql'));

global $gigpress; $gigpress = array();
	$gigpress['gigs_table'] = $wpdb->prefix . "gigpress_shows";
	$gigpress['tours_table'] = $wpdb->prefix . "gigpress_tours";
	$gigpress['version'] = "1.4.3";
	$gigpress['db_version'] = "1.3";
	$gigpress['rss'] = get_bloginfo('home') . "/?feed=gigpress";
	
if ($GLOBALS['wp_version'] < "2.5") {
	$gigpress['admin_class'] = "v23";
} elseif ($GLOBALS['wp_version'] < "2.7") {
	$gigpress['admin_class'] = "v25";
} else {
	$gigpress['admin_class'] = "v25";
}
	
global $default_options; $default_options = array(
	"alternate_clock" => 0,
	"autocreate_post" => 0,
	"band" => "",		
	"category_exclude" => 0,
	"date_format_long" => "l, F jS Y",
	"date_format" => "m/d/y",
	"db_version" => $gigpress['db_version'],
	"default_country" => "US",
	"default_date" => $now,
	"default_time" => "00:01",
	"default_tour" => 0,
	"display_country" => 1,
	"individual_heading" => "Individual shows",
	"nopast" => "No shows in the archive yet.",
	"noupcoming" => "No shows booked at the moment.",
	"related_category" => 1,
	"related_heading" => "Related show",
	"related_position" => "nowhere",
	"related" => "Related post.",
	"relatedlink_city" => 0,
	"relatedlink_date" => 0,
	"relatedlink_notes" => 1,			
	"rss_head" => 1,
	"rss_list" => 1,
	"rss_title" => "Upcoming shows",
	"rss_upcoming" => 1,
	"shows_page" => "",
	"sidebar_link" => 0,
	"target_blank" => 0,
	"time_format" => "g:ia",
	"tour_heading" => "h3",
	"tour_label" => "Tour",
	"tour_location" => "before",
	"tour_segment" => 1,
	"upcoming_phrase" => "View all shows",
	"user_level" => 2,
	"welcome" => "yes",
	"widget_heading" => "Upcoming shows",
	"widget_number" => 3,
	"widget_segment" => 1
);

$gpo = get_option('gigpress_settings');

require('admin-upgrade.php');
require('admin-install.php');

require('admin-addshow.php');
require('admin-upcoming.php');
require('admin-past.php');
require('admin-tables.php');
require('admin-tours.php');
require('admin-options.php');
require('admin-export.php');

require('gp-tables.php');
require('gp-upcoming.php');
require('gp-archive.php');
require('gp-related.php');
require('gp-sidebar.php');
require('gp-widget.php');
require('gp-feed.php');


function gigpress_admin() {
	// Add our WordPress admin pages, top-level-like. "Ill-advised," 
	// sure, but methinks bands need will be on this page aplenty,
	// so I wish not to bury it.
	
	$gpo = get_option('gigpress_settings');
	
	$add = __("Add a show", "gigpress");
	$upcoming = __("Upcoming shows", "gigpress");
	$tours = __("Tours", "gigpress");
	$past = __("Past shows", "gigpress");
	$options = __("Settings", "gigpress");
	$export = __("Export", "gigpress");
	
	add_menu_page("GigPress &rsaquo; $add", "GigPress", $gpo['user_level'], __FILE__, "gigpress_add", WP_PLUGIN_URL . "/gigpress/images/gigpress-icon-16.png");
	// By setting the unique identifier of the submenu page to be __FILE__,
	// we let it be the first page to load when the top-level menu
	// item is clicked
	add_submenu_page(__FILE__, "GigPress &rsaquo; $add", $add, $gpo['user_level'], __FILE__, "gigpress_add");
	add_submenu_page(__FILE__, "GigPress &rsaquo; $upcoming", $upcoming, $gpo['user_level'], "gigpress-upcoming", "gigpress_admin_upcoming");
	add_submenu_page(__FILE__, "GigPress &rsaquo; $tours", $tours, $gpo['user_level'], "gigpress-tours", "gigpress_tours");
	add_submenu_page(__FILE__, "GigPress &rsaquo; $past", $past, $gpo['user_level'], "gigpress-past", "gigpress_admin_past");
	add_submenu_page(__FILE__, "GigPress &rsaquo; $options", $options, $gpo['user_level'], "gigpress-options", "gigpress_options");
	add_submenu_page(__FILE__, "GigPress &rsaquo; $export", $export, $gpo['user_level'], "gigpress-export", "gigpress_export");
}


// MISCELLANEOUS FUNCTIONS

function gigpress_load_jquery()	{
	// If we're in the admin, and on a GigPress page, load up jQuery
	if ( is_admin() && strpos($_SERVER['QUERY_STRING'], 'gigpress') !== false ) {
		wp_enqueue_script('jquery');
	}
}


function gigpress_admin_head() {
	// Helps some formatting on our admin pages ...
	echo('
	<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/gigpress/css/gigpress-admin.css" />
	');
	// Load our JS if we're on a GigPress page
	if(strpos($_SERVER['QUERY_STRING'], 'gigpress') !== false ) {
		echo('<script type="text/javascript" src="'. WP_PLUGIN_URL .'/gigpress/scripts/gigpress.js"></script>
		');
	}
}



function gigpress_admin_footer() {
	global $gigpress;
	echo(__("You're using", "gigpress").' <a href="http://gigpress.com">GigPress '.$gigpress['version'].'</a>. ');
}


function gigpress_flush_rules(){
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}


function gigpress_check_excerpt($excerpt) {
	global $is_excerpt; $is_excerpt = TRUE;
	return $excerpt;
}


function gigpress_styles() {
	// Default style sheet
	echo('
	<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/gigpress/css/gigpress.css" />
	');
	// If there's a custom stylesheet, load it too ...
	if (file_exists(get_template_directory()."/gigpress.css")) {
		echo('<link rel="stylesheet" type="text/css" href="'. get_bloginfo('template_directory').'/gigpress.css" />
		');
	}
}


function gigpress_check_url($url) {
	// This makes sure urls start with a protocol
	$url = trim($url);
	$http = substr($url, 0, 7);
	$https = substr($url, 0, 8);
	if($http != "http://" && $https != "https://") {
		$url = "http://". $url;
	}
	return $url;
}


function gigpress_timefy($time) {
	// Format our show time as we like
	$gpo = get_option('gigpress_settings');
	$timeparts = explode(':', $time);
	$fulltime = mktime($timeparts[0], $timeparts[1]);
	$formattedtime = date($gpo['time_format'], $fulltime);
	return $formattedtime;
}


function gigpress_mapit($address, $locale, $country) {
	// This makes our street address linked up to Google Maps
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	if($address) {
		$url = 'http://maps.google.com/maps?&amp;q='. urlencode($address). ','. urlencode($locale) .','. urlencode($country);
		if($gpo['target_blank'] == 1) {
			$url = '<a href="'. $url .'" title="'.__("Find this address using Google Maps.", "gigpress"). ' (' .__("opens in a new window", "gigpress"). ')" class="gigpress-address" target="_blank">'.$address.'</a>';
		} else {
			$url = '<a href="'. $url .'" title="'.__("Find this address using Google Maps.", "gigpress"). '" class="gigpress-address">'.$address.'</a>';
		}	
			
		return $url;
	}
}


function gigpress_sanitize($input) {
	// Make sure our DB output is kosher
	$input = stripslashes($input);
	$input = wptexturize($input);
	return $input;
}


function gigpress_admittance($admittance) {
	// Strings for our admittance field
	if($admittance == "All Ages") { $admittance = __("All Ages", "gigpress"); }
	if($admittance == "All Ages/Licensed") { $admittance = __("All Ages/Licensed", "gigpress"); }
	if($admittance == "No minors") { $admittance = __("No minors", "gigpress"); }
	return $admittance;
}


function gigpress_intl() {
	// Internationalize
	load_plugin_textdomain('gigpress', PLUGINDIR.'/gigpress/langs/');
}

function register_gigpress_settings() {
	if ( function_exists('register_setting') ) {
		register_setting('gigpress','gigpress_settings');
	}
}

function gigpress_favorites($actions) {
	$gpo = get_option('gigpress_settings');
	$level = "level_" . $gpo['user_level']; 
	$actions['admin.php?page=gigpress/gigpress.php'] = array('Add a show', $level);
    return $actions;
}


// HOOKS, ACTIONS, DESCENT INTO THE ABYSS

register_activation_hook(__FILE__,'gigpress_install');
register_activation_hook(__FILE__,'gigpress_flush_rules');

add_action(init,'add_gigpress_feed');
add_action(init,'gigpress_intl');

add_action('admin_init','gigpress_load_jquery');
add_action('admin_init', 'register_gigpress_settings'); 
add_action('admin_head', 'gigpress_admin_head');
add_action('admin_menu', 'gigpress_admin');
add_filter('favorite_actions', 'gigpress_favorites');
if ( strpos($_SERVER['QUERY_STRING'], 'gigpress') !== FALSE ) {
	add_action('in_admin_footer', 'gigpress_admin_footer', 9);
}
add_action('widgets_init', 'gigpress_widget_init');

add_action('wp_head', 'gigpress_styles');
if ( $gpo['rss_head'] == 1 ) {
	add_action('wp_head', 'gigpress_head_feed');
}

if ( function_exists('add_shortcode') ) {
	add_shortcode('gigpress_upcoming','gigpress_upcoming');
	add_shortcode('gigpress_archive','gigpress_archive');
} else {
	add_filter('the_content', 'gigpress_upcoming_wrapper');
	add_filter('the_content', 'gigpress_archive_wrapper');
}

if ( $gpo['category_exclude'] == 1) {
	add_action('pre_get_posts','gigpress_exclude_shows');
}
if ( $gpo['related_position'] != "nowhere" ) {
	add_filter('get_the_excerpt', 'gigpress_check_excerpt', 1);
	add_filter('the_content', 'gigpress_show_related');
}
add_action('delete_post', 'gigpress_remove_related');



// We're forced to bed, but we're free to dream.

?>