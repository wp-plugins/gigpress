<?php

// TOURS LISTER
// ============

function gigpress_tours_lister($dates) {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;
	global $have_tours;

	$heading = gigpress_sanitize($gpo['tour_heading']);
	// See if we're displaying the country, and build the table accordingly
	if($gpo['display_country'] == 1) { $cols = 4; } else { $cols = 3; }

	// Retrieve all tours from the DB
	$tours = $wpdb->get_results("
		SELECT * FROM ". $gigpress['tours_table'] ." WHERE tour_status = 'active' ORDER BY tour_order ASC
	");
	
	// If there are indeed tours to be found
	if($tours != FALSE) {
	
		foreach($tours as $tour) {
		
			$query = "SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = '". $tour->tour_id ."' 	AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') AND ";
			if($dates == "upcoming") {
				$query .= "show_expire >= '$now' ORDER BY show_date ASC";
			} else {
				$query .= "show_expire < '$now' ORDER BY show_date DESC";
			}		

			// See if each tour actually has any shows assigned to it
			$shows = $wpdb->get_results("$query");
			
			// If there are shows for the tour, display that shiznatt
			if($shows != FALSE) {
			$have_tours = TRUE;
			?>
			<tbody>	
			<tr>
				<th colspan="<?php echo $cols; ?>" class="gigpress-heading" id="tour-<?php echo $tour->tour_id; ?>">
					<?php echo "<$heading>".gigpress_sanitize($tour->tour_name)."</$heading>"; ?>
				</th>
			</tr>
			</tbody>
			<?php
			gigpress_gigs_table($shows, "tour", $dates);
				
			} // If there aren't any shows for the tour, do nothing
			
		} // end foreach tour
		
	} // And if there aren't any tours at all, do nothing

}



// INDIVIDUAL SHOWS LISTER
// =======================

function gigpress_shows_lister($dates) {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;
	global $have_tours;
	global $have_shows;
	
	$heading = gigpress_sanitize($gpo['tour_heading']);
	// See if we're displaying the country, and build the table accordingly
	if($gpo['display_country'] == 1) { $cols = 4; } else { $cols = 3; }

	$query = "SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = '0' AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') AND ";
	if($dates == "upcoming") {
		$query .= "show_expire >= '$now' ORDER BY show_date ASC";
	} else {
		$query .= "show_expire < '$now' ORDER BY show_date DESC";
	}

	// Get all upcoming dates from the DB that are NOT part of a tour
	$shows = $wpdb->get_results($query);
	
	if($shows != FALSE) {
		
		// Only show a heading for these little guys if there were tours above them ...
		if($have_tours == TRUE) { ?>
		<tbody>
			<tr>
				<th colspan="<?php echo $cols; ?>" class="gigpress-heading">
				<?php echo "<$heading>".gigpress_sanitize($gpo['individual_heading'])."</$heading>"; ?>
				</th>
			</tr>
		</tbody>
	<?php }
		$have_shows = TRUE;
		gigpress_gigs_table($shows, "individual", $dates);	
	}
}




// ALL SHOWS LISTER
// ================

function gigpress_allshows_lister($dates, $tour = FALSE) {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;
	global $have_shows;
	
	// See if we're displaying the country, and build the table accordingly
	if($gpo['display_country'] == 1) { $cols = 4; } else { $cols = 3; }
	
	$query = "SELECT * FROM ". $gigpress['gigs_table'] ." WHERE (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') AND ";
	
	if($tour) {
		$query .= "show_tour_id = $tour AND ";
	}
	
	if($dates == "upcoming") {
		$query .= "show_expire >= '$now' ORDER BY show_date ASC";
	} else {
		$query .= "show_expire < '$now' ORDER BY show_date DESC";
	}
		
	// Get all upcoming dates from the DB
		
	$shows = $wpdb->get_results($query);
		
	if($shows != FALSE) {
		$have_shows = TRUE;
		gigpress_gigs_table($shows, "all", "$dates");
	}

}




// OUTPUT: SHOWS TABLE
// ===================


function gigpress_gigs_table($showarray, $type, $scope) {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');

	foreach($showarray as $show) {
		
		if($type == "all") {
			$tour = "";
			if($show->show_tour_id != 0) {
				$tour = $wpdb->get_var("
				SELECT tour_name FROM ". $gigpress['tours_table'] ." WHERE tour_id = ". $show->show_tour_id ." LIMIT 1
			");
			}
		}
	
		// Format the date as per our options page
		$date = mysql2date($gpo['date_format'], $show->show_date);
		$expire = mysql2date($gpo['date_format'], $show->show_expire);
		$time = gigpress_timefy($show->show_time);
		$secs = $show->show_time{7};
		$hdate = $show->show_date." ".$show->show_time;
		$hexpire = $show->show_expire." ".$show->show_time;
	
	
		// Make the address Googley
		$address = gigpress_mapit(gigpress_sanitize($show->show_address), gigpress_sanitize($show->show_locale), $show->show_country);
	
		// Make sure our links are protocol'd
		$venue_url = gigpress_check_url(gigpress_sanitize($show->show_venue_url));
		$tix_url = gigpress_check_url(gigpress_sanitize($show->show_tix_url));
	
		// Link up the venue name if it has a url associated with it
		if(!empty($show->show_venue_url)) {
		
			if($gpo['target_blank'] == 1) {
				$venue = "<a href=\"$venue_url\" target=\"_blank\" title=\"(".__("opens in a new window", "gigpress").")\">".gigpress_sanitize($show->show_venue)."</a>";
			} else {
				$venue = "<a href=\"$venue_url\">".gigpress_sanitize($show->show_venue)."</a>";
			}
		
		} else {
			$venue = gigpress_sanitize($show->show_venue);
		}

		// See if there's a buy link
		// if so, make magic happen with the clicking and such
		if(!empty($show->show_tix_url) && $show->show_status = 'active') {
		
			if($gpo['target_blank'] == 1) {
				$buy = "<a href=\"$tix_url\" target=\"_blank\" title=\"(".__("opens in a new window", "gigpress").")\">".__("Buy Tickets", "gigpress")."</a>. ";
			} else {
				$buy = "<a href=\"$tix_url\">".__("Buy tickets", "gigpress")."</a>. ";
			}		
		
		} elseif ($show->show_status == 'cancelled') {
				$buy = '<strong class="gigpress-cancelled">' . __("Cancelled", "gigpress") . '.</strong> ';
		} elseif($show->show_status == 'soldout') {
				$buy = '<strong class="gigpress-soldout">' . __("Sold Out", "gigpress") . '.</strong> ';
		} else {
			$buy = '';
		}

		
		$admittance = gigpress_admittance($show->show_ages);
		
		// See if we're displaying the country, and build the table accordingly
		if($gpo['display_country'] == 1) { $cols = 3; } else { $cols = 2; }
	
		// This is for alternating row styles
		++ $i;
		$style = ($i % 2) ? '' : ' gigpress-alt';

		// Print out our rows
		?>
		<tbody<?php if($show->show_status != 'cancelled') echo(' class="vevent"'); ?>>
		<tr class="gigpress-row<?php echo $style . ' ' . $show->show_status; ?>">
			<td class="gigpress-date">
			<?php if ( $show->show_related && $gpo['relatedlink_date'] == 1 ) {
			echo('<a href="' . gigpress_related_link($show->show_related, 'url') . '">');
			} ?>
			<abbr class="dtstart" title="<?php echo $hdate; ?>"><?php echo $date; ?></abbr><?php if($show->show_multi == 1) { echo(' - <abbr class="dtend" title="'.$hexpire.'">'.$expire.'</abbr>'); }?>
			<?php if ( $show->show_related && $gpo['relatedlink_date'] == 1 ) {
			echo('</a>');
			} ?>
			</td>
			<td class="gigpress-city summary"><span class="hide"><?php echo gigpress_sanitize($gpo['band']); ?> <?php _e("in", "gigpress"); ?> </span>
			<?php if ( $show->show_related && $gpo['relatedlink_city'] == 1 ) {
			echo('<a href="' . gigpress_related_link($show->show_related, 'url') . '">');
			} ?>
			<?php echo gigpress_sanitize($show->show_locale); ?>
			<?php if ( $show->show_related && $gpo['relatedlink_city'] == 1 ) {
			echo('</a>');
			} ?>
			</td>
			<td class="gigpress-venue location"><?php echo $venue; ?></td>
			<?php if($cols == 3) { ?>
				<td class="gigpress-country"><?php echo $show->show_country; ?></td>
			<?php } ?>
		</tr>
		<tr class="gigpress-info<?php echo $style . ' ' . $show->show_status; ?>">
			<td>&nbsp;</td>
			<td colspan="<?php echo $cols ?>" class="description">
			<?php if($show->show_status != 'cancelled') { ?>
				<?php if($tour) { ?><span class="gigpress-info-item"><span class="gigpress-info-label"><?php echo $gpo['tour_label']; ?>:</span> <?php echo gigpress_sanitize($tour).". " ?></span><?php } ?>
				<?php if($secs != "1") { ?><span class="gigpress-info-item"><span class="gigpress-info-label"><?php _e("Time", "gigpress"); ?>:</span> <?php echo $time.". " ?></span><?php } ?>
				<?php if($show->show_price) { ?><span class="gigpress-info-item"><span class="gigpress-info-label"><?php _e("Admission", "gigpress"); ?>:</span> <?php echo gigpress_sanitize($show->show_price) .'. '; ?></span><?php } ?>
				<?php if($show->show_ages != "Not sure") { ?><span class="gigpress-info-item"><span class="gigpress-info-label"><?php _e("Age restrictions", "gigpress"); ?>:</span> <?php echo gigpress_sanitize($admittance) .'. '; ?></span><?php } ?>
				<?php if($scope == "upcoming" && $buy != "") { ?> <span class="gigpress-info-item"><?php echo $buy; ?></span><?php } ?>
				<?php if($show->show_tix_phone) { ?><span class="gigpress-info-item"><span class="gigpress-info-label"><?php _e("Box office", "gigpress"); ?>:</span> <?php echo gigpress_sanitize($show->show_tix_phone) .'. '; ?></span><?php } ?>
				<?php if($address) { ?> <span class="gigpress-info-item"><span class="gigpress-info-label"><?php _e("Address", "gigpress"); ?>:</span> <?php echo $address .'. '; ?></span><?php } ?>
				<?php if($show->show_venue_phone) { ?><span class="gigpress-info-item"><span class="gigpress-info-label"><?php _e("Venue phone", "gigpress"); ?>:</span> <?php echo gigpress_sanitize($show->show_venue_phone) .'. '; ?></span><?php } ?>				
				<?php if($show->show_notes) { ?> <span class="gigpress-info-item"><?php echo gigpress_sanitize($show->show_notes); ?></span><?php } ?>
				<?php if($show->show_related && $gpo['relatedlink_notes'] == 1) { ?> <span class="gigpress-info-item"><?php echo gigpress_related_link($show->show_related, 'view'); ?></span><?php } ?>
			<?php } else {
				echo $buy;	
			} ?>
			</td>
		</tr>
		</tbody> <?php
	} // end foreach show
}