<?php

// PAST SHOWS TABLE FUNCTION
// =========================

function gigpress_archive($filter) {
	
	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;	
	global $have_tours; $have_tours = FALSE;
	global $have_shows; $have_shows = FALSE;
	
	if( function_exists('shortcode_atts') ) {
	extract( shortcode_atts( array(
		'tour' => FALSE,
		'band' => FALSE
		), $filter ) );
	} else {
		extract($filter);
	}

	$heading = gigpress_sanitize($gpo['tour_heading']);
	
	// See if we're displaying the country, and build the table accordingly
	if($gpo['display_country'] == 1) { $cols = 4; } else { $cols = 3; }
	
	ob_start();
	
	?>
	<table class="gigpress-table hcalendar" cellspacing="0">
		<tbody>
			<tr class="gigpress-header">
				<th scope="col" class="gigpress-date"><?php _e("Date", "gigpress"); ?></th>
				<th scope="col" class="gigpress-city"><?php _e("City", "gigpress"); ?></th>
				<th scope="col" class="gigpress-venue"><?php _e("Venue", "gigpress"); ?></th>
				<?php if($cols == 4) { ?>
				<th scope="col" class="gigpress-country"><?php _e("Country", "gigpress"); ?></th>
				<?php } ?>
		</tr>
		</tbody>

<?php
	
	if($tour) {
	
		gigpress_allshows_lister("past",$tour);
		
	} else {
	
		// If grouping by tour	
			
		if($gpo['tour_segment'] == 1) {
	
			if($gpo['tour_location'] == "before") {	
				gigpress_tours_lister("past");
				gigpress_shows_lister("past");
			} else {
				gigpress_shows_lister("past");
				gigpress_tours_lister("past");
			}
		
		// End if grouping by tour
			
		} else {
			gigpress_allshows_lister("past");		
		}
	}
				
	if($have_tours == FALSE && $have_shows == FALSE) {
	// We don't have shows of any kind to show you
	?>
	<tbody>
	<tr><td colspan="<?php echo $cols; ?>" class="gigpress-row"><?php echo gigpress_sanitize($gpo['nopast']); ?></td></tr>
	</tbody>
	<?php } ?>
	</table>

<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


// PAST SHOWS TABLE FUNCTION WRAPPER
// =================================


function gigpress_archive_wrapper($content) {

	if(!preg_match('|[gigpress_archive]|', $content)) {
		return $content;
	}

	ob_start();
		gigpress_archive();
		$output = ob_get_contents();
	ob_end_clean();

    return str_replace('[gigpress_archive]', $output, $content);
}