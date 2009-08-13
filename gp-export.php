<?php
	
	require('../../../wp-blog-header.php');
	
	global $wpdb;
	global $gigpress;
	
	check_admin_referer('gigpress-action');
	
	$now = date('Y-m-d');
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=gigpress-export-" . $now . ".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	// Header row
	_e("Date", "gigpress"); echo("\t");
	_e("Time", "gigpress"); echo("\t");
	_e("End date", "gigpress"); echo("\t");
	_e("City", "gigpress"); echo("\t");
	_e("Country", "gigpress"); echo("\t");
	_e("Venue", "gigpress"); echo("\t");
	_e("Venue address", "gigpress"); echo("\t");
	_e("Venue phone", "gigpress"); echo("\t");
	_e("Venue website", "gigpress"); echo("\t");
	_e("Admittance", "gigpress"); echo("\t");
	_e("Price", "gigpress"); echo("\t");
	_e("Ticket URL", "gigpress"); echo("\t");
	_e("Ticket phone", "gigpress"); echo("\t");
	_e("Notes", "gigpress"); echo("\t");
	_e("Link", "gigpress"); echo("\t");
	_e("Tour", "gigpress"); echo("\t");
	_e("Status", "gigpress"); echo("\n");
	
	$shows = $wpdb->get_results("SELECT * FROM ". $gigpress['gigs_table'] ." WHERE (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') ORDER BY show_date DESC,show_time DESC");
	
	if($shows) {
	
		$bad_chars = array("\t", "\r", "\n", "&#8220;", "&#8221;", "&#8216;", "&#8217;", "&#8212;");
		$replace = array('', '', '', '"', '"', '\'', '\'', '-');
	
		foreach (  $shows as $show ) {
		
			$time = ( $show->show_time{7} == 1 ) ? '' : $show->show_time;
			$end_date = ( $show->show_date == $show->show_expire ) ? '' : $show->show_expire;
			$city = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_locale)));
			$venue = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_venue)));
			$venue_address = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_address)));
			$venue_phone = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_venue_phone)));
			$venue_url = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_show_venue_url)));
			$admittance = gigpress_admittance($show->show_ages);
			$price = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_price)));
			$ticket_url = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_tix_url)));
			$ticket_phone = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_show_tix_phone)));
			$notes = trim(stripslashes(str_replace($bad_chars, $replace, $show->show_notes)));
			
			$link = ( $show->show_related ) ? gigpress_related_link($show->show_related, 'url') : '';
			
			if($show->show_tour_id != 0) {
				$tour = $wpdb->get_var("SELECT tour_name FROM ". $gigpress['tours_table'] ." WHERE tour_id = ". $show->show_tour_id ." LIMIT 1");
				$tour = trim(stripslashes(str_replace($bad_chars, $replace, $tour)));
			} else {
				$tour = '';
			}
			
			echo($show->show_date . "\t" . $time . "\t" . $end_date . "\t" . $city . "\t" . $show->show_country . "\t" . $venue . "\t" . $venue_address . "\t" . $venue_phone . "\t" . $venue_url . "\t" . $admittance . "\t" . $price . "\t" . $ticket_url . "\t" . $ticket_phone . "\t" . $notes . "\t" . $link . "\t" . $tour . "\t" . $show->show_status . "\n");
			
		}
	
	}
	
?>