<?php

// UPCOMING SHOWS TABLE FUNCTION
// =============================

function gigpress_upcoming($filter = null, $content = null) {
	
	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;	
	global $have_tours; $have_tours = FALSE;
	global $have_shows; $have_shows = FALSE;
	
	if ( is_array($filter) ) {
		if( function_exists('shortcode_atts') ) {
		extract( shortcode_atts( array(
			'tour' => FALSE,
			'band' => FALSE,
			'limit' => FALSE
			), $filter ) );
		} else {
			extract($filter);
		}
	}
			
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
	
	// if we're specifying a tour	
	if( is_numeric($tour) ) {
	
		gigpress_allshows_lister("upcoming", $tour, $limit);
		
	} else {
	
		// If grouping by tour	
		if($gpo['tour_segment'] == 1 && $limit == FALSE) {				
		
			if($gpo['tour_location'] == "before") {	
				gigpress_tours_lister("upcoming");
				gigpress_shows_lister("upcoming");
			} else {
				gigpress_shows_lister("upcoming");
				gigpress_tours_lister("upcoming");
			}
		
		// End if grouping by tour
			
		} else {	
			gigpress_allshows_lister("upcoming", $tour, $limit);		
		}
		
	}
			
	if($have_tours == FALSE && $have_shows == FALSE) {
	// We don't have shows of any kind to show you
	?>
	<tbody>
		<tr><td colspan="<?php echo $cols; ?>" class="gigpress-row"><?php echo gigpress_sanitize($gpo['noupcoming']); ?></td></tr>
	</tbody>
	<?php }
	if($gpo['rss_upcoming'] == 1) { ?>
	<tbody>
	<tr>
		<td class="gigpress-rss gigpress-row" colspan="<?php echo $cols; ?>"><a href="<?php echo $gigpress['rss']; ?>" title="<?php echo gigpress_sanitize($gpo['rss_title']); ?>">RSS</a></td>
	</tr>
	</tbody>	
	<?php } ?>
	</table>

<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}




// UPCOMING SHOWS TABLE FUNCTION WRAPPER
// =====================================

function gigpress_upcoming_wrapper($content) {

	if(!preg_match('|[gigpress_upcoming]|', $content)) {
		return $content;
	}
	
	$output	= gigpress_upcoming();

    return str_replace('[gigpress_upcoming]', $output, $content);
				
}