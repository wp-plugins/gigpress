<?php
// If we haven't upgraded to serialized options yet
if ( $gpo == FALSE ) {
	foreach ( $default_options as $key => $value ) {
		if ( $existing = get_option( 'gigpress_' . $key ) ) {
			$default_options[$key] = $existing;
			delete_option( 'gigpress_' . $key );
		}
	}
	
	add_option('gigpress_settings', $default_options);
	$gpo = get_option('gigpress_settings');
}	


if ( $gpo['db_verson'] < $gigpress['db_version'] ) {
	
	$charset_collate = '';

	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}
	
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
	
	dbDelta($upgrade);
	
	// Upgrading from DB version 1.0
	if($installed_db == "1.0") {
		
		// Added in DB version 1.1
		
		// We need to make sure the current show_dates in the DB are cloned to the show_expire fields
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
		
		// Now set show_time to NA
		$settime = $wpdb->query("
		UPDATE ".$gigpress['gigs_table']." SET show_time = '00:00:01'
		");
		
		// Added in DB version 1.2
		
		// Set status for all shows and tours
		$showstatus = $wpdb->query("UPDATE ".$gigpress['gigs_table']." SET show_status = 'active'");	
		$tourstatus = $wpdb->query("UPDATE ".$gigpress['tours_table']." SET tour_status = 'active'");	
		
		// Added in DB version 1.3
		$gpo['date_format_long'] = $gpo['date_format'];

	
	} // End upgrade from DB version 1.0

	// Upgrading from DB version 1.1
	if($installed_db == "1.1") {
	
		// Added in DB version 1.2	
		
		// Set status for all shows and tours
		$showstatus = $wpdb->query("UPDATE ".$gigpress['gigs_table']." SET show_status = 'active'");	
		$tourstatus = $wpdb->query("UPDATE ".$gigpress['tours_table']." SET tour_status = 'active'");	
		
		// Added in DB version 1.3
		$gpo['date_format_long'] = $gpo['date_format'];
			
				
	// End upgrade from DB version 1.1
	
	}
	
	// Upgrading from DB version 1.2
	if($installed_db == "1.2") {

		// Added in DB version 1.3
		$gpo['date_format_long'] = $gpo['date_format'];
						
	// End upgrade from DB version 1.2
	
	}	
	
	update_option('gigpress_settings', $gpo);

}