<?php
/*
Plugin Name: GigPress
Plugin URI: http://gigpress.com
Description: An easy way for bands to list and manage tour dates on their WordPress-powered website.
Version: 1.2.1
Author: Derek Hogue
Author URI: http://amphibian.info

Copyright 2008  AMANDA KISSENHUG

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


global $wpdb;
global $gigpress;
$gigpress = array();
$gigpress['gigs_table'] = $wpdb->prefix . "gigpress_shows";
$gigpress['tours_table'] = $wpdb->prefix . "gigpress_tours";
$gigpress['version'] = "1.2.1";
$gigpress['db_version'] = "1.1";
$gigpress['rss'] = get_bloginfo('home') . "/?feed=gigpress";



// -------------------- ADMINISTRATION, BACK-END SHIZZLE ---------------------//



// See if our main table already exists, and if not,
// create both of our tables.  We should probably check for each individually
// - and concomitantly create each individually.  But alas.  We do not.

function gigpress_install() {
	global $wpdb;
	global $gigpress;
	
	if($wpdb->get_var("SHOW TABLES LIKE ".$gigpress['gigs_table']."") != $gigpress['gigs_table']) {
		$create = "CREATE TABLE " . $gigpress['gigs_table'] . " (
		show_id INTEGER(4) AUTO_INCREMENT,
		show_tour_id INTEGER(4) NOT NULL,
		show_date DATE NOT NULL,
		show_expire DATE NOT NULL,
		show_multi INTEGER(1),
		show_time TIME NOT NULL,
		show_address VARCHAR(255),
		show_locale VARCHAR(255) NOT NULL,
		show_country VARCHAR(2) NOT NULL,
		show_price VARCHAR(32) DEFAULT 'Not sure',
		show_tix_url VARCHAR(255),
		show_venue VARCHAR(255) DEFAULT 'TBA' NOT NULL,
		show_venue_url VARCHAR(255),
		show_ages ENUM('All Ages','No minors','All Ages/Licensed','Not sure'),
		show_notes TEXT,
		PRIMARY KEY  (show_id)
		);
		CREATE TABLE " . $gigpress['tours_table'] . " (
		tour_id INTEGER(4) AUTO_INCREMENT,
		tour_name VARCHAR(255) NOT NULL,
		tour_created DATE NOT NULL,
		PRIMARY KEY  (tour_id)
		);
		ALTER TABLE " . $gigpress['gigs_table'] . " ADD FOREIGN KEY (show_tour_id) REFERENCES " . $gigpress['tours_table'] . " (tour_id)
		;";
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	
		dbDelta($create);
		
		add_option("gigpress_db_version", $gigpress['db_version']);
		add_option("gigpress_welcome", "yes");
		add_option("gigpress_css", "gigpress");
		add_option("gigpress_tour_heading", "h3");
		add_option("gigpress_date_format", "m/d/Y");
		add_option("gigpress_time_format", "g:ia");
		add_option("gigpress_noupcoming", "No shows booked at the moment.");
		add_option("gigpress_nopast", "No shows in the archive yet.");
		add_option("gigpress_widget_number", "3");
		add_option("gigpress_widget_heading", "Upcoming shows");
		add_option("gigpress_widget_segment", "1");
		add_option("gigpress_rss_upcoming", "1");
		add_option("gigpress_rss_list", "1");
		add_option("gigpress_rss_head", "1");
		add_option("gigpress_sidebar_link", "0");
		add_option("gigpress_user_level", "2");
		add_option("gigpress_display_country", "1");
	}
	
	// Since we added a 'time' field in 1.2, let's see if we need to upgrade the database
	
	$installed_db = get_option('gigpress_db_version');
	
	// If we do, we'll call the same SQL and use the dbDelta function to update it
	
	if($installed_db != $gigpress['db_version']) {
		
		$upgrade = "CREATE TABLE " . $gigpress['gigs_table'] . " (
		show_id INTEGER(4) AUTO_INCREMENT,
		show_tour_id INTEGER(4) NOT NULL,
		show_date DATE NOT NULL,
		show_multi INTEGER(1),
		show_time TIME NOT NULL,
		show_expire DATE NOT NULL,
		show_address VARCHAR(255),
		show_locale VARCHAR(255) NOT NULL,
		show_country VARCHAR(2) NOT NULL,
		show_price VARCHAR(32) DEFAULT 'Not sure',
		show_tix_url VARCHAR(255),
		show_venue VARCHAR(255) DEFAULT 'TBA' NOT NULL,
		show_venue_url VARCHAR(255),
		show_ages ENUM('All Ages','No minors','All Ages/Licensed','Not sure'),
		show_notes TEXT,
		PRIMARY KEY  (show_id)
		);
		CREATE TABLE " . $gigpress['tours_table'] . " (
		tour_id INTEGER(4) AUTO_INCREMENT,
		tour_name VARCHAR(255) NOT NULL,
		tour_created DATE NOT NULL,
		PRIMARY KEY  (tour_id)
		);
		ALTER TABLE " . $gigpress['gigs_table'] . " ADD FOREIGN KEY (show_tour_id) REFERENCES " . $gigpress['tours_table'] . " (tour_id)
		;";
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	
		dbDelta($upgrade);
		
		update_option("gigpress_db_version", $gigpress['db_version']);
		
		// Now we need to make sure the current show_dates in the DB are cloned to the show_expire fields
		
		// Get all shows where the show_multi is NULL
		$getshows = $wpdb->get_results("
			SELECT * FROM ".$gigpress['gigs_table']." WHERE show_multi IS NULL;
		");
		
		// Update each one's show_expire with its show_date
		if($getshows) {
			foreach($getshows as $show) {
				$setdates = $wpdb->query("
					UPDATE ".$gigpress['gigs_table']." SET show_expire = '".$show->show_date."' WHERE show_id = ".$show->show_id."
				");	
			}
		};
		
		// These two options are new in 1.2, and should be populated by default

		add_option("gigpress_user_level", "2");
		add_option("gigpress_display_country", "1");			
	
	}

}



// Add our WordPress admin pages, top-level-like. "Ill-advised," 
// sure, but methinks bands need will be on this page aplenty,
// so I wish not to bury it.



function gigpress_admin() {
	
	$gp_user_level = get_option('gigpress_user_level');

	add_menu_page('GigPress &rsaquo; Add a show', 'GigPress', $gp_user_level, __FILE__, 'gigpress_add');
	// By setting the unique identifier of the submenu page to be __FILE__,
	// we let it be the first page to load when the top-level menu
	// item is clicked
	add_submenu_page(__FILE__, 'GigPress &rsaquo; Add a show', 'Add a show', $gp_user_level, __FILE__, 'gigpress_add');
	add_submenu_page(__FILE__, 'GigPress &rsaquo; Upcoming shows', 'Upcoming shows', $gp_user_level, 'gp-upcoming', 'gigpress_admin_upcoming');
	add_submenu_page(__FILE__, 'GigPress &rsaquo; Tours', 'Tours', $gp_user_level, 'gp-tours', 'gigpress_tours');
	add_submenu_page(__FILE__, 'GigPress &rsaquo; Past shows', 'Past shows', $gp_user_level, 'gp-past', 'gigpress_admin_past');
	add_submenu_page(__FILE__, 'GigPress &rsaquo; Options', 'Options', $gp_user_level, 'gp-options', 'gigpress_options');

}



// ADMIN: ADD/EDIT SHOWS
// Here be the main admin page, where we add and edit shows ...
// ============================================================



function gigpress_add() {

	global $wpdb;
	global $gigpress;
		
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "add") {
			// This is for when we've just POST-ed a new show ...
			gigpress_add_show();
		}
		
	// If they're done with the welcome message, kill it
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "killwelcome") {
			update_option('gigpress_welcome','no');
	}
	
	// If the welcome message is to be displayed, then do so
	if(get_option('gigpress_welcome') == "yes") { ?>
	
		<div id="message" class="updated">
			<p><strong>Welcome to GigPress!</strong> You should first <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gp-options">enter your band name on the options page</a>, then get right into adding shows below. Questions?  Please check out the <a href="http://gigpress.com/docs">documentation</a> and <a href="http://gigpress.com/faq">FAQ</a> on the GigPress website. Enjoy! <small>(<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=killwelcome">Don't show this again.</a>)</small></p>
			
		</div>
		
	<?php } ?>
	
	<div class="wrap gigpress">
	
	<?php 	// We're about to edit an existing show ...
			
		if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") {
		
		// Check le nonce
		check_admin_referer('gigpress-action');
		// Load the previous show info into the edit form, and so forth 
		$showdata = $wpdb->get_results("
		SELECT * from ". $gigpress['gigs_table'] ." WHERE show_id = ". $_GET['show_id'] ." LIMIT 1
		");
			if($showdata) {
				// We got the goods from the DB, so let's get the date ready to
				// populate our date picker, get our variables jimmy-jazzed(?)
				// and proceed with the edit form ...
				
				foreach($showdata as $show) {
					
					$date = explode('-', $show->show_date);
						$mm = $date[1];
						$dd = $date[2];
						$yy = $date[0];
					
					$time = explode(':', $show->show_time);
						$hh = $time[0];
						$min = $time[1];
						
					$expire = explode('-', $show->show_expire);
						$exp_mm = $expire[1];
						$exp_dd = $expire[2];
						$exp_yy = $expire[0];
					
					$multi = $show->show_multi;	
					$locale = gigpress_sanitize($show->show_locale);
					$address = gigpress_sanitize($show->show_address);
					$venue = gigpress_sanitize($show->show_venue);
					$venue_url = gigpress_sanitize($show->show_venue_url);
					$country = $show->show_country;
					$admittance = $show->show_ages;
					$tixurl = gigpress_sanitize($show->show_tix_url);
					$notes = gigpress_sanitize($show->show_notes);
					$price = gigpress_sanitize($show->show_price);
					$showtour = $show->show_tour_id;				
				}
				
			} else {
		
				// Couldn't grab the show's details from the database
				// for some reason
				echo('<div id="message" class="error fade"><p>');
				_e('Sorry, but we had trouble loading that show for editing.');
				echo('</div>');
			}
		?>
	
	<h2><?php _e("Edit this show"); ?></h2>
	
	<form method="post" action="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=<?php echo $_GET['gpreturn']; ?>">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="update" />
		<input type="hidden" name="show_id" value="<?php echo $_GET['show_id']; ?>" />
	
	<?php } else { ?>
	
	<h2><?php _e("Add a show"); ?></h2>
		
	<form method="post" action="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=gigpress/gigpress.php">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="add" />
	<?php } ?>
		
		<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform gp-table">
		  <tr>
			<th width="33%" scope="row"><label for="mm"><strong><?php _e('Date:') ?></strong></label></th>
			<?php if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") { ?>
				<td><select name="mm" id="mm">
				  <option value="01"<?php if($mm == 01) echo(' selected="selected"'); ?>>January</option>
				  <option value="02"<?php if($mm == 02) echo(' selected="selected"'); ?>>February</option>
				  <option value="03"<?php if($mm == 03) echo(' selected="selected"'); ?>>March</option>
				  <option value="04"<?php if($mm == 04) echo(' selected="selected"'); ?>>April</option>
				  <option value="05"<?php if($mm == 05) echo(' selected="selected"'); ?>>May</option>
				  <option value="06"<?php if($mm == 06) echo(' selected="selected"'); ?>>June</option>
				  <option value="07"<?php if($mm == 07) echo(' selected="selected"'); ?>>July</option>
				  <option value="08"<?php if($mm == 08) echo(' selected="selected"'); ?>>August</option>
				  <option value="09"<?php if($mm == 09) echo(' selected="selected"'); ?>>September</option>
				  <option value="10"<?php if($mm == 10) echo(' selected="selected"'); ?>>October</option>
				  <option value="11"<?php if($mm == 11) echo(' selected="selected"'); ?>>November</option>
				  <option value="12"<?php if($mm == 12) echo(' selected="selected"'); ?>>December</option>
				</select>
				  <select name="dd" id="dd">
					<option value="01"<?php if($dd == 01) echo(' selected="selected"'); ?>>01</option>
					<option value="02"<?php if($dd == 02) echo(' selected="selected"'); ?>>02</option>
					<option value="03"<?php if($dd == 03) echo(' selected="selected"'); ?>>03</option>
					<option value="04"<?php if($dd == 04) echo(' selected="selected"'); ?>>04</option>
					<option value="05"<?php if($dd == 05) echo(' selected="selected"'); ?>>05</option>
					<option value="06"<?php if($dd == 06) echo(' selected="selected"'); ?>>06</option>
					<option value="07"<?php if($dd == 07) echo(' selected="selected"'); ?>>07</option>
					<option value="08"<?php if($dd == 08) echo(' selected="selected"'); ?>>08</option>
					<option value="09"<?php if($dd == 09) echo(' selected="selected"'); ?>>09</option>
					<option value="10"<?php if($dd == 10) echo(' selected="selected"'); ?>>10</option>
					<option value="11"<?php if($dd == 11) echo(' selected="selected"'); ?>>11</option>
					<option value="12"<?php if($dd == 12) echo(' selected="selected"'); ?>>12</option>
					<option value="13"<?php if($dd == 13) echo(' selected="selected"'); ?>>13</option>
					<option value="14"<?php if($dd == 14) echo(' selected="selected"'); ?>>14</option>
					<option value="15"<?php if($dd == 15) echo(' selected="selected"'); ?>>15</option>
					<option value="16"<?php if($dd == 16) echo(' selected="selected"'); ?>>16</option>
					<option value="17"<?php if($dd == 17) echo(' selected="selected"'); ?>>17</option>
					<option value="18"<?php if($dd == 18) echo(' selected="selected"'); ?>>18</option>
					<option value="19"<?php if($dd == 19) echo(' selected="selected"'); ?>>19</option>
					<option value="20"<?php if($dd == 20) echo(' selected="selected"'); ?>>20</option>
					<option value="21"<?php if($dd == 21) echo(' selected="selected"'); ?>>21</option>
					<option value="22"<?php if($dd == 22) echo(' selected="selected"'); ?>>22</option>
					<option value="23"<?php if($dd == 23) echo(' selected="selected"'); ?>>23</option>
					<option value="24"<?php if($dd == 24) echo(' selected="selected"'); ?>>24</option>
					<option value="25"<?php if($dd == 25) echo(' selected="selected"'); ?>>25</option>
					<option value="26"<?php if($dd == 26) echo(' selected="selected"'); ?>>26</option>
					<option value="27"<?php if($dd == 27) echo(' selected="selected"'); ?>>27</option>
					<option value="28"<?php if($dd == 28) echo(' selected="selected"'); ?>>28</option>
					<option value="29"<?php if($dd == 29) echo(' selected="selected"'); ?>>29</option>
					<option value="30"<?php if($dd == 30) echo(' selected="selected"'); ?>>30</option>
					<option value="31"<?php if($dd == 31) echo(' selected="selected"'); ?>>31</option>
				  </select>
				  <select name="yy" id="yy">
					<option value="2000"<?php if($yy == 2000) echo(' selected="selected"'); ?>>2000</option>
					<option value="2001"<?php if($yy == 2001) echo(' selected="selected"'); ?>>2001</option>
					<option value="2002"<?php if($yy == 2002) echo(' selected="selected"'); ?>>2002</option>
					<option value="2003"<?php if($yy == 2003) echo(' selected="selected"'); ?>>2003</option>
					<option value="2004"<?php if($yy == 2004) echo(' selected="selected"'); ?>>2004</option>
					<option value="2005"<?php if($yy == 2005) echo(' selected="selected"'); ?>>2005</option>
					<option value="2006"<?php if($yy == 2006) echo(' selected="selected"'); ?>>2006</option>
					<option value="2007"<?php if($yy == 2007) echo(' selected="selected"'); ?>>2007</option>
					<option value="2008"<?php if($yy == 2008) echo(' selected="selected"'); ?>>2008</option>
					<option value="2009"<?php if($yy == 2009) echo(' selected="selected"'); ?>>2009</option>
					<option value="2010"<?php if($yy == 2010) echo(' selected="selected"'); ?>>2010</option>
					<option value="2011"<?php if($yy == 2011) echo(' selected="selected"'); ?>>2011</option>
					<option value="2012"<?php if($yy == 2012) echo(' selected="selected"'); ?>>2012</option>
				  </select>
				&nbsp; at &nbsp; 
				<select name="hh" id="hh">
					<optgroup id="am" label="AM">
					  <option value="00"<?php if($hh == 00) echo(' selected="selected"'); ?>>12</option>
					  <option value="01"<?php if($hh == 01) echo(' selected="selected"'); ?>>01</option>
					  <option value="02"<?php if($hh == 02) echo(' selected="selected"'); ?>>02</option>
					  <option value="03"<?php if($hh == 03) echo(' selected="selected"'); ?>>03</option>
					  <option value="04"<?php if($hh == 04) echo(' selected="selected"'); ?>>04</option>
					  <option value="05"<?php if($hh == 05) echo(' selected="selected"'); ?>>05</option>
					  <option value="06"<?php if($hh == 06) echo(' selected="selected"'); ?>>06</option>
					  <option value="07"<?php if($hh == 07) echo(' selected="selected"'); ?>>07</option>
					  <option value="08"<?php if($hh == 08) echo(' selected="selected"'); ?>>08</option>
					  <option value="09"<?php if($hh == 09) echo(' selected="selected"'); ?>>09</option>
					  <option value="10"<?php if($hh == 10) echo(' selected="selected"'); ?>>10</option>
					  <option value="11"<?php if($hh == 11) echo(' selected="selected"'); ?>>11</option>
					</optgroup>
					<optgroup id="pm" label="PM">
					  <option value="12"<?php if($hh == 12) echo(' selected="selected"'); ?>>12</option>
					  <option value="13"<?php if($hh == 13) echo(' selected="selected"'); ?>>01</option>
					  <option value="14"<?php if($hh == 14) echo(' selected="selected"'); ?>>02</option>
					  <option value="15"<?php if($hh == 15) echo(' selected="selected"'); ?>>03</option>
					  <option value="16"<?php if($hh == 16) echo(' selected="selected"'); ?>>04</option>
					  <option value="17"<?php if($hh == 17) echo(' selected="selected"'); ?>>05</option>
					  <option value="18"<?php if($hh == 18) echo(' selected="selected"'); ?>>06</option>
					  <option value="19"<?php if($hh == 19) echo(' selected="selected"'); ?>>07</option>
					  <option value="20"<?php if($hh == 20) echo(' selected="selected"'); ?>>08</option>
					  <option value="21"<?php if($hh == 21) echo(' selected="selected"'); ?>>09</option>
					  <option value="22"<?php if($hh == 22) echo(' selected="selected"'); ?>>10</option>
					  <option value="23"<?php if($hh == 23) echo(' selected="selected"'); ?>>11</option>
					</optgroup>
				</select>
				<select name="min" id="min">
						<option value="00"<?php if($min == 00) echo(' selected="selected"'); ?>>00</option>
						<option value="05"<?php if($min == 05) echo(' selected="selected"'); ?>>05</option>
						<option value="10"<?php if($min == 10) echo(' selected="selected"'); ?>>10</option>
						<option value="15"<?php if($min == 15) echo(' selected="selected"'); ?>>15</option>
						<option value="20"<?php if($min == 20) echo(' selected="selected"'); ?>>20</option>
						<option value="25"<?php if($min == 25) echo(' selected="selected"'); ?>>25</option>
						<option value="30"<?php if($min == 30) echo(' selected="selected"'); ?>>30</option>
						<option value="35"<?php if($min == 35) echo(' selected="selected"'); ?>>35</option>
						<option value="40"<?php if($min == 40) echo(' selected="selected"'); ?>>40</option>
						<option value="45"<?php if($min == 45) echo(' selected="selected"'); ?>>45</option>
						<option value="50"<?php if($min == 50) echo(' selected="selected"'); ?>>50</option>
						<option value="55"<?php if($min == 55) echo(' selected="selected"'); ?>>55</option>
				</select>
			<?php } else { ?>
			<td><select name="mm" id="mm">
					<option value="01"<?php if(date('m') == 01) echo(' selected="selected"'); ?>>January</option>
			  		<option value="02"<?php if(date('m') == 02) echo(' selected="selected"'); ?>>February</option>
				  <option value="03"<?php if(date('m') == 03) echo(' selected="selected"'); ?>>March</option>
				  <option value="04"<?php if(date('m') == 04) echo(' selected="selected"'); ?>>April</option>
				  <option value="05"<?php if(date('m') == 05) echo(' selected="selected"'); ?>>May</option>
				  <option value="06"<?php if(date('m') == 06) echo(' selected="selected"'); ?>>June</option>
				  <option value="07"<?php if(date('m') == 07) echo(' selected="selected"'); ?>>July</option>
				  <option value="08"<?php if(date('m') == 08) echo(' selected="selected"'); ?>>August</option>
				  <option value="09"<?php if(date('m') == 09) echo(' selected="selected"'); ?>>September</option>
				  <option value="10"<?php if(date('m') == 10) echo(' selected="selected"'); ?>>October</option>
				  <option value="11"<?php if(date('m') == 11) echo(' selected="selected"'); ?>>November</option>
				  <option value="12"<?php if(date('m') == 12) echo(' selected="selected"'); ?>>December</option>
				</select>
				 <select name="dd" id="dd">
					<option value="01"<?php if(date('d') == 01) echo(' selected="selected"'); ?>>01</option>
					<option value="02"<?php if(date('d') == 02) echo(' selected="selected"'); ?>>02</option>
					<option value="03"<?php if(date('d') == 03) echo(' selected="selected"'); ?>>03</option>
					<option value="04"<?php if(date('d') == 04) echo(' selected="selected"'); ?>>04</option>
					<option value="05"<?php if(date('d') == 05) echo(' selected="selected"'); ?>>05</option>
					<option value="06"<?php if(date('d') == 06) echo(' selected="selected"'); ?>>06</option>
					<option value="07"<?php if(date('d') == 07) echo(' selected="selected"'); ?>>07</option>
					<option value="08"<?php if(date('d') == 08) echo(' selected="selected"'); ?>>08</option>
					<option value="09"<?php if(date('d') == 09) echo(' selected="selected"'); ?>>09</option>
					<option value="10"<?php if(date('d') == 10) echo(' selected="selected"'); ?>>10</option>
					<option value="11"<?php if(date('d') == 11) echo(' selected="selected"'); ?>>11</option>
					<option value="12"<?php if(date('d') == 12) echo(' selected="selected"'); ?>>12</option>
					<option value="13"<?php if(date('d') == 13) echo(' selected="selected"'); ?>>13</option>
					<option value="14"<?php if(date('d') == 14) echo(' selected="selected"'); ?>>14</option>
					<option value="15"<?php if(date('d') == 15) echo(' selected="selected"'); ?>>15</option>
					<option value="16"<?php if(date('d') == 16) echo(' selected="selected"'); ?>>16</option>
					<option value="17"<?php if(date('d') == 17) echo(' selected="selected"'); ?>>17</option>
					<option value="18"<?php if(date('d') == 18) echo(' selected="selected"'); ?>>18</option>
					<option value="19"<?php if(date('d') == 19) echo(' selected="selected"'); ?>>19</option>
					<option value="20"<?php if(date('d') == 20) echo(' selected="selected"'); ?>>20</option>
					<option value="21"<?php if(date('d') == 21) echo(' selected="selected"'); ?>>21</option>
					<option value="22"<?php if(date('d') == 22) echo(' selected="selected"'); ?>>22</option>
					<option value="23"<?php if(date('d') == 23) echo(' selected="selected"'); ?>>23</option>
					<option value="24"<?php if(date('d') == 24) echo(' selected="selected"'); ?>>24</option>
					<option value="25"<?php if(date('d') == 25) echo(' selected="selected"'); ?>>25</option>
					<option value="26"<?php if(date('d') == 26) echo(' selected="selected"'); ?>>26</option>
					<option value="27"<?php if(date('d') == 27) echo(' selected="selected"'); ?>>27</option>
					<option value="28"<?php if(date('d') == 28) echo(' selected="selected"'); ?>>28</option>
					<option value="29"<?php if(date('d') == 29) echo(' selected="selected"'); ?>>29</option>
					<option value="30"<?php if(date('d') == 30) echo(' selected="selected"'); ?>>30</option>
					<option value="31"<?php if(date('d') == 31) echo(' selected="selected"'); ?>>31</option>
				  </select>
				  <select name="yy" id="yy">
					<option value="2000">2000</option>
					<option value="2001">2001</option>
					<option value="2002">2002</option>
					<option value="2003">2003</option>
					<option value="2004">2004</option>
					<option value="2005">2005</option>
					<option value="2006">2006</option>
					<option value="2007">2007</option>
					<option value="2008"<?php if(date('Y') == 2008) echo(' selected="selected"'); ?>>2008</option>
					<option value="2009"<?php if(date('Y') == 2009) echo(' selected="selected"'); ?>>2009</option>
					<option value="2010"<?php if(date('Y') == 2010) echo(' selected="selected"'); ?>>2010</option>
					<option value="2011"<?php if(date('Y') == 2011) echo(' selected="selected"'); ?>>2011</option>
					<option value="2012"<?php if(date('Y') == 2012) echo(' selected="selected"'); ?>>2012</option>
				  </select>
				&nbsp; at &nbsp; 
					<select name="hh" id="hh">
					<optgroup id="am" label="AM">
					  <option value="00">12</option>
					  <option value="01">01</option>
					  <option value="02">02</option>
					  <option value="03">03</option>
					  <option value="04">04</option>
					  <option value="05">05</option>
					  <option value="06">06</option>
					  <option value="07">07</option>
					  <option value="08">08</option>
					  <option value="09">09</option>
					  <option value="10">10</option>
					  <option value="11">11</option>
					</optgroup>
					<optgroup id="pm" label="PM">
					  <option value="12">12</option>
					  <option value="13">01</option>
					  <option value="14">02</option>
					  <option value="15">03</option>
					  <option value="16">04</option>
					  <option value="17">05</option>
					  <option value="18">06</option>
					  <option value="19">07</option>
					  <option value="20" selected="selected">08</option>
					  <option value="21">09</option>
					  <option value="22">10</option>
					  <option value="23">11</option>
					</optgroup>
					</select>
					<select name="min" id="min">
						<option value="00" selected="selected">00</option>
						<option value="05">05</option>
						<option value="10">10</option>
						<option value="15">15</option>
						<option value="20">20</option>
						<option value="25">25</option>
						<option value="30">30</option>
						<option value="35">35</option>
						<option value="40">40</option>
						<option value="45">45</option>
						<option value="50">50</option>
						<option value="55">55</option>
					</select>
					<?php } ?>
					<span id="ampm">&nbsp;</span>
					<p class="gp-instructions">&nbsp;<label><input type="checkbox" value="1"<?php if($multi == "1") echo(' checked="checked"'); ?> id="multi" name="multi" />&nbsp;<?php _e('This is a multi-day event'); ?></label></p>
					</td>
				</tr>
				<!-- For multiple-day events -->
				<tr id="expire"<?php if($multi != 1) echo(' class="inactive"'); ?>>
					<th width="33%" scope="row"><label for="mm"><strong><?php _e('End date:') ?></strong></label></th>
					<?php if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") { ?>
						<td><select name="exp_mm" id="exp_mm">
						  <option value="01"<?php if($exp_mm == 01) echo(' selected="selected"'); ?>>January</option>
						  <option value="02"<?php if($exp_mm == 02) echo(' selected="selected"'); ?>>February</option>
						  <option value="03"<?php if($exp_mm == 03) echo(' selected="selected"'); ?>>March</option>
						  <option value="04"<?php if($exp_mm == 04) echo(' selected="selected"'); ?>>April</option>
						  <option value="05"<?php if($exp_mm == 05) echo(' selected="selected"'); ?>>May</option>
						  <option value="06"<?php if($exp_mm == 06) echo(' selected="selected"'); ?>>June</option>
						  <option value="07"<?php if($exp_mm == 07) echo(' selected="selected"'); ?>>July</option>
						  <option value="08"<?php if($exp_mm == 08) echo(' selected="selected"'); ?>>August</option>
						  <option value="09"<?php if($exp_mm == 09) echo(' selected="selected"'); ?>>September</option>
						  <option value="10"<?php if($exp_mm == 10) echo(' selected="selected"'); ?>>October</option>
						  <option value="11"<?php if($exp_mm == 11) echo(' selected="selected"'); ?>>November</option>
						  <option value="12"<?php if($exp_mm == 12) echo(' selected="selected"'); ?>>December</option>
						</select>
						  <select name="exp_dd" id="exp_dd">
							<option value="01"<?php if($exp_dd == 01) echo(' selected="selected"'); ?>>01</option>
							<option value="02"<?php if($exp_dd == 02) echo(' selected="selected"'); ?>>02</option>
							<option value="03"<?php if($exp_dd == 03) echo(' selected="selected"'); ?>>03</option>
							<option value="04"<?php if($exp_dd == 04) echo(' selected="selected"'); ?>>04</option>
							<option value="05"<?php if($exp_dd == 05) echo(' selected="selected"'); ?>>05</option>
							<option value="06"<?php if($exp_dd == 06) echo(' selected="selected"'); ?>>06</option>
							<option value="07"<?php if($exp_dd == 07) echo(' selected="selected"'); ?>>07</option>
							<option value="08"<?php if($exp_dd == 08) echo(' selected="selected"'); ?>>08</option>
							<option value="09"<?php if($exp_dd == 09) echo(' selected="selected"'); ?>>09</option>
							<option value="10"<?php if($exp_dd == 10) echo(' selected="selected"'); ?>>10</option>
							<option value="11"<?php if($exp_dd == 11) echo(' selected="selected"'); ?>>11</option>
							<option value="12"<?php if($exp_dd == 12) echo(' selected="selected"'); ?>>12</option>
							<option value="13"<?php if($exp_dd == 13) echo(' selected="selected"'); ?>>13</option>
							<option value="14"<?php if($exp_dd == 14) echo(' selected="selected"'); ?>>14</option>
							<option value="15"<?php if($exp_dd == 15) echo(' selected="selected"'); ?>>15</option>
							<option value="16"<?php if($exp_dd == 16) echo(' selected="selected"'); ?>>16</option>
							<option value="17"<?php if($exp_dd == 17) echo(' selected="selected"'); ?>>17</option>
							<option value="18"<?php if($exp_dd == 18) echo(' selected="selected"'); ?>>18</option>
							<option value="19"<?php if($exp_dd == 19) echo(' selected="selected"'); ?>>19</option>
							<option value="20"<?php if($exp_dd == 20) echo(' selected="selected"'); ?>>20</option>
							<option value="21"<?php if($exp_dd == 21) echo(' selected="selected"'); ?>>21</option>
							<option value="22"<?php if($exp_dd == 22) echo(' selected="selected"'); ?>>22</option>
							<option value="23"<?php if($exp_dd == 23) echo(' selected="selected"'); ?>>23</option>
							<option value="24"<?php if($exp_dd == 24) echo(' selected="selected"'); ?>>24</option>
							<option value="25"<?php if($exp_dd == 25) echo(' selected="selected"'); ?>>25</option>
							<option value="26"<?php if($exp_dd == 26) echo(' selected="selected"'); ?>>26</option>
							<option value="27"<?php if($exp_dd == 27) echo(' selected="selected"'); ?>>27</option>
							<option value="28"<?php if($exp_dd == 28) echo(' selected="selected"'); ?>>28</option>
							<option value="29"<?php if($exp_dd == 29) echo(' selected="selected"'); ?>>29</option>
							<option value="30"<?php if($exp_dd == 30) echo(' selected="selected"'); ?>>30</option>
							<option value="31"<?php if($exp_dd == 31) echo(' selected="selected"'); ?>>31</option>
						  </select>
						  <select name="exp_yy" id="exp_yy">
							<option value="2000"<?php if($exp_yy == 2000) echo(' selected="selected"'); ?>>2000</option>
							<option value="2001"<?php if($exp_yy == 2001) echo(' selected="selected"'); ?>>2001</option>
							<option value="2002"<?php if($exp_yy == 2002) echo(' selected="selected"'); ?>>2002</option>
							<option value="2003"<?php if($exp_yy == 2003) echo(' selected="selected"'); ?>>2003</option>
							<option value="2004"<?php if($exp_yy == 2004) echo(' selected="selected"'); ?>>2004</option>
							<option value="2005"<?php if($exp_yy == 2005) echo(' selected="selected"'); ?>>2005</option>
							<option value="2006"<?php if($exp_yy == 2006) echo(' selected="selected"'); ?>>2006</option>
							<option value="2007"<?php if($exp_yy == 2007) echo(' selected="selected"'); ?>>2007</option>
							<option value="2008"<?php if($exp_yy == 2008) echo(' selected="selected"'); ?>>2008</option>
							<option value="2009"<?php if($exp_yy == 2009) echo(' selected="selected"'); ?>>2009</option>
							<option value="2010"<?php if($exp_yy == 2010) echo(' selected="selected"'); ?>>2010</option>
							<option value="2011"<?php if($exp_yy == 2011) echo(' selected="selected"'); ?>>2011</option>
							<option value="2012"<?php if($exp_yy == 2012) echo(' selected="selected"'); ?>>2012</option>
						  </select>
						</td>
					<?php } else { ?>
						<td><select name="exp_mm" id="exp_mm">
						  <option value="01"<?php if(date('m') == 01) echo(' selected="selected"'); ?>>January</option>
						  <option value="02"<?php if(date('m') == 02) echo(' selected="selected"'); ?>>February</option>
						  <option value="03"<?php if(date('m') == 03) echo(' selected="selected"'); ?>>March</option>
						  <option value="04"<?php if(date('m') == 04) echo(' selected="selected"'); ?>>April</option>
						  <option value="05"<?php if(date('m') == 05) echo(' selected="selected"'); ?>>May</option>
						  <option value="06"<?php if(date('m') == 06) echo(' selected="selected"'); ?>>June</option>
						  <option value="07"<?php if(date('m') == 07) echo(' selected="selected"'); ?>>July</option>
						  <option value="08"<?php if(date('m') == 08) echo(' selected="selected"'); ?>>August</option>
						  <option value="09"<?php if(date('m') == 09) echo(' selected="selected"'); ?>>September</option>
						  <option value="10"<?php if(date('m') == 10) echo(' selected="selected"'); ?>>October</option>
						  <option value="11"<?php if(date('m') == 11) echo(' selected="selected"'); ?>>November</option>
						  <option value="12"<?php if(date('m') == 12) echo(' selected="selected"'); ?>>December</option>
						</select>
						  <select name="exp_dd" id="exp_dd">
							<option value="01"<?php if(date('d') == 01) echo(' selected="selected"'); ?>>01</option>
							<option value="02"<?php if(date('d') == 02) echo(' selected="selected"'); ?>>02</option>
							<option value="03"<?php if(date('d') == 03) echo(' selected="selected"'); ?>>03</option>
							<option value="04"<?php if(date('d') == 04) echo(' selected="selected"'); ?>>04</option>
							<option value="05"<?php if(date('d') == 05) echo(' selected="selected"'); ?>>05</option>
							<option value="06"<?php if(date('d') == 06) echo(' selected="selected"'); ?>>06</option>
							<option value="07"<?php if(date('d') == 07) echo(' selected="selected"'); ?>>07</option>
							<option value="08"<?php if(date('d') == 08) echo(' selected="selected"'); ?>>08</option>
							<option value="09"<?php if(date('d') == 09) echo(' selected="selected"'); ?>>09</option>
							<option value="10"<?php if(date('d') == 10) echo(' selected="selected"'); ?>>10</option>
							<option value="11"<?php if(date('d') == 11) echo(' selected="selected"'); ?>>11</option>
							<option value="12"<?php if(date('d') == 12) echo(' selected="selected"'); ?>>12</option>
							<option value="13"<?php if(date('d') == 13) echo(' selected="selected"'); ?>>13</option>
							<option value="14"<?php if(date('d') == 14) echo(' selected="selected"'); ?>>14</option>
							<option value="15"<?php if(date('d') == 15) echo(' selected="selected"'); ?>>15</option>
							<option value="16"<?php if(date('d') == 16) echo(' selected="selected"'); ?>>16</option>
							<option value="17"<?php if(date('d') == 17) echo(' selected="selected"'); ?>>17</option>
							<option value="18"<?php if(date('d') == 18) echo(' selected="selected"'); ?>>18</option>
							<option value="19"<?php if(date('d') == 19) echo(' selected="selected"'); ?>>19</option>
							<option value="20"<?php if(date('d') == 20) echo(' selected="selected"'); ?>>20</option>
							<option value="21"<?php if(date('d') == 21) echo(' selected="selected"'); ?>>21</option>
							<option value="22"<?php if(date('d') == 22) echo(' selected="selected"'); ?>>22</option>
							<option value="23"<?php if(date('d') == 23) echo(' selected="selected"'); ?>>23</option>
							<option value="24"<?php if(date('d') == 24) echo(' selected="selected"'); ?>>24</option>
							<option value="25"<?php if(date('d') == 25) echo(' selected="selected"'); ?>>25</option>
							<option value="26"<?php if(date('d') == 26) echo(' selected="selected"'); ?>>26</option>
							<option value="27"<?php if(date('d') == 27) echo(' selected="selected"'); ?>>27</option>
							<option value="28"<?php if(date('d') == 28) echo(' selected="selected"'); ?>>28</option>
							<option value="29"<?php if(date('d') == 29) echo(' selected="selected"'); ?>>29</option>
							<option value="30"<?php if(date('d') == 30) echo(' selected="selected"'); ?>>30</option>
							<option value="31"<?php if(date('d') == 31) echo(' selected="selected"'); ?>>31</option>
						  </select>
						  <select name="exp_yy" id="exp_yy">
							<option value="2000">2000</option>
							<option value="2001">2001</option>
							<option value="2002">2002</option>
							<option value="2003">2003</option>
							<option value="2004">2004</option>
							<option value="2005">2005</option>
							<option value="2006">2006</option>
							<option value="2007">2007</option>
							<option value="2008"<?php if(date('Y') == 2008) echo(' selected="selected"'); ?>>2008</option>
							<option value="2009"<?php if(date('Y') == 2009) echo(' selected="selected"'); ?>>2009</option>
							<option value="2010"<?php if(date('Y') == 2010) echo(' selected="selected"'); ?>>2010</option>
							<option value="2011"<?php if(date('Y') == 2011) echo(' selected="selected"'); ?>>2011</option>
							<option value="2012"<?php if(date('Y') == 2012) echo(' selected="selected"'); ?>>2012</option>
						  </select>
						<?php } ?>
						</tr>
			<tr>
				<th width="33%" scope="row"><label for="show_locale"><strong><?php _e('City:') ?></strong></label></th>
				<td><input type="text" size="48" name="show_locale" value="<?php echo $locale; ?>" /><br />
				<p class="gp-instructions"><?php _e('I&rsquo;d suggest a format along the lines of: <strong>Winnipeg, MB</strong>.'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_venue"><strong><?php _e('Venue:') ?></strong></label></th>
				<td><input type="text" size="48" name="show_venue" id="show_venue" value="<?php echo $venue; ?>" /><br />
				<p class="gp-instructions"><?php _e('This is a required field, so if you don&rsquo;t know, just put <strong>TBA</strong>.'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_venue_url"><?php _e('Venue website:') ?></label></th>
				<td><input type="text" size="48" name="show_venue_url" id="show_venue_url" value="<?php echo $venue_url; ?>" /><br />
				<p class="gp-instructions"><?php _e('Any value entered here will be added as a hyperlink attached to the name of the venue.'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_address"><?php _e('Address:') ?></label></th>
				<td><input type="text" size="48" name="show_address" id="show_address" value="<?php echo $address; ?>" /><br />
				<p class="gp-instructions"><?php _e('If you enter a street address, it will enable auto-linkage to Google Maps.  True that, double true.'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_country"><?php _e('Country:') ?></label></th>
				<?php // We'll stack what we think are the most often-used countries at the top, and make them "sticky" for when you're editing.
				// Too lazy to make the other 143 countries sticky as well. */ ?>
				<td><select name="show_country" id="show_country">
						<option value="AU"<?php if($country == "AU") echo(' selected="selected"'); ?>>Australia</option>
							<option value="AT"<?php if($country == "AT") echo(' selected="selected"'); ?>>Austria</option>
							<option value="BR"<?php if($country == "BR") echo(' selected="selected"'); ?>>Brazil</option>
							<option value="CA"<?php if($country == "CA") echo(' selected="selected"'); ?>>Canada</option>
							<option value="DK"<?php if($country == "DK") echo(' selected="selected"'); ?>>Denmark</option>
							<option value="FR"<?php if($country == "FR") echo(' selected="selected"'); ?>>France</option>
							<option value="DE"<?php if($country == "DE") echo(' selected="selected"'); ?>>Germany</option>
							<option value="IT"<?php if($country == "IT") echo(' selected="selected"'); ?>>Italy</option>
							<option value="JP"<?php if($country == "JP") echo(' selected="selected"'); ?>>Japan</option>
							<option value="MX"<?php if($country == "MX") echo(' selected="selected"'); ?>>Mexico</option>
							<option value="ES"<?php if($country == "ES") echo(' selected="selected"'); ?>>Spain</option>
							<option value="SE"<?php if($country == "SE") echo(' selected="selected"'); ?>>Sweden</option>
							<option value="CH"<?php if($country == "CH") echo(' selected="selected"'); ?>>Switzerland</option>
							<option value="US"<?php if($country == "US" || $_GET['gpaction'] != "edit") echo(' selected="selected"'); ?>>United States</option>
							<option value="GB"<?php if($country == "GB") echo(' selected="selected"'); ?>>United Kingdom</option>
							<option value="">-------------</option>
							<option value="AF"<?php if($country == "AF") echo(' selected="selected"'); ?>>Afghanistan</option>
							<option value="AG"<?php if($country == "AG") echo(' selected="selected"'); ?>>Antigua and Barbuda</option>
							<option value="AI"<?php if($country == "AI") echo(' selected="selected"'); ?>>Anguilla</option>
							<option value="AL"<?php if($country == "AL") echo(' selected="selected"'); ?>>Albania</option>
							<option value="AM"<?php if($country == "AM") echo(' selected="selected"'); ?>>Armenia</option>
							<option value="DZ"<?php if($country == "DZ") echo(' selected="selected"'); ?>>Algeria</option>
							<option value="AD"<?php if($country == "AD") echo(' selected="selected"'); ?>>Andorra</option>
							<option value="AO"<?php if($country == "AO") echo(' selected="selected"'); ?>>Angola</option>
							<option value="AR"<?php if($country == "AR") echo(' selected="selected"'); ?>>Argentina</option>
							<option value="AW"<?php if($country == "AW") echo(' selected="selected"'); ?>>Aruba</option>
							<option value="BS"<?php if($country == "BS") echo(' selected="selected"'); ?>>Bahamas</option>
							<option value="BH"<?php if($country == "BH") echo(' selected="selected"'); ?>>Bahrain</option>
							<option value="BD"<?php if($country == "BD") echo(' selected="selected"'); ?>>Bangladesh</option>
							<option value="BB"<?php if($country == "BB") echo(' selected="selected"'); ?>>Barbados</option>
							<option value="BE"<?php if($country == "BE") echo(' selected="selected"'); ?>>Belgium</option>
							<option value="BZ"<?php if($country == "BZ") echo(' selected="selected"'); ?>>Belize</option>
							<option value="BM"<?php if($country == "BM") echo(' selected="selected"'); ?>>Bermuda</option>
							<option value="BT"<?php if($country == "BT") echo(' selected="selected"'); ?>>Bhutan</option>
							<option value="BO"<?php if($country == "BO") echo(' selected="selected"'); ?>>Bolivia</option>
							<option value="BA"<?php if($country == "BA") echo(' selected="selected"'); ?>>Bosnia and Herzegovina</option>
							<option value="BW"<?php if($country == "BW") echo(' selected="selected"'); ?>>Botswana</option>
							<option value="BN"<?php if($country == "BN") echo(' selected="selected"'); ?>>Brunei</option>
							<option value="BG"<?php if($country == "BG") echo(' selected="selected"'); ?>>Bulgaria</option>
							<option value="BI"<?php if($country == "BI") echo(' selected="selected"'); ?>>Burundi</option>
							<option value="KH"<?php if($country == "KH") echo(' selected="selected"'); ?>>Cambodia</option>
							<option value="CV"<?php if($country == "CV") echo(' selected="selected"'); ?>>Cape Verde</option>
							<option value="KY"<?php if($country == "KY") echo(' selected="selected"'); ?>>Cayman Islands</option>
							<option value="CF"<?php if($country == "CF") echo(' selected="selected"'); ?>>Central African Republic</option>
							<option value="CL"<?php if($country == "CL") echo(' selected="selected"'); ?>>Chile</option>
							<option value="CN"<?php if($country == "CN") echo(' selected="selected"'); ?>>China</option>
							<option value="CO"<?php if($country == "CO") echo(' selected="selected"'); ?>>Colombia</option>
							<option value="KM"<?php if($country == "KM") echo(' selected="selected"'); ?>>Comoros</option>
							<option value="CR"<?php if($country == "CR") echo(' selected="selected"'); ?>>Costa Rica</option>
							<option value="HR"<?php if($country == "HR") echo(' selected="selected"'); ?>>Croatia</option>
							<option value="CU"<?php if($country == "CU") echo(' selected="selected"'); ?>>Cuba</option>
							<option value="CY"<?php if($country == "CY") echo(' selected="selected"'); ?>>Cyprus</option>
							<option value="CZ"<?php if($country == "CZ") echo(' selected="selected"'); ?>>Czech Republic</option>
							<option value="DJ"<?php if($country == "DJ") echo(' selected="selected"'); ?>>Djibouti</option>
							<option value="DO"<?php if($country == "DO") echo(' selected="selected"'); ?>>Dominican Republic</option>
							<option value="EC"<?php if($country == "EC") echo(' selected="selected"'); ?>>Ecuador</option>
							<option value="EG"<?php if($country == "EG") echo(' selected="selected"'); ?>>Egypt</option>
							<option value="SV"<?php if($country == "SV") echo(' selected="selected"'); ?>>El Salvador</option>
							<option value="EE"<?php if($country == "EE") echo(' selected="selected"'); ?>>Estonia</option>
							<option value="ET"<?php if($country == "ET") echo(' selected="selected"'); ?>>Ethiopia</option>
							<option value="FK"<?php if($country == "FK") echo(' selected="selected"'); ?>>Falkland Islands</option>
							<option value="FJ"<?php if($country == "FJ") echo(' selected="selected"'); ?>>Fiji</option>
							<option value="FI"<?php if($country == "FI") echo(' selected="selected"'); ?>>Finland</option>
							<option value="GM"<?php if($country == "GM") echo(' selected="selected"'); ?>>Gambia</option>
							<option value="GH"<?php if($country == "GH") echo(' selected="selected"'); ?>>Ghana</option>
							<option value="GI"<?php if($country == "GI") echo(' selected="selected"'); ?>>Gibraltar</option>
							<option value="GR"<?php if($country == "GR") echo(' selected="selected"'); ?>>Greece</option>
							<option value="GT"<?php if($country == "GT") echo(' selected="selected"'); ?>>Guatemala</option>
							<option value="GN"<?php if($country == "GN") echo(' selected="selected"'); ?>>Guinea</option>
							<option value="GY"<?php if($country == "GY") echo(' selected="selected"'); ?>>Guyana</option>
							<option value="HT"<?php if($country == "HT") echo(' selected="selected"'); ?>>Haiti</option>
							<option value="HN"<?php if($country == "HN") echo(' selected="selected"'); ?>>Honduras</option>
							<option value="HK"<?php if($country == "HK") echo(' selected="selected"'); ?>>Hong Kong</option>
							<option value="HU"<?php if($country == "HU") echo(' selected="selected"'); ?>>Hungary</option>
							<option value="IS"<?php if($country == "IS") echo(' selected="selected"'); ?>>Iceland</option>
							<option value="IN"<?php if($country == "IN") echo(' selected="selected"'); ?>>India</option>
							<option value="ID"<?php if($country == "ID") echo(' selected="selected"'); ?>>Indonesia</option>
							<option value="IR"<?php if($country == "IR") echo(' selected="selected"'); ?>>Iran</option>
							<option value="IQ"<?php if($country == "IQ") echo(' selected="selected"'); ?>>Iraq</option>
							<option value="IE"<?php if($country == "IE") echo(' selected="selected"'); ?>>Ireland</option>
							<option value="IL"<?php if($country == "IL") echo(' selected="selected"'); ?>>Israel</option>
							<option value="JM"<?php if($country == "JM") echo(' selected="selected"'); ?>>Jamaica</option>
							<option value="JO"<?php if($country == "JO") echo(' selected="selected"'); ?>>Jordan</option>
							<option value="KZ"<?php if($country == "KZ") echo(' selected="selected"'); ?>>Kazakhstan</option>
							<option value="KE"<?php if($country == "KE") echo(' selected="selected"'); ?>>Kenya</option>
							<option value="KW"<?php if($country == "KW") echo(' selected="selected"'); ?>>Kuwait</option>
							<option value="LA"<?php if($country == "LA") echo(' selected="selected"'); ?>>Laos</option>
							<option value="LV"<?php if($country == "LV") echo(' selected="selected"'); ?>>Latvia</option>
							<option value="LB"<?php if($country == "LB") echo(' selected="selected"'); ?>>Lebanon</option>
							<option value="LS"<?php if($country == "LS") echo(' selected="selected"'); ?>>Lesotho</option>
							<option value="LR"<?php if($country == "LR") echo(' selected="selected"'); ?>>Liberia</option>
							<option value="LY"<?php if($country == "LY") echo(' selected="selected"'); ?>>Libya</option>
							<option value="LT"<?php if($country == "LT") echo(' selected="selected"'); ?>>Lithuania</option>
							<option value="LU"<?php if($country == "LU") echo(' selected="selected"'); ?>>Luxembourg</option>
							<option value="MO"<?php if($country == "MO") echo(' selected="selected"'); ?>>Macao</option>
							<option value="MG"<?php if($country == "MG") echo(' selected="selected"'); ?>>Madagascar</option>
							<option value="MW"<?php if($country == "MW") echo(' selected="selected"'); ?>>Malawi</option>
							<option value="MY"<?php if($country == "MY") echo(' selected="selected"'); ?>>Malaysia</option>
							<option value="MV"<?php if($country == "MV") echo(' selected="selected"'); ?>>Maldives</option>
							<option value="MT"<?php if($country == "MT") echo(' selected="selected"'); ?>>Malta</option>
							<option value="MR"<?php if($country == "MR") echo(' selected="selected"'); ?>>Mauritania</option>
							<option value="MU"<?php if($country == "MU") echo(' selected="selected"'); ?>>Mauritius</option>
							<option value="MN"<?php if($country == "MN") echo(' selected="selected"'); ?>>Mongolia</option>
							<option value="ME"<?php if($country == "ME") echo(' selected="selected"'); ?>>Montenegro</option>
							<option value="MA"<?php if($country == "MA") echo(' selected="selected"'); ?>>Morocco</option>
							<option value="MZ"<?php if($country == "MZ") echo(' selected="selected"'); ?>>Mozambique</option>
							<option value="MM"<?php if($country == "MM") echo(' selected="selected"'); ?>>Myanmar</option>
							<option value="NA"<?php if($country == "NA") echo(' selected="selected"'); ?>>Namibia</option>
							<option value="NP"<?php if($country == "NP") echo(' selected="selected"'); ?>>Nepal</option>
							<option value="NL"<?php if($country == "NL") echo(' selected="selected"'); ?>>Netherlands</option>
							<option value="AN"<?php if($country == "AN") echo(' selected="selected"'); ?>>Netherlands Antilles</option>
							<option value="NZ"<?php if($country == "NZ") echo(' selected="selected"'); ?>>New Zealand</option>
							<option value="NI"<?php if($country == "NI") echo(' selected="selected"'); ?>>Nicaragua</option>
							<option value="NG"<?php if($country == "NG") echo(' selected="selected"'); ?>>Nigeria</option>
							<option value="KP"<?php if($country == "KP") echo(' selected="selected"'); ?>>North Korea</option>
							<option value="NO"<?php if($country == "NO") echo(' selected="selected"'); ?>>Norway</option>
							<option value="OM"<?php if($country == "OM") echo(' selected="selected"'); ?>>Oman</option>
							<option value="PK"<?php if($country == "PK") echo(' selected="selected"'); ?>>Pakistan</option>
							<option value="PA"<?php if($country == "PA") echo(' selected="selected"'); ?>>Panama</option>
							<option value="PG"<?php if($country == "PG") echo(' selected="selected"'); ?>>Papua New Guinea</option>
							<option value="PY"<?php if($country == "PY") echo(' selected="selected"'); ?>>Paraguay</option>
							<option value="PE"<?php if($country == "PE") echo(' selected="selected"'); ?>>Peru</option>
							<option value="PH"<?php if($country == "PH") echo(' selected="selected"'); ?>>Philippines</option>
							<option value="PL"<?php if($country == "PL") echo(' selected="selected"'); ?>>Poland</option>
							<option value="PT"<?php if($country == "PT") echo(' selected="selected"'); ?>>Portugal</option>
							<option value="QA"<?php if($country == "QA") echo(' selected="selected"'); ?>>Qatar</option>
							<option value="RO"<?php if($country == "RO") echo(' selected="selected"'); ?>>Romania</option>
							<option value="RU"<?php if($country == "RU") echo(' selected="selected"'); ?>>Russia</option>
							<option value="WS"<?php if($country == "WS") echo(' selected="selected"'); ?>>Samoa</option>
							<option value="ST"<?php if($country == "ST") echo(' selected="selected"'); ?>>Sao Tome/Principe</option>
							<option value="SA"<?php if($country == "SA") echo(' selected="selected"'); ?>>Saudi Arabia</option>
							<option value="RS"<?php if($country == "RS") echo(' selected="selected"'); ?>>Serbia</option>
							<option value="SC"<?php if($country == "SC") echo(' selected="selected"'); ?>>Seychelles</option>
							<option value="SL"<?php if($country == "SL") echo(' selected="selected"'); ?>>Sierra Leone</option>
							<option value="SG"<?php if($country == "SG") echo(' selected="selected"'); ?>>Singapore</option>
							<option value="SK"<?php if($country == "SK") echo(' selected="selected"'); ?>>Slovakia</option>
							<option value="SI"<?php if($country == "SI") echo(' selected="selected"'); ?>>Slovenia</option>
							<option value="SB"<?php if($country == "SB") echo(' selected="selected"'); ?>>Solomon Islands</option>
							<option value="SO"<?php if($country == "SO") echo(' selected="selected"'); ?>>Somalia</option>
							<option value="ZA"<?php if($country == "ZA") echo(' selected="selected"'); ?>>South Africa</option>
							<option value="KR"<?php if($country == "KR") echo(' selected="selected"'); ?>>South Korea</option>
							<option value="LK"<?php if($country == "LK") echo(' selected="selected"'); ?>>Sri Lanka</option>
							<option value="SH"<?php if($country == "SH") echo(' selected="selected"'); ?>>Saint Helena</option>
							<option value="SD"<?php if($country == "SD") echo(' selected="selected"'); ?>>Sudan</option>
							<option value="SR"<?php if($country == "SR") echo(' selected="selected"'); ?>>Suriname</option>
							<option value="SZ"<?php if($country == "SZ") echo(' selected="selected"'); ?>>Swaziland</option>
							<option value="SY"<?php if($country == "SY") echo(' selected="selected"'); ?>>Syria</option>
							<option value="TW"<?php if($country == "TW") echo(' selected="selected"'); ?>>Taiwan</option>
							<option value="TZ"<?php if($country == "TZ") echo(' selected="selected"'); ?>>Tanzania</option>
							<option value="TH"<?php if($country == "TH") echo(' selected="selected"'); ?>>Thailand</option>
							<option value="TO"<?php if($country == "TO") echo(' selected="selected"'); ?>>Tonga</option>
							<option value="TT"<?php if($country == "TT") echo(' selected="selected"'); ?>>Trinidad/Tobago</option>
							<option value="TN"<?php if($country == "TN") echo(' selected="selected"'); ?>>Tunisia</option>
							<option value="TR"<?php if($country == "TR") echo(' selected="selected"'); ?>>Turkey</option>
							<option value="UG"<?php if($country == "UG") echo(' selected="selected"'); ?>>Uganda</option>
							<option value="UA"<?php if($country == "UA") echo(' selected="selected"'); ?>>Ukraine</option>
							<option value="UY"<?php if($country == "UY") echo(' selected="selected"'); ?>>Uruguay</option>
							<option value="AE"<?php if($country == "AE") echo(' selected="selected"'); ?>>United Arab Emirates</option>
							<option value="VU"<?php if($country == "VU") echo(' selected="selected"'); ?>>Vanuatu</option>
							<option value="VE"<?php if($country == "VE") echo(' selected="selected"'); ?>>Venezuela</option>
							<option value="VN"<?php if($country == "VN") echo(' selected="selected"'); ?>>Vietnam</option>
							<option value="ZM"<?php if($country == "ZM") echo(' selected="selected"'); ?>>Zambia</option>
							<option value="ZW"<?php if($country == "ZW") echo(' selected="selected"'); ?>>Zimbabwe</option>
					</select>
			</td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_ages"><?php _e('Admittance:') ?></label></th>
				<td><select name="show_ages" id="show_ages">
				  <option value="Not sure"<?php if($admittance == "Not sure") echo(' selected="selected"'); ?>><?php _e('Not sure') ?></option>
				  <option value="No minors"<?php if($admittance == "No minors") echo(' selected="selected"'); ?>><?php _e('No minors') ?></option>
				  <option value="All Ages/Licensed"<?php if($admittance == "All Ages/Licensed") echo(' selected="selected"'); ?>><?php _e('All Ages/Licensed') ?></option>
				  <option value="All Ages"<?php if($admittance == "All Ages") echo(' selected="selected"'); ?>><?php _e('All Ages') ?></option>
				  </select>
				</td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_price"><?php _e('Price:') ?></label></th>
				<td><input type="text" size="10" name="show_price" id="show_price" value="<?php echo $price; ?>" /><br />
				<p class="gp-instructions"><?php _e('To accomodate various currency display formats, no auto-formatting will be applied to what you enter here. Keep that in mind!'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_tix_url"><?php _e('Ticket URL:') ?></label></th>
				<td><input type="text" size="48" name="show_tix_url" id="show_tix_url" value="<?php echo $tixurl; ?>" /><br />
				<p class="gp-instructions"><?php _e('This will enable the &ldquo;buy link&rdquo; when displaying your tour dates.'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_notes"><?php _e('Notes:') ?></label></th>
				<td><textarea name="show_notes" id="show_notes" cols="45" rows="5"><?php echo $notes; ?></textarea>
				<br />
				<p class="gp-instructions"><?php _e('Use this space to list opening (or headlining) bands, &ldquo;presented by&rdquo; info, or whatever else you like.<br />
				Keep it brief, and maybe don&rsquo;t try to display images inline in this field.'); ?></p></td>
			  </tr>
			  <tr>
				<th width="33%" scope="row"><label for="show_tour_id"><?php _e('Part of a tour?') ?></label></th>
				<td><select name="show_tour_id" id="show_tour_id">
				  <option value="0"><?php _e('Nope') ?></option>
				  <option value="0">-------------</option>
				  <?php $tours = $wpdb->get_results("
						SELECT * FROM ". $gigpress['tours_table'] ." ORDER BY tour_created DESC
						");
						if($tours != FALSE) {
							foreach($tours as $tour) {
								$tourname = gigpress_sanitize($tour->tour_name);
								echo("<option value=\"$tour->tour_id\"");
								if($showtour == $tour->tour_id) echo(' selected="selected"');
								echo(">$tourname</option>\n\t\t\t");
							}
						} else {
							echo("<option value=\"0\">No tours in the database</option>/n/t/t/t");
						}
					?>
				  </select>
				  <br />
				<p class="gp-instructions"><?php _e('If this show is part of a tour you&rsquo;ve already entered into GigPress, select it here.<br />
				No tours yet?'); ?> <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gp-tours"><?php _e("Create them here."); ?></a></p>
				</td>
			  </tr>
		  <tr>
			<th width="33%" scope="row">&nbsp;</th>
			<td>
			<?php if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") { ?>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update show') ?>" /></p></td>
			<?php } else { ?>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Add show') ?>" /></p></td>
			<?php } ?>
		</tr>
		</table>		

	</form>
	<?php gigpress_footer() ?>
	</div>
	
<?php }



// ADMIN: UPCOMING SHOWS
// Here be the upcoming shows admin page, where we can see and manage
// upcoming shows in the database ...
// ==================================


function gigpress_admin_upcoming() {

	global $wpdb;
	global $gigpress;
	global $tourshows;
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "delete") {
		gigpress_delete_show();		
	}
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "update") {
		gigpress_update_show();
	} ?>
		
	<div class="wrap gigpress">

	<h2><?php _e("Upcoming shows"); ?></h2>
		
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e("Date"); ?></th>
					<th scope="col"><?php _e("City"); ?></th>
					<th scope="col"><?php _e("Venue"); ?></th>
					<th scope="col"><?php _e("Address"); ?></th>
					<th scope="col"><?php _e("Country"); ?></th>
					<th scope="col"><?php _e("Admittance"); ?></th>
					<th scope="col" class="gp-centre"><?php _e("Price"); ?></th>
					<th scope="col"><?php _e("Buy link"); ?></th>
					<th colspan="2" class="gp-centre" scope="col"><?php _e("Actions"); ?></th>
				</tr>
			</thead>
	
	<?php
		
		// Retrieve all tours from the DB
		$tours = $wpdb->get_results("SELECT * FROM ". $gigpress['tours_table'] ." ORDER BY tour_created ASC");
		
		// If there are indeed tours to be found
		if($tours != FALSE) {
			
			foreach($tours as $tour) {
			
				// See if each tour actually has any shows assigned to it
				$shows = $wpdb->get_results("
					SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND show_expire >= '". current_time('mysql') ."' ORDER BY show_date ASC
				");
				
				// If there are shows for the tour, display that shiznatt
				if($shows != FALSE) { ?>
				
				<tr>
					<th colspan="10" class="gp-thead"><h3><?php _e('Tour: '); echo $tour->tour_name; ?></h3></th>
				</tr>
		
				<?php gigpress_admin_table($shows, "tour", "upcoming");
			
				} // if there are no shows, do nothing
				
			} // end foreach tour

		} // And if there aren't any tours, do nothing

		// Get all upcoming dates from the DB that are NOT part of a tour
		
		$upcoming = $wpdb->get_results("
			SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire >= '". current_time('mysql') ."' AND show_tour_id = 0 ORDER BY show_date ASC");
		
		// Do we have dates?
		if($upcoming != FALSE) {
			
			if($tourshows == TRUE) { ?>
			<tr>
				<th colspan="10" class="gp-thead"><h3><?php _e("Individual shows"); ?></h3></th>
			</tr>
			
			<?php }
		
			gigpress_admin_table($upcoming, "individual", "upcoming");
			
		} // And if there aren't any individual shows, move on
		
		if($tourshows != TRUE && $upcoming == FALSE) {
		// We don't have shows of any kind to show you ?>
			<tr><td colspan="10"><strong><?php echo gigpress_sanitize(get_option('gigpress_noupcoming')); ?></strong></td></tr>
		<?php } ?>
		</table>
	<?php gigpress_footer() ?>
	</div>

<?php 

}


// ADMIN: PAST SHOWS
// Here be the past shows admin page, where we can see and manage shows
// in the database that happened before today...
// =============================================


function gigpress_admin_past() {

	global $wpdb;
	global $gigpress;
	global $tourshows;
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "delete") {
		gigpress_delete_show();		
	}
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "update") {
		gigpress_update_show();
	} ?>

	<div class="wrap gigpress">

	<h2><?php _e("Past shows"); ?></h2>
	
	<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e("Date"); ?></th>
					<th scope="col"><?php _e("City"); ?></th>
					<th scope="col"><?php _e("Venue"); ?></th>
					<th scope="col"><?php _e("Address"); ?></th>
					<th scope="col"><?php _e("Country"); ?></th>
					<th scope="col"><?php _e("Admittance"); ?></th>
					<th scope="col" class="gp-centre"><?php _e("Price"); ?></th>
					<th scope="col"><?php _e("Buy link"); ?></th>
					<th colspan="2" class="gp-centre" scope="col"><?php _e("Actions"); ?></th>
				</tr>
			</thead>
	
	<?php
		
		// Retrieve all tours from the DB
		$tours = $wpdb->get_results("SELECT * FROM ". $gigpress['tours_table'] ." ORDER BY tour_created ASC");
		
		// If there are indeed tours to be found
		if($tours != FALSE) {
		
			foreach($tours as $tour) {
			
				// See if each tour actually has any shows assigned to it
				$shows = $wpdb->get_results("
					SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND show_expire < '". current_time('mysql') ."' ORDER BY show_date DESC
				");
				
				// If there are shows for the tour, display that shiznatt
				if($shows != FALSE) { ?>
				
				<tr>
					<th colspan="10" class="gp-thead"><h3><?php _e('Tour: '); echo $tour->tour_name; ?></h3></th>
				</tr>
		
				<?php gigpress_admin_table($shows, "tour", "past");
			
				} // if there are no shows, do nothing
				
			} // end foreach tour

		} // And if there aren't any tours, do nothing
		
		// Get all upcoming dates from the DB that are NOT part of a tour
		
		$past = $wpdb->get_results("
			SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire < '". current_time('mysql') ."' AND show_tour_id = 0 ORDER BY show_date DESC");
		
		// Do we have dates?
		if($past != FALSE) { ?>
			
			<?php if($tourshows == TRUE) { ?>
			<tr>
				<th colspan="10" class="gp-thead"><h3><?php _e("Individual shows"); ?></h3></th>
			</tr>
			
			<?php }
		
			gigpress_admin_table($past, "individual", "past");
			
		} // And if there aren't any individual shows, move on
		
		if($tourshows != TRUE && $past == FALSE) {
		// We don't have shows of any kind to show you ?>
			<tr><td colspan="10"><strong><?php echo gigpress_sanitize(get_option('gigpress_nopast')); ?></strong></td></tr>
		<?php } ?>
		</table>
	<?php gigpress_footer() ?>
	</div>

<?php }



// OUTPUT: ADMIN SHOW TABLES
// =========================


function gigpress_admin_table($shows, $type, $scope) {
		
	foreach($shows as $show) {

		// Register that we have a tour with shows
		if($type == "tour") {
			global $tourshows;
			$tourshows = TRUE;
		}

		// Format the date as per our options page
		$date = mysql2date(get_option('gigpress_date_format'), $show->show_date);
		$expire = mysql2date(get_option('gigpress_date_format'), $show->show_expire);
		$time = gigpress_timefy($show->show_time);

		// Make mappy, check urls
		$address = gigpress_mapit(gigpress_sanitize($show->show_address), gigpress_sanitize($show->show_locale), $show->show_country);
		$venue_url = gigpress_check_url(gigpress_sanitize($show->show_venue_url));
		$tix_url = gigpress_check_url(gigpress_sanitize($show->show_tix_url));

		// Link up the venue name if it has a url associated with it
		if(!empty($show->show_venue_url)) {
			$venue = "<a href=\"$venue_url\">".gigpress_sanitize($show->show_venue)."</a>";
		} else {
			$venue = gigpress_sanitize($show->show_venue);
		}

		// See if there's a buy link
		// if so, make magic happen with the clicking and such
		if(!empty($show->show_tix_url)) {
			$buy = "<a href=\"$tix_url\">". _('Link') ."</a>";
		} else {
			$buy = "Nope";
		}

		// Determine where we return to after an edit or delete
		if($scope == "past") { $return = "gp-past"; } else { $return = "gp-upcoming"; }

		// Create our edit and delete links
		$edit = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=edit&amp;show_id='.$show->show_id.'&amp;gpreturn='.$return;
		$edit = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($edit, 'gigpress-action') : $edit;

		$delete = get_bloginfo('wpurl').'/wp-admin/admin.php?page='.$return.'&amp;gpaction=delete&amp;show_id='.$show->show_id;
		$delete = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($delete, 'gigpress-action') : $delete;

		++ $i;
		$style = ($i % 2) ? '' : ' class="alternate"';
		// Print out our rows.
		?>
		<tr<?php echo $style; ?>>
			<td><span class="gigpress-date"><?php echo $date; ?><?php if($show->show_multi == 1) { echo(' - ').$expire; }?></span><br /><span class="gigpress-time"><?php echo $time; ?></span></td>
			<td><?php echo gigpress_sanitize($show->show_locale); ?></td>
			<td><?php echo $venue; ?></td>
			<td><?php echo $address; ?></td>
			<td><?php echo $show->show_country; ?></td>
			<td><?php echo $show->show_ages; ?></td>
			<td class="gp-centre"><?php echo gigpress_sanitize($show->show_price); ?></td>
			<td><?php echo $buy; ?></td>
			<td><a href="<?php echo $edit; ?>" class="edit"><?php _e('Edit'); ?></a></td>
			<td><a href="<?php echo $delete; ?>" class="delete"><?php _e('Delete'); ?></a></td>
		</tr>
		<?php if(!empty($show->show_notes)) { ?>
			<tr<?php echo $style; ?>>
				<td colspan="10" class="notes"><?php echo gigpress_sanitize($show->show_notes); ?></td>
			</tr>
		<?php }
	
	} // end foreach ?>
	
	<tr><td colspan="10">&nbsp;</td></tr>
	<?php
}



// ADMIN: TOURS
// Here be the tours admin page, where we can add, edit and delete
// the label that is applied to specific collections of shows ...
// ==============================================================



function gigpress_tours() {

	global $wpdb;
	global $gigpress;
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "add") {
		gigpress_add_tour();		
	}
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "update") {
		gigpress_update_tour();
	}
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "delete") {
		gigpress_delete_tour();		
	}

	?>

	<div class="wrap gigpress">

	<h2><?php _e("Tours"); ?></h2>
	
	<p><?php _e('A tour is simply a collection of shows that you want to group together.  When displayed on your site, tours are grouped together under their respective headings.'); ?></p>
	
	<?php if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") {
	
		// Check le nonce
		check_admin_referer('gigpress-action');
	
		// Load the previous show info into the edit form, and so forth 
		$tourdata = $wpdb->get_results("
		SELECT * from ". $gigpress['tours_table'] ." WHERE tour_id = ". $_GET['tour_id'] ." LIMIT 1
		");
		if($tourdata) {
			// We got the goods from the DB - proceed with the edit form ...
			
			foreach($tourdata as $tour) {
				$tourname = gigpress_sanitize($tour->tour_name);		
			}
	
		?>
	
		<form method="post" action="<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?page=gp-tours"; ?>">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="update" />
		<input type="hidden" name="tour_id" value="<?php echo $_GET['tour_id']; ?>" />
			
		<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform gp-table">
			<tr>
				<th width="33%" scope="row"><label for="tour_name"><?php _e('Edit your tour name:') ?></label></th>
				<td><input name="tour_name" type="text" size="48" value="<?php echo $tourname; ?>" />
				</td>
			</tr>
			<tr>
				<th width="33%" scope="row">&nbsp;</th>
				<td>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update tour') ?>" /></p></td>
			</tr>
		</table>
		
		</form>
	
		<?php
			
		} else {
		
			// Couldn't grab the show's details from the database
			// for some reason
			echo('<div id="message" class="error fade"><p>');
			_e('Sorry, but we had trouble loading that tour for editing.');
			echo('</div>');
		}
		
	} else { ?>
	
		<form method="post" action="<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?page=gp-tours"; ?>">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="add" />
			
		<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform gp-table">
			<tr>
				<th width="33%" scope="row"><label for="tour_name"><?php _e('Add a new tour:') ?></label></th>
				<td><input name="tour_name" type="text" size="48" />
				</td>
			</tr>
			<tr>
				<th width="33%" scope="row">&nbsp;</th>
				<td>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Add tour') ?>" /></p></td>
			</tr>
		</table>
		
		</form>
	
	<?php } ?>
	
	<h3><?php _e("All tours"); ?></h3>
	
	<p><?php _e("Note that deleting a tour will <strong>NOT</strong> delete the shows associated with that tour."); ?></p>
	
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e("Date added"); ?></th>
				<th scope="col"><?php _e("Tour name"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Shows in tour"); ?></th>
				<th colspan="2" class="gp-centre" scope="col"><?php _e("Actions"); ?></th>
			</tr>
		</thead>
	<?php
		// Get all upcoming tours from the DB
		$tours = $wpdb->get_results("SELECT * from ". $gigpress['tours_table'] ." ORDER BY tour_created DESC");
		
		// Do we have tours?
		if($tours) {
				
			foreach($tours as $tour) {
			
				if($n = $wpdb->get_var("
					SELECT count(*) FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ."
				")) {
					$count = $n;
				} else {
					$count = 0;
				}
				
				// Format the date as per our options page
				$created = mysql2date(get_option('gigpress_date_format'), $tour->tour_created);
				
				// Create our edit and delete links
				$edit = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gp-tours&amp;gpaction=edit&amp;tour_id='.$tour->tour_id;
				$edit = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($edit, 'gigpress-action') : $edit;
				
				$delete = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gp-tours&amp;gpaction=delete&amp;tour_id='.$tour->tour_id;
				$delete = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($delete, 'gigpress-action') : $delete;
				
				++ $i;
				$style = ($i % 2) ? '' : ' class="alternate"';
				// Print out our rows.
				?>
				<tr<?php echo $style; ?>>
					<td><?php echo $created; ?></td>
					<td><?php echo gigpress_sanitize($tour->tour_name); ?></td>
					<td class="gp-centre"><?php echo $count; ?></td>
					<td><a href="<?php echo $edit; ?>" class="edit"><?php _e('Edit'); ?></a></td>
					<td><a href="<?php echo $delete; ?>" class="delete"><?php _e('Delete'); ?></a></td>
				</tr>
				<?php }
		} else {

			// We don't have any tours, so let's say so ?>
			<tr><td colspan="5"><strong><?php _e('No tours in the database!'); ?></strong></td></tr>
	<?php	}	
	?>
	</table>
	<?php gigpress_footer() ?>
	</div>
	
<?php }



// ADMIN: OPTIONS
// Aarrrr, and here be the Options page, which includes options for our widget
// ==========================================================================


function gigpress_options() {
	
	global $gigpress;

	// This gives us the magic fading menu when we update.  Yes - magic.
	require(ABSPATH . 'wp-admin/options-head.php');
	
	?>

	<div class="wrap gigpress">
	
	<h2><?php _e("Options"); ?></h2>
	
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options') ?>
	
	<fieldset class="options" id="gpgeneral">
	
		<legend><?php _e('General options'); ?></legend>
	
	<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform gp-table">
		<tr>
			<th width="33%" scope="row"><?php _e('Name of your band:') ?></th>
			<td>
				<input type="text" name="gigpress_band" value="<?php echo gigpress_sanitize(get_option('gigpress_band')); ?>" /><br />

				<small class="gp-instructions"><?php _e('This is used in your RSS feed and hCalendar data.') ?></small>
			</td>
		</tr>
		<tr>
			<th width="33%" scope="row"><?php _e('User level required to use GigPress:') ?></th>
			<td>
				<select name="gigpress_user_level">
					<option value="8"<?php if (get_option('gigpress_user_level') == '8') echo (' selected="selected"'); ?>>Administrator</option>
					<option value="3"<?php if (get_option('gigpress_user_level') == '3') echo (' selected="selected"'); ?>>Editor</option>
					<option value="2"<?php if (get_option('gigpress_user_level') == '2') echo (' selected="selected"'); ?>>Author</option>
					<option value="1"<?php if (get_option('gigpress_user_level') == '1') echo (' selected="selected"'); ?>>Contributor</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
		</tr>
	</table>
	
	</fieldset>
	
	<fieldset class="options" id="gpformatting">
		
		<legend><?php _e('Display &amp; Formatting')?></legend>
		
		<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform gp-table">
		
		<tr>
			<th width="33%" scope="row"><?php _e('Date format:') ?></th>
			<td>
				<input type="text" name="gigpress_date_format" value="<?php echo get_option('gigpress_date_format'); ?>" />
			</td>
		</tr>	
		<tr>
			<th width="33%" scope="row"><?php _e('Time format:') ?></th>
			<td>
				<input type="text" name="gigpress_time_format" value="<?php echo get_option('gigpress_time_format'); ?>" /><br />
				<?php _e('Output:') ?> <strong><?php echo mysql2date(get_option('gigpress_date_format'), current_time('mysql')); ?></strong> - <strong><?php echo gmdate(get_option('gigpress_time_format'), current_time('timestamp')); ?></strong><br />
			
				<small class="gp-instructions"><a href="http://codex.wordpress.org/Formatting_Date_and_Time"><?php _e('Here&rsquo;s some documentation on date and time formatting') ?></a>. <?php _e('Click &ldquo;Update options&rdquo; to update the sample output.') ?></small>
			
			</td>
		</tr>
		
		<tr>
			<th width="33%" scope="row"><?php _e('Full URL to your &lsquo;Upcoming Shows&rsquo; page:') ?></th>
			<td>
				<input type="text" size="48" name="gigpress_shows_page" value="<?php echo gigpress_sanitize(get_option('gigpress_shows_page')); ?>" /><br />

				<p><label><input type="checkbox" name="gigpress_sidebar_link" value="1" <?php if(get_option('gigpress_sidebar_link') == 1) echo('checked="checked"'); ?> /> <?php _e('Show a link to this page below my sidebar listing.'); ?></label></p>
			</td>
		</tr>	

		<tr>
			<th width="33%" scope="row"><?php _e('No upcoming shows message:') ?></th>
			<td>
				<input type="text" name="gigpress_noupcoming" size="48" value="<?php echo gigpress_sanitize(get_option('gigpress_noupcoming')); ?>" />
			</td>
		</tr>	
		<tr>
			<th width="33%" scope="row"><?php _e('No past shows message:') ?></th>
			<td>
				<input type="text" name="gigpress_nopast" size="48" value="<?php echo gigpress_sanitize(get_option('gigpress_nopast')); ?>" />
			</td>
		</tr>	

		<tr>
			<th width="33%" scope="row"><?php _e('RSS Feed') ?></th>
			<td>
				<p><?php _e('Please include a link to my upcoming shows RSS feed'); ?> ...</p>
				<p><label><input type="checkbox" name="gigpress_rss_upcoming" value="1" <?php if(get_option('gigpress_rss_upcoming') == 1) echo('checked="checked"'); ?> /> <?php _e('below my upcoming shows table'); ?></label> ... 
				<label><input type="checkbox" name="gigpress_rss_list" value="1" <?php if(get_option('gigpress_rss_list') == 1) echo('checked="checked"'); ?> /> <?php _e('below my sidebar listing'); ?></label> ... 
				<label><input type="checkbox" name="gigpress_rss_head" value="1" <?php if(get_option('gigpress_rss_head') == 1) echo('checked="checked"'); ?> /> <?php _e('in the <code>head</code> portion of my page'); ?></label></p>
				<p class="gp-instructions"><?php _e('Your RSS feed for upcoming shows is '); ?><code><?php echo $gigpress['rss']; ?></code>.</p>
			</td>
		</tr>
		
		<tr>
			<th width="33%" scope="row"><?php _e('Display &lsquo;Country&rsquo; column?') ?></th>
			<td>
				<input type="checkbox" name="gigpress_display_country" value="1" <?php if(get_option('gigpress_display_country') == 1) echo('checked="checked"'); ?> />
			</td>
		</tr>
		
		<tr>
			<th width="33%" scope="row"><?php _e('Heading level of tour names:') ?></th>
			<td>
				<select name="gigpress_tour_heading">
					<option value="h1"<?php if (get_option('gigpress_tour_heading') == 'h1') echo (' selected="selected"'); ?>>h1</option>
					<option value="h2"<?php if (get_option('gigpress_tour_heading') == 'h2') echo (' selected="selected"'); ?>>h2</option>
					<option value="h3"<?php if (get_option('gigpress_tour_heading') == 'h3') echo (' selected="selected"'); ?>>h3</option>
					<option value="h4"<?php if (get_option('gigpress_tour_heading') == 'h4') echo (' selected="selected"'); ?>>h4</option>
					<option value="h5"<?php if (get_option('gigpress_tour_heading') == 'h5') echo (' selected="selected"'); ?>>h5</option>
				</select>
				<br />
				<small class="gp-instructions"><?php _e('Depending on the semantic structure of your page template, you may want the headings which display the name of your tour(s) to be a different level.'); ?> (<a href="http://en.wikipedia.org/wiki/HTML_element#Headings"><?php _e('More on HTML headings.'); ?></a>)</small>
			</td>
		</tr>
		<tr>
			<th width="33%" scope="row"><abbr title="<?php _e('Cascading Style Sheets'); ?>"><?php _e('CSS:') ?></abbr></th>
			<td>
				<p><label>
					<input name="gigpress_css" type="radio" value="gigpress" class="tog"<?php if(get_option('gigpress_css') == "gigpress") echo ' checked="checked"'; ?> />
					<?php _e("Use GigPress' default style sheet to display your tour dates"); ?>
				</label></p>
				<p><label>
					<input name="gigpress_css" type="radio" value="user" class="tog"<?php if(get_option('gigpress_css') == "user") echo ' checked="checked"'; ?> />
					<?php _e("Use your own custom styles to display your tour dates"); ?>
				</label><br />
				<small class="gp-instructions"><?php _e('For more information on using your own style sheet, '); ?><a href="http://gigpress.com/docs#css"><?php _e('please refer to the documentation'); ?></a>.</small>
				</p>
			</td>
		</tr>
		
		<tr>
			<th>&nbsp;</th>
			<td><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
		</tr>
		</table>
	
	</fieldset>
	
	<fieldset class="options" id="gpwidget">
	
		<legend><?php _e('Widget options'); ?></legend>
		
	<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform gp-table">
		<tr>
			<th width="33%" scope="row"><?php _e('List heading:') ?></th>
			<td>
				<input type="text" name="gigpress_widget_heading" value="<?php echo gigpress_sanitize(get_option('gigpress_widget_heading')); ?>" />
			</td>
		</tr>
		<tr>
			<th width="33%" scope="row"><?php _e('Number of shows to list:') ?></th>
			<td>
				<input type="text" name="gigpress_widget_number" value="<?php echo get_option('gigpress_widget_number'); ?>" /><br />
				<small class="gp-instructions"><?php _e('Any number from 1 to 99'); ?>.</small>
			</td>
		</tr>
		<tr>
			<th width="33%" scope="row"><?php _e('Segment sidebar list into tours?') ?></th>
			<td>
				<input type="checkbox" name="gigpress_widget_segment" value="1" <?php if(get_option('gigpress_widget_segment') == 1) echo('checked="checked"'); ?> />
			</td>
		</tr>	
		<tr>
			<th>&nbsp;</th>
			<td><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
		</tr>
		
		</table>
		
	</fieldset>

		<input type="hidden" name="action" value="update" />
	
		<input type="hidden" name="page_options" value="gigpress_band,gigpress_css,gigpress_tour_heading,gigpress_date_format,gigpress_time_format,gigpress_noupcoming,gigpress_nopast,gigpress_rss_upcoming,gigpress_rss_list,gigpress_rss_head,gigpress_widget_number,gigpress_widget_heading,gigpress_widget_segment,gigpress_shows_page,gigpress_sidebar_link,gigpress_user_level,gigpress_display_country" />
		
	</form>

	<?php gigpress_footer() ?>
	</div>
	
	<?php
}


// HANDLER: ADD A SHOW
// ===================


function gigpress_add_show() {

	global $wpdb;
	global $gigpress;

	// Check the nonce
	check_admin_referer('gigpress-action');
	
	// If our required fields are empty, stop the presses (no pun intended)
	// and say why
	if(empty($_POST['show_locale'])) $errors['locale'] = "You must enter a city.";
	if(empty($_POST['show_venue'])) $errors['venue'] = "You must enter a venue (perhaps it's TBA?).";
	
	if($errors) {
		echo('<div id="message" class="error fade">');
		if($errors['locale']) _e("<p>". $errors['locale'] ."<p>");
		if($errors['venue']) _e("<p>". $errors['venue'] ."<p>");
		echo("</div>");
		
	} else {
	
		// Looks like we're all here, so let's add to the DB
		$date = $_POST['yy'] . '-' . $_POST['mm'] . '-' . $_POST['dd'];
		$time = $_POST['hh'] . ':' . $_POST['min'] . ':00';
		if($_POST['multi'] != 1) {
			$expire = $_POST['yy'] . '-' . $_POST['mm'] . '-' . $_POST['dd'];
		} else {
			$expire = $_POST['exp_yy'] . '-' . $_POST['exp_mm'] . '-' . $_POST['exp_dd'];
		}
		$addshow = $wpdb->query("
			INSERT INTO ". $gigpress['gigs_table'] ." (show_date, show_time, show_expire, show_multi, show_address, show_locale, show_country, show_price, show_tix_url, show_venue, show_venue_url, show_ages, show_notes, show_tour_id)
			VALUES ('". $date ."', '". $time ."', '". $expire ."', '". $_POST['multi'] ."','". $_POST['show_address'] ."', '". $_POST['show_locale'] ."', '". $_POST['show_country'] ."', '". $_POST['show_price'] ."', '". $_POST['show_tix_url'] ."', '". $_POST['show_venue'] ."', '". $_POST['show_venue_url'] ."', '". $_POST['show_ages'] ."', '". $_POST['show_notes'] ."', '". $_POST['show_tour_id'] ."')
		");
		
		// Was the query successful?
		if($addshow != FALSE) {
			
			//YES! ?>
			<div id="message" class="updated fade"><p><?php _e('Your show  in '. gigpress_sanitize($_POST['show_locale']) .' on '.  mysql2date(get_option('gigpress_date_format'), $date) .' was successfully added.'); ?></p></div>
			
	<?php } else {
			// NO? ?>
			<div id="message" class="error fade"><p><?php _e('Something ain&rsquo;t right - try again?'); ?></p></div>			
	<?php }
	}
}


// HANDLER: EDIT A SHOW
// ====================


function gigpress_update_show() {

	// We use this to update a show that's already in the DB
	global $wpdb;
	global $gigpress;
	// Check the nonce
	check_admin_referer('gigpress-action');
			
	// If our required fields are empty, stop the presses (no pun intended)
	// (not funny the 2nd time) (or the first time) and say why
	if(empty($_POST['show_locale'])) $errors['locale'] = "You must enter a city.";
	if(empty($_POST['show_venue'])) $errors['venue'] = "You must enter a venue (perhaps it's TBA?).";
	
	if($errors) {
		echo('<div id="message" class="error fade">');
		if($errors['locale']) _e("<p>". $errors['locale'] ."<p>");
		if($errors['venue']) _e("<p>". $errors['venue'] ."<p>");
		echo("</div>");
		
	} else {
	
		// Looks like we're all here, so let's update the DB
		$date = $_POST['yy'] . '-' . $_POST['mm'] . '-' . $_POST['dd'];
		$time = $_POST['hh'] . ':' . $_POST['min'] . ':00';
		if($_POST['multi'] != 1) {
			$expire = $_POST['yy'] . '-' . $_POST['mm'] . '-' . $_POST['dd'];
		} else {
			$expire = $_POST['exp_yy'] . '-' . $_POST['exp_mm'] . '-' . $_POST['exp_dd'];
		}
		
		$updateshow = $wpdb->query("
			UPDATE ". $gigpress['gigs_table'] ." SET show_date = '". $date ."',show_time = '". $time ."',show_expire = '". $expire ."',show_multi = '". $_POST['multi'] ."',show_address = '". $_POST['show_address'] ."',show_locale = '". $_POST['show_locale'] ."',show_country = '". $_POST['show_country'] ."',show_price = '". $_POST['show_price'] ."',show_tix_url = '". $_POST['show_tix_url'] ."',show_venue = '". $_POST['show_venue'] ."',show_venue_url = '". $_POST['show_venue_url'] ."',show_ages = '". $_POST['show_ages'] ."',show_notes = '". $_POST['show_notes'] ."',show_tour_id = '". $_POST['show_tour_id'] ."' WHERE show_id = ". $_POST['show_id']."
		");
		
		// Was the query successful?
		if($updateshow != FALSE) {
			// YES! ?>
				<div id="message" class="updated fade"><p><?php _e('Your show  in '. gigpress_sanitize($_POST['show_locale']) .' on '. mysql2date(get_option('gigpress_date_format'), $date) .' was successfully updated.'); ?></p></div>
		<?php	
		} else {
			// NO? ?>
				<div id="message" class="error fade"><p><?php _e('Something ain&rsquo;t right - try again?'); ?></p></div>
	<?php }
	}
}


// HANDLER: DELETE A SHOW
// ======================


function gigpress_delete_show() {

	// We use this to delete shows
	global $wpdb;
	global $gigpress;	
	// Check the nonce
	check_admin_referer('gigpress-action');	
	$trashshow = $wpdb->query("
		DELETE FROM ". $gigpress['gigs_table'] ." WHERE show_id = ". $_GET['show_id'] ." LIMIT 1
		");
	if($trashshow != FALSE) { ?>
			<div id="message" class="updated fade"><p><?php _e('Show successfully deleted from the database.') ?></p></div>
	<?php } else { ?>
			<div id="message" class="error fade"><p><?php _e('We ran into some trouble deleting the show. Sorry.') ?></p></div>
	<?php }
}


// HANDLER: ADD A TOUR
// ===================


function gigpress_add_tour() {

	global $wpdb;
	global $gigpress;
	// Check the nonce
	check_admin_referer('gigpress-action');
	
	// If our required fields are empty, stop the presses (no pun intended) 
	// (good god stop it) and say why
	if(empty($_POST['tour_name']))  {
		echo('<div id="message" class="error fade"><p>');
		_e('You need to enter a name for the tour. It&rsquo;s the only field for crying out loud!');
		echo('</p></div>');
	} else {
	
		// Looks like we're all here, so let's add to the DB
		$addtour = $wpdb->query("
			INSERT into ". $gigpress['tours_table'] ." (tour_created, tour_name)
			VALUES ('". current_time('mysql') ."', '". $_POST['tour_name'] ."')
		");
		
		// Was the query successful?
		if($addtour != FALSE) {
			
			//YES! ?>
				<div id="message" class="updated fade"><p><?php _e(''. gigpress_sanitize($_POST['tour_name']) .' was successfully added to the database.'); ?></p></div>
			
	<?php } else { // NO? ?>
				<div id="message" class="error fade"><p><?php _e('Something ain&rsquo;t right - try again?'); ?></p></div>
	<?php }
	}
}


// HANDLER: UPDATE A TOUR
// ======================


function gigpress_update_tour() {

	// We use this to update a show that's already in the DB
	global $wpdb;
	global $gigpress;
	// Check the nonce
	check_admin_referer('gigpress-action');
			
	// If our required field is empty, chastize
	if(empty($_POST['tour_name']))  {
		echo('<div id="message" class="error fade"><p>');
		_e('You need to enter a name for the tour. It&rsquo;s the only field for crying out loud!');
		echo('</p></div>');
	} else {
			
		// Looks like we're all here, so let's update the DB
		$updatetour = $wpdb->query("
			UPDATE ". $gigpress['tours_table'] ." SET tour_name = '". $_POST['tour_name'] ."' WHERE tour_id = ". $_POST['tour_id']."
		");
		
		// Was the query successful?
		if($updatetour != FALSE) {
			// YES! ?>
				<div id="message" class="updated fade"><p><?php _e('Tour name successfully changed to: '. gigpress_sanitize($_POST['tour_name'])); ?></p></div>
	<?php } else { // NO? ?>
				<div id="message" class="error fade"><p><?php _e('Something ain&rsquo;t right - try again?'); ?></p></div>
	<?php }
	}
}


// HANDLER: DELETE A TOUR
// ======================


function gigpress_delete_tour() {

	// We use this to delete tours
	global $wpdb;
	global $gigpress;	
	// Check the nonce
	check_admin_referer('gigpress-action');	
	
	// Delete the tour
	$trashtour = $wpdb->query("
		DELETE FROM ". $gigpress['tours_table'] ." WHERE tour_id = '". $_GET['tour_id'] ."' LIMIT 1;
		");
	if($trashtour != FALSE) {
		// Find any shows associated with that tour and remove their foreign key
		$cleanup = $wpdb->query("
		UPDATE ". $gigpress['gigs_table'] ." SET show_tour_id = 0 WHERE show_tour_id = ". $_GET['tour_id'] .";
		");
		echo('<div id="message" class="updated fade"><p>'); _e('Tour successfully deleted from the database.'); echo('</p></div>');
	} else {
		echo('<div id="message" class="error fade"><p>'); _e('We ran into some trouble deleting the tour. Sorry.'); echo('</p></div>');				
	}
}



// ---------------------- AND NOW THE FRONT-END SHIZZLE ------------------//



// UPCOMING SHOWS TABLE FUNCTION
// =============================

function gigpress_upcoming_content() {
	
	global $wpdb;
	global $gigpress;
	global $tourshows;
	
	$heading = gigpress_sanitize(get_option('gigpress_tour_heading'));
	
	// See if we're displaying the country, and build the table accordingly
	if(get_option('gigpress_display_country') == 1) { $cols = 4; } else { $cols = 3; }
	?>
	<table class="gigpress-table hcalendar" cellspacing="0">
		<thead>
			<tr class="gigpress-header">
				<th scope="col" class="gigpress-date"><?php _e('Date'); ?></th>
				<th scope="col" class="gigpress-city"><?php _e('City'); ?></th>
				<th scope="col" class="gigpress-venue"><?php _e('Venue'); ?></th>
				<?php if($cols == 4) { ?>
				<th scope="col" class="gigpress-country"><?php _e('Country'); ?></th>
				<?php } ?>
		</tr>
		</thead>
		<?php
		// Retrieve all tours from the DB
		$tours = $wpdb->get_results("
			SELECT * FROM ". $gigpress['tours_table'] ." ORDER BY tour_created ASC
		");
		
		// If there are indeed tours to be found
		if($tours != FALSE) {
		
			foreach($tours as $tour) {
			
				// See if each tour actually has any shows assigned to it
				$shows = $wpdb->get_results("
					SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND show_expire >= '". current_time('mysql') ."' ORDER BY show_date ASC
				");
				
				// If there are shows for the tour, display that shiznatt
				if($shows != FALSE) { ?>
				
				<tr>
					<th colspan="<?php echo $cols; ?>" class="gigpress-heading" id="tour-<?php echo $tour->tour_id; ?>">
						<?php echo "<".$heading.">".gigpress_sanitize($tour->tour_name)."</".$heading.">"; ?>
					</th>
				</tr>
				<?php
				gigpress_shows_table($shows, "tour", "upcoming");
					
			} // If there aren't any shows for the tour, do nothing
			
		} // end foreach tour
			
	} // And if there aren't any tours at all, do nothing
			
	// Get all upcoming dates from the DB that are NOT part of a tour
		
	$upcoming = $wpdb->get_results("
		SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire >= '". current_time('mysql') ."' AND show_tour_id = 0 ORDER BY show_date ASC
	");
		
	// Do we have dates?
	if($upcoming != FALSE) {
		
		// Only show a heading for these little guys if there were tours above them ...
		if($tourshows == TRUE) { ?>
		<tr>
			<th colspan="<?php echo $cols; ?>" class="gigpress-heading">
				<?php echo "<".$heading.">".__("Individual shows")."</".$heading.">"; ?>
			</th>
		</tr>
		<?php
		}
	
		gigpress_shows_table($upcoming, "individual", "upcoming");
			
	} // end if upcoming shows
				
	if($tourshows != TRUE && $upcoming == FALSE) {
	// We don't have shows of any kind to show you ?>
		<tr><td colspan="<?php echo $cols; ?>" class="gigpress-row"><?php echo gigpress_sanitize(get_option('gigpress_noupcoming')); ?></td></tr>
	<?php }
	if(get_option('gigpress_rss_upcoming') == 1) { ?>
	<tr>
		<td class="gigpress-rss gigpress-row" colspan="<?php echo $cols; ?>"><a href="<?php echo $gigpress['rss']; ?>" rel="alternate" type="application/rss+xml" title="<?php _e('Upcoming shows RSS feed'); ?>">RSS</a></td>
	</tr>
	<?php } ?>
	</table>

<?php }




// UPCOMING SHOWS TABLE FUNCTION WRAPPER
// =====================================

function gigpress_upcoming($content) {

	if(!preg_match('|[gigpress_upcoming]|', $content)) {
		return $content;
	}
	
	ob_start();
		gigpress_upcoming_content();
		$output = ob_get_contents();
	ob_end_clean();

    return str_replace('[gigpress_upcoming]', $output, $content);
				
}



// PAST SHOWS TABLE FUNCTION
// =========================

function gigpress_archive_content() {
	
	global $wpdb;
	global $gigpress;
	global $tourshows;
	
	$heading = gigpress_sanitize(get_option('gigpress_tour_heading'));
	
	// See if we're displaying the country, and build the table accordingly
	if(get_option('gigpress_display_country') == 1) { $cols = 4; } else { $cols = 3; }
	?>
	<table class="gigpress-table hcalendar" cellspacing="0">
		<thead>
			<tr class="gigpress-header">
				<th scope="col" class="gigpress-date"><?php _e('Date'); ?></th>
				<th scope="col" class="gigpress-city"><?php _e('City'); ?></th>
				<th scope="col" class="gigpress-venue"><?php _e('Venue'); ?></th>
				<?php if($cols == 4) { ?>
				<th scope="col" class="gigpress-country"><?php _e('Country'); ?></th>
				<?php } ?>
		</tr>
		</thead>
		<?php
		// Retrieve all tours from the DB
		$tours = $wpdb->get_results("
			SELECT * FROM ". $gigpress['tours_table'] ." ORDER BY tour_created ASC
		");
		
		// If there are indeed tours to be found
		if($tours != FALSE) {
		
			foreach($tours as $tour) {
			
				// See if each tour actually has any shows assigned to it
				$shows = $wpdb->get_results("
					SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND show_expire < '". current_time('mysql') ."' ORDER BY show_date DeSC
				");
				
				// If there are shows for the tour, display that shiznatt
				if($shows != FALSE) { ?>
				
				<tr>
					<th colspan="<?php echo $cols; ?>" class="gigpress-heading" id="tour-<?php echo $tour->tour_id; ?>">
						<?php echo "<".$heading.">".gigpress_sanitize($tour->tour_name)."</".$heading.">"; ?>
					</th>
				</tr>
				<?php
				gigpress_shows_table($shows, "tour", "past");
					
			} // If there aren't any shows for the tour, do nothing
			
		} // end foreach tour
			
	} // And if there aren't any tours at all, do nothing
			
	// Get all past dates from the DB that are NOT part of a tour
		
	$oast = $wpdb->get_results("
		SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire < '". current_time('mysql') ."' AND show_tour_id = 0 ORDER BY show_date DESC
	");
		
	// Do we have dates?
	if($past != FALSE) {
		
		// Only show a heading for these little guys if there were tours above them ...
		if($tourshows == TRUE) { ?>
		<tr>
			<th colspan="<?php echo $cols; ?>" class="gigpress-heading">
				<?php echo "<".$heading.">".__("Individual shows")."</".$heading.">"; ?>
			</th>
		</tr>
		<?php
		}
	
		gigpress_shows_table($past, "individual", "past");
			
	} // end if upcoming shows
				
	if($tourshows != TRUE && $upcoming == FALSE) {
	// We don't have shows of any kind to show you ?>
		<tr><td colspan="<?php echo $cols; ?>" class="gigpress-row"><?php echo gigpress_sanitize(get_option('gigpress_nopast')); ?></td></tr>
	<?php } ?>
	</table>

<?php }



// PAST SHOWS TABLE FUNCTION WRAPPER
// =================================


function gigpress_archive($content) {

	if(!preg_match('|[gigpress_archive]|', $content)) {
		return $content;
	}

	ob_start();
		gigpress_archive_content();
		$output = ob_get_contents();
	ob_end_clean();

    return str_replace('[gigpress_archive]', $output, $content);
}



// OUTPUT: SHOWS TABLE
// ===================


function gigpress_shows_table($shows, $type, $scope) {

	foreach($shows as $show) {
	
		// Register that we have a tour with shows
		if($type == "tour") {
			global $tourshows;
			$tourshows = TRUE;
		}
	
		// Format the date as per our options page
		$date = mysql2date(get_option('gigpress_date_format'), $show->show_date);
		$expire = mysql2date(get_option('gigpress_date_format'), $show->show_expire);
		$time = gigpress_timefy($show->show_time);
		$hdate = $show->show_date." ".$show->show_time;
		$hexpire = $show->show_expire." ".$show->show_time;
	
	
		// Make the address Googley
		$address = gigpress_mapit(gigpress_sanitize($show->show_address), gigpress_sanitize($show->show_locale), $show->show_country);
	
		// Make sure our links are protocol'd
		$venue_url = gigpress_check_url(gigpress_sanitize($show->show_venue_url));
		$tix_url = gigpress_check_url(gigpress_sanitize($show->show_tix_url));
	
		// Link up the venue name if it has a url associated with it
		if(!empty($show->show_venue_url)) {
			$venue = "<a href=\"$venue_url\">".gigpress_sanitize($show->show_venue)."</a>";
		} else {
			$venue = gigpress_sanitize($show->show_venue);
		}
	
		// See if there's a buy link - if so, make magic happen with the clicking and such
		if(!empty($show->show_tix_url)) {
			$buy = "<a href=\"$tix_url\" class=\"buy-tickets url\">". __('Buy tickets.') ."</a> ";
		} else {
			$buy = "";
		}
		
		// See if we're displaying the country, and build the table accordingly
		if(get_option('gigpress_display_country') == 1) { $cols = 3; } else { $cols = 2; }
	
		// This is for alternating row styles
		++ $i;
		$style = ($i % 2) ? '' : ' gigpress-alt';

		// Print out our rows ?>
		<tbody class="vevent">
		<tr class="gigpress-row<?php echo $style; ?>">
			<td class="gigpress-date"><abbr class="dtstart" title="<?php echo $hdate; ?>"><?php echo $date; ?></abbr><?php if($show->show_multi == 1) { echo(' - <abbr class="dtend" title="'.$hexpire.'">'.$expire.'</abbr>'); }?></td>
			<td class="gigpress-city summary"><span class="hide"><?php echo gigpress_sanitize(get_option('gigpress_band')); ?> in </span><?php echo gigpress_sanitize($show->show_locale); ?></td>
			<td class="gigpress-venue location"><?php echo $venue; ?></td>
			<?php if($cols == 3) { ?>
				<td class="gigpress-country"><?php echo $show->show_country; ?></td>
			<?php } ?>
		</tr>
		<tr class="gigpress-info<?php echo $style; ?>">
			<td>&nbsp;</td>
			<td colspan="<?php echo $cols ?>" class="description">
			<span class="gigpress-info-label"><?php _e('Time:'); ?></span> <?php echo $time.". " ?>
			<?php if($show->show_price) { ?><span class="gigpress-info-label"><?php _e('Admission:'); ?></span> <?php echo gigpress_sanitize($show->show_price) .'. '; } ?>
			<?php if($show->show_ages != "Not sure") { ?> <span class="gigpress-info-label"><?php _e('Age restrictions:'); ?></span> <?php echo $show->show_ages .'. '; } ?>
			<?php if($scope == "upcoming") { echo $buy; } ?>
			<?php if($address) { ?> <span class="gigpress-info-label"><?php _e('Address:'); ?></span> <?php echo $address .'. '; } ?>
			<?php if($show->show_notes) { echo gigpress_sanitize($show->show_notes); } ?>
			</td>
		</tr>
		</tbody> <?php
	} // end foreach show
}


// OUTPUT: SIDEBAR SHOW LISTER
// ===========================================


function gigpress_sidebar_content($shows, $type) {
	
	$num = count($shows);
	$item = 1;
	
	// Register that we have a tour with shows and give us a heading
	if($type == "tour") {
		global $tourshows;
		$tourshows = TRUE;
	}
	
	foreach ($shows as $show) {
		
		$date = mysql2date(get_option('gigpress_date_format'), $show->show_date);
		$expire = mysql2date(get_option('gigpress_date_format'), $show->show_expire);
		$time = gigpress_timefy($show->show_time);
		$hdate = $show->show_date." ".$show->show_time;
		$hexpire = $show->show_expire." ".$show->show_time;

		// Make sure our links are protocol'd
		$venue_url = gigpress_check_url(gigpress_sanitize($show->show_venue_url));

		// Link up the venue name if it has a url associated with it
		if(!empty($show->show_venue_url)) {
			$venue = "<a href=\"$venue_url\">".gigpress_sanitize($show->show_venue)."</a>";
		} else {
			$venue = gigpress_sanitize($show->show_venue);
		}

		echo('<li class="vevent');

		if($item == 1) { echo(' gigpress-list-first'); }
		if($item == $num) { echo(' gigpress-list-last'); }

		echo("\"><span class=\"gigpress-date\"><abbr class=\"dtstart\" title=\"$hdate\">$date</abbr>");
		if($show->show_multi == 1) { echo(' - <abbr class="dtend" title="'.$hexpire.'">'.$expire.'</abbr>'); };
		echo("</span><br /><span class=\"summary\"><span class=\"hide\">". gigpress_sanitize(get_option('gigpress_band')) ." in </span>".gigpress_sanitize($show->show_locale)."</span> at <span class=\"location\">$venue</span></li>\n\t\t");
		$item++;
	} // end foreach show
}



// UPCOMING SHOWS SIDEBAR FUNCTION
// ===============================


function gigpress_sidebar($number = 3,$segment = 1) {
	
	global $wpdb;
	global $gigpress;
	global $tourshows;
	
	echo('<ul class="gigpress-listing">');
	
	if($segment == 1) {
		
		$tours = $wpdb->get_results("
			SELECT * FROM ". $gigpress['tours_table'] ." ORDER BY tour_created ASC
		");
		
		// If there are indeed tours to be found
		if($tours != FALSE) {
		
			foreach($tours as $tour) {
			
				// See if each tour actually has any shows assigned to it
				$shows = $wpdb->get_results("
					SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND show_expire >= '". current_time('mysql') ."' ORDER BY show_date ASC LIMIT ".$number."
				");
				
				// If there are shows for the tour, display that shiznatt
				if($shows != FALSE) {
					
					echo('<li><span class="gigpress-list-heading">'.$tour->tour_name.'</span>
							<ul>');
					gigpress_sidebar_content($shows, "tour");
					
				echo('</ul>
				</li>');
				} // end if shows
			} // end foreach tour
		} // if there are no tours, carry on 
		
		// Now we list any shows that are not part of a tour
	
		$list = $wpdb->get_results("
			SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire >= '". current_time('mysql') ."' AND show_tour_id = 0 ORDER BY show_date ASC LIMIT ". $number ."
		");
	
		if($list != FALSE) {

			if($tourshows == TRUE) {
			echo('<li><span class="gigpress-list-heading">'.__('Individual shows').'</span>
				<ul>');
			}
		
			gigpress_sidebar_content($list, "individual");
		
			if($tourshows == TRUE) {
			echo('</ul>
			</li>');
			}
		} // end if individual shows

		if($tourshows != TRUE && $list == FALSE) {
			echo('<li>'.gigpress_sanitize(get_option('gigpress_noupcoming')).'</li>');
		}
				
	} else { // if we're not segmenting by tour

		$list = $wpdb->get_results("
			SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire >= '". current_time('mysql') ."' ORDER BY show_date ASC LIMIT ". $number ."
		");
		
		if($list != FALSE) {
			
			gigpress_sidebar_content($list, "individual");
			
		} else {
			echo('<li>'. gigpress_sanitize(get_option('gigpress_noupcoming')) .'</li>');
		}
	}
	if(get_option('gigpress_sidebar_link') == 1) {
	echo('<li class="gigpress-list-more"><a href="'.gigpress_check_url(get_option('gigpress_shows_page')).'" title="'.__('View all shows').'">'.__('View all shows').'</a></li>');
	}
	if(get_option('gigpress_rss_list') == 1) {
	echo('<li class="gigpress-list-rss"><a href="'.$gigpress['rss'].'" rel="alternate" type="application/rss+xml" title="'.__('Upcoming shows RSS feed').'">RSS</a></li>');
	}
	echo("\n\t\t</ul>");
	echo('<!-- Generated by GigPress '.$gigpress['version'].'. -->');
}



// OUR SIDEBAR WIDGET!
// ===================



function gigpress_widget_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	function gigpress_widget($args) {
		
	// $args is an array of strings that help widgets to conform to
	// the active theme: before_widget, before_title, after_widget,
	// and after_title are the array keys. Default tags: li and h2.
	extract($args);

	// Each widget can store its own options. We keep strings here.
	$title = gigpress_sanitize(get_option('gigpress_widget_heading'));
	$number = get_option('gigpress_widget_number');
	$segment = get_option('gigpress_widget_segment');

	echo $before_widget . $before_title . $title . $after_title;
	
	// Call our sidebar function
	gigpress_sidebar($number, $segment);
	
	echo $after_widget;
	}
	
	function gigpress_widget_control() {
	
		echo('<p>');
		_e('GigPress widget options can be found on the');
		echo(' <a href="'. get_bloginfo('wpurl') .'/wp-admin/admin.php?page=gp-options#gpwidget">');
		_e('GigPress options page');
		echo('</a>.</p>');
	
	}
	
	register_sidebar_widget(array('GigPress', 'widgets'), 'gigpress_widget');	
	register_widget_control(array('GigPress', 'widgets'), 'gigpress_widget_control', 300, 100);
	
}


// FLUSH THE TOIL ... REWRITE RULES
// ================================


function gigpress_flush_rules(){
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}


// ADD THE FEED TYPE
// =================

	
function add_gigpress_feed() {
	add_feed('gigpress','gigpress_feed');
}


// BUILD THE RSS FEED
// ==================


function gigpress_feed() {
	
	global $wpdb;
	global $gigpress;

	header("Content-type: text/xml; charset=".get_bloginfo('charset'));
	echo('<?xml version="1.0" encoding="'.get_bloginfo('charset').'"?>
	<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
			<title>'.gigpress_sanitize(get_option('gigpress_band')).'</title>
			<description>Tour dates from '.gigpress_sanitize(get_option('gigpress_band')).'</description>
			<link>');
			if(get_option('gigpress_shows_page') != "") { echo(gigpress_check_url(get_option('gigpress_shows_page'))); } else { bloginfo('home'); }
			echo('</link>');

	$shows = $wpdb->get_results("
		SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire >= '". current_time('mysql') ."' ORDER BY show_date ASC
	");

	if($shows != FALSE) {
		foreach ($shows as $show) {

			$date = mysql2date(get_option('gigpress_date_format'), $show->show_date);
			$expire = mysql2date(get_option('gigpress_date_format'), $show->show_expire);
			$rssdate = mysql2date('D, d M Y', $show->show_date). " ". $show->show_time." GMT";
			// Make the address Googley
			$address = gigpress_mapit(stripslashes($show->show_address), stripslashes($show->show_locale), $show->show_country);

			// Make sure our links are protocol'd
			$venue_url = gigpress_check_url(stripslashes($show->show_venue_url));
			$tix_url = gigpress_check_url(stripslashes($show->show_tix_url));

			// Link up the venue name if it has a url associated with it
			if(!empty($show->show_venue_url)) {
				$venue = "<a href=\"$venue_url\">".stripslashes($show->show_venue)."</a>";
			} else {
				$venue = stripslashes($show->show_venue);
			}
			
			

			// Are we part of a tour?
			$tour = $wpdb->get_var("SELECT tour_name from ".$gigpress['tours_table']." WHERE tour_id = ".$show->show_tour_id." LIMIT 1");

			// See if there's a buy link - if so, make magic happen with the clicking and such
			if(!empty($show->show_tix_url)) {
				$buy = "<a href=\"$tix_url\" class=\"buy-tickets url\">". __('Buy tickets.') ."</a> ";
			} else {
				$buy = "";
			}

			echo('<item>
				<title><![CDATA['.stripslashes(get_option('gigpress_band')).' in '.stripslashes($show->show_locale).' on '.$date.']]></title>
				<description><![CDATA[');
				if($show->show_multi == 1) { echo(__('Ends: ').$expire.'. '); }
				if($show->show_price) { echo(__('Admission:').' '.stripslashes($show->show_price).'. '); }
				if($show->show_ages != "Not sure") { echo(__('Age restrictions:').' '.$show->show_ages.'. '); }
				echo $buy;
				echo('Venue: '.$venue.'. '); if($address) { echo(__('Address:').' '.$address.'. '); }
				if($show->show_notes) { echo stripslashes($show->show_notes); }
				if($tour) { echo(' Part of '.stripslashes($tour).'.');}
				echo(']]></description>
				<link>');
				if(get_option('gigpress_shows_page') != "") { echo(gigpress_check_url(get_option('gigpress_shows_page'))); } else { bloginfo('home'); }
				echo('</link>
				<guid isPermaLink="false">#show-'.$show->show_id.'</guid>
				<pubDate>'.$rssdate.'</pubDate>
			</item>
			');
		}
	}

	echo('</channel>
	</rss>');
}


// MISCELLANEOUS FUNCTIONS
// =======================

function gigpress_load_jquery()	{
	wp_enqueue_script('jquery');
}


function gigpress_admin_head() {
	
	// If we're before WP 2.2, we need to include jQuery
	if($GLOBALS['wp_version'] < "2.2") {
		echo('<script type="text/javascript" src="'. get_bloginfo('wpurl') .'/wp-content/plugins/gigpress/scripts/jquery-1.2.2.min.js"></script>');
	}
	
	// Helps some formatting on our admin pages ...
	echo('<link rel="stylesheet" type="text/css" href="'. get_bloginfo('wpurl') .'/wp-content/plugins/gigpress/gigpress-admin.css" />
	<script type="text/javascript" src="'. get_bloginfo('wpurl') .'/wp-content/plugins/gigpress/scripts/gigpress.js"></script>');
}

function gigpress_footer() {
	global $gigpress;
	echo('<p class="gigpress-footer"><a href="http://gigpress.com">You&rsquo;re using GigPress '.$gigpress['version'].'</a></p>');
}

function gigpress_styles() {
	echo('<link rel="stylesheet" type="text/css" href="'. get_bloginfo('wpurl') .'/wp-content/plugins/gigpress/gigpress.css" />');
}

function gigpress_head_feed() {
	global $gigpress;
	echo('<link href="'.$gigpress['rss'].'" rel="alternate" type="application/rss+xml" title="'.__('Upcoming shows RSS feed').'" />');
}

// This makes sure urls start with a protocol
function gigpress_check_url($url) {
	$url = trim($url);
	$http = substr($url, 0, 7);
	$https = substr($url, 0, 8);
	if($http != "http://" && $https != "https://") {
		$url = "http://". $url;
	}
	return $url;
}

// Format our show time as we like
function gigpress_timefy($time) {
	$timeparts = explode(':', $time);
	$fulltime = mktime($timeparts[0], $timeparts[1]);
	$formattedtime = date(get_option('gigpress_time_format'), $fulltime);
	return $formattedtime;
}

// This makes our street address linked up to Google Maps
function gigpress_mapit($address, $locale, $country) {
	if($address) {
		$url = '<a href="http://maps.google.com/maps?&amp;q='. urlencode($address). ','. urlencode($locale) .','. urlencode($country) .'" title="Find this address using Google Maps" class="gigpress-address">'.$address.'</a>';
		return $url;
	}
}

// Make sure our DB output is kosher
function gigpress_sanitize($input) {
	$input = stripslashes($input);
	$input = wptexturize($input);
	// $input = htmlspecialchars($input, ENT_QUOTES);
	return $input;
}


// HOOKS, ACTIONS, DESCENT INTO THE ABYSS
// ======================================


register_activation_hook(__FILE__,'gigpress_install');
register_activation_hook(__FILE__,'gigpress_flush_rules');

add_action(init,'add_gigpress_feed');
add_action(init,'gigpress_load_jquery');

add_action('admin_head', 'gigpress_admin_head');
add_action('admin_menu', 'gigpress_admin');
add_action('widgets_init', 'gigpress_widget_init');

if(get_option('gigpress_css') == "gigpress") {
	add_action('wp_head', 'gigpress_styles');
}

if(get_option('gigpress_rss_head') == 1) {
	add_action('wp_head', 'gigpress_head_feed');
}

add_filter('the_content', 'gigpress_upcoming', 7);
add_filter('the_content', 'gigpress_archive', 7);

// We're forced to bed, but we're free to dream.


?>