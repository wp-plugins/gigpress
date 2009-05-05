<?php

function gigpress_show_related($content) {
	
	global $is_excerpt;
	if ( $is_excerpt == TRUE ) { $is_excerpt = FALSE; return $content; }
	
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $wpdb;
	global $post;
		
	$shows = $wpdb->get_results("SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_related = ". $post->ID ." AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled')");

	if($shows != FALSE) {
		
		ob_start();
		
			if ( $gpo['related_heading'] ) {
				echo('<h3 class="gigpress-related-heading">' . $gpo['related_heading'] . '</h3>');
			}
			
			foreach ($shows as $show) {
						
				// Format the date as per our options page
				$date = mysql2date($gpo['date_format_long'], $show->show_date);
				$expire = mysql2date($gpo['date_format_long'], $show->show_expire);
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
						$buy = '<strong class="gigpress-cancelled">' . __("Cancelled", "gigpress") . '</strong> ';
				} elseif($show->show_status == 'soldout') {
						$buy = '<strong class="gigpress-soldout">' . __("Sold Out", "gigpress") . '</strong>';
				} else {
					$buy = '';
				}
				
				$admittance = gigpress_admittance($show->show_ages);
				
				$tourname = $wpdb->get_var("SELECT tour_name FROM ". $gigpress['tours_table'] ." WHERE tour_id = ". $show->show_tour_id ." LIMIT 1");
				if($tourname) $tourname = gigpress_sanitize($tourname);
				
				?>
												
				<ul class="gigpress-related-show <?php if($show->show_status != 'cancelled') { echo('vevent '); } echo $show->show_status; ?>">
				<?php if($tourname) { ?>
					<li><span class="gigpress-related-label"><?php echo gigpress_sanitize($gpo['tour_label']); ?>:</span> <span class="gigpress-related-item"><?php echo $tourname; ?></span></li>
				<?php } ?>
					<li><span class="gigpress-related-label"><?php _e("Date", "gigpress"); ?>:</span> <span class="gigpress-related-item"><abbr class="dtstart" title="<?php echo $hdate; ?>"><?php echo $date; ?></abbr>
					<?php if($show->show_multi == 1) { ?>
						&ndash; <abbr class="dtend" title="<?php echo $hexpire; ?>"><?php echo $expire; ?></abbr>
					<?php } ?>
					</span></li>
					<?php if($secs != "1") { ?>
						<li><span class="gigpress-related-label"><?php _e("Time", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo $time; ?></span></li>
					<?php } ?>
					<li><span class="gigpress-related-label"><?php _e("City", "gigpress"); ?>:</span> <span class="gigpress-related-item summary"><span class="hide"><?php echo gigpress_sanitize($gpo['band']); _e("in", "gigpress"); ?> </span><?php echo gigpress_sanitize($show->show_locale); ?></span></li>
					<li><span class="gigpress-related-label"><?php _e("Venue", "gigpress"); ?>:</span> <span class="gigpress-show-related location"><?php echo $venue; ?></span></li>
					<?php if($show->show_status != 'cancelled') { ?>
						<?php if($address) { ?>
							<li><span class="gigpress-related-label"><?php _e("Address", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo $address; ?></span></li>
						<?php } ?>
						<?php if($show->show_venue_phone) { ?>
							<li><span class="gigpress-related-label"><?php _e("Venue phone", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo gigpress_sanitize($show->show_venue_phone); ?></span></li>
						<?php } ?>
						<li><span class="gigpress-related-label"><?php _e("Country", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo $show->show_country; ?></span></li>
						<?php if($show->show_price) { ?>
							<li><span class="gigpress-related-label"><?php _e("Admission", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo gigpress_sanitize($show->show_price); ?></span></li>
						<?php } ?>
						<?php if($show->show_ages != "Not sure") { ?>
							<li><span class="gigpress-related-label"><?php _e("Age restrictions", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo gigpress_sanitize($admittance); ?></span></li>
						<?php } ?>
						<?php if($show->show_tix_phone) { ?>
							<li><span class="gigpress-related-label"><?php _e("Box office", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo gigpress_sanitize($show->show_tix_phone); ?></span></li>
						<?php } ?>
						<?php if( ($show->show_date <= $now && $buy != "") || ($show->show_status != 'active') ) { ?>
							<li><?php echo $buy; ?></li>
						<?php }
					} else {
						echo("<li>$buy</li>");
					} ?>
					<?php if($show->show_notes) { ?>
							<li><span class="gigpress-related-label"><?php _e("Notes", "gigpress"); ?>:</span> <span class="gigpress-related-item"><?php echo gigpress_sanitize($show->show_notes); ?></span></li>
					<?php } ?>
				</ul>
				
		<?php }
		
		$giginfo = ob_get_clean();
		
		if ( $gpo['related_position'] == "before" ) {
			$output = $giginfo . $content;
		} else {
			$output = $content . $giginfo;
		}
		
		return $output;
						
	} else {
	
		return $content;
	}
}


function gigpress_related_link($postid, $format) {
	// Show the related post link
	if($postid == 0) { return; }
	
	$gpo = get_option('gigpress_settings');
	
	switch($format) {
		case 'url':
			$link = get_permalink($postid);
			$output = $link;
			break;
		case 'edit':
			$link = get_bloginfo('wpurl') . '/wp-admin/post.php?action=edit&amp;post=' . $postid;
			$output = '<a href="' . $link . '">' . $gpo['related'] . '</a>';
			break;
		case 'view':
			$link = get_permalink($postid);
			$output = '<a href="' . $link . '">' . $gpo['related'] . '</a>';
			break;
	}
		
	return $output;
}


function gigpress_exclude_shows($query) {
	// Excludes the Related Post category from normal listings
	global $wp_query;
	$gpo = get_option('gigpress_settings');
	
	$categories = array($gpo['related_category']);
	
	if( !is_category() && !is_single() && !is_tag() && !is_admin() ) { 
		$wp_query->set('category__not_in', $categories); 
	}
}


function gigpress_remove_related($postID) {
	// Remove the post relation if it's deleted in WP
	global $wpdb;
	global $gigpress;
	
	$cleanup = $wpdb->query("UPDATE ". $gigpress['gigs_table'] ." SET show_related = '0' WHERE show_related = '".$postID."'");
	return $postID;
	
}