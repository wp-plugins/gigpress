<?php
function gigpress_install() {

	// See if our main table already exists, and if not,
	// create both of our tables.  We should probably check for each individually
	// - and concomitantly create each individually.  But alas.  We do not.	

	global $wpdb;
	global $gigpress;
	global $default_settings;
	global $now;
	
	$charset_collate = '';

	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	
	if($wpdb->get_var("SHOW TABLES LIKE '".$gigpress['gigs_table']."'") != $gigpress['gigs_table']) {
		
		$create = "CREATE TABLE " . $gigpress['gigs_table'] . " (
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
		show_tix_phone VARCHAR(255),
		show_venue VARCHAR(255) DEFAULT 'TBA' NOT NULL,
		show_venue_url VARCHAR(255),
		show_venue_phone VARCHAR(255),
		show_ages ENUM('All Ages','No minors','All Ages/Licensed','Not sure'),
		show_notes TEXT,
		show_related BIGINT(20),
		show_status VARCHAR(32) DEFAULT 'active',
		show_tour_restore INTEGER(1) DEFAULT 0,
		PRIMARY KEY  (show_id)
		) $charset_collate;
		CREATE TABLE " . $gigpress['tours_table'] . " (
		tour_id INTEGER(4) AUTO_INCREMENT,
		tour_name VARCHAR(255) NOT NULL,
		tour_order INTEGER(4) DEFAULT 0,
		tour_status VARCHAR(32) DEFAULT 'active',
		PRIMARY KEY  (tour_id)
		) $charset_collate;
		ALTER TABLE " . $gigpress['gigs_table'] . " ADD FOREIGN KEY (show_tour_id) REFERENCES " . $gigpress['tours_table'] . " (tour_id)
		;";
		
		if(file_exists( ABSPATH . 'wp-admin/includes/upgrade.php')) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		} else {
			 require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		}
			
		dbDelta($create);
		
		add_option("gigpress_settings", $default_options);

	}
}