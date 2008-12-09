<?php

function gigpress_admin_table($shows, $type, $scope) {
	
	global $gigpress;
	$gpo = get_option('gigpress_settings');
		
	foreach($shows as $show) {

		// Register that we have a tour with shows
		if($type == "tour") {
			global $tourshows;
			$tourshows = TRUE;
		}

		// Format the date and time as per our options page
		$date = mysql2date($gpo['date_format'], $show->show_date);
		$expire = mysql2date($gpo['date_format'], $show->show_expire);
		$time = gigpress_timefy($show->show_time);
		$secs = $show->show_time{7};

		// Make mappy, check urls
		$address = gigpress_mapit(gigpress_sanitize($show->show_address), gigpress_sanitize($show->show_locale), $show->show_country);
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
				$buy = "<a href=\"$tix_url\" target=\"_blank\" title=\"(".__("opens in a new window", "gigpress").")\">".__("Link", "gigpress")."</a>";
			} else {
				$buy = "<a href=\"$tix_url\">".__("Link", "gigpress")."</a>";
			}		
		
		} elseif ($show->show_status == 'cancelled') {
				$buy = '<strong class="gigpress-cancelled">' . __("Cancelled", "gigpress") . '</strong>';
		} elseif($show->show_status == 'soldout') {
				$buy = '<strong class="gigpress-soldout">' . __("Sold Out", "gigpress") . '</strong>';
		} else {
			$buy = '';
		}
		
		$admittance = gigpress_admittance($show->show_ages);

		// Determine where we return to after an edit or delete
		if($scope == "past") { $return = "gigpress-past"; } else { $return = "gigpress-upcoming"; }

		// Create our edit and delete links
		$edit = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=edit&amp;show_id='.$show->show_id.'&amp;gpreturn='.$return;
		$copy = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=copy&amp;show_id='.$show->show_id;
		$delete = get_bloginfo('wpurl').'/wp-admin/admin.php?page='.$return.'&amp;gpaction=delete&amp;show_id='.$show->show_id.'&amp;gpreturn='.$return;
		$delete = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($delete, 'gigpress-action') : $delete;

		++ $i;
		$style = ($i % 2) ? 'mainstream' : 'alternate';
		// Print out our rows.
		?>
		<tr class="<?php echo $style . ' gigpress-' . $show->show_status; ?>">
			<td><span class="gigpress-date"><?php echo $date; ?><?php if($show->show_multi == 1) { echo(' - ').$expire; }?></span>
			<?php if($secs != "1") { ?>
				<br /><span class="gigpress-time"><?php echo $time; ?></span>
			<?php } ?>
			</td>
			<td><?php echo gigpress_sanitize($show->show_locale) . ", " . $show->show_country; ?></td>
			<td><?php echo $venue; if($address && $venue) echo(" ("); echo $address; if($address && $venue) echo(")"); ?></td>
			<td><?php echo gigpress_sanitize($admittance); ?></td>
			<td class="gp-centre"><?php echo gigpress_sanitize($show->show_price); ?></td>
			<td><?php echo $buy; ?></td>
			<td class="gp-centre">
				<a href="<?php echo $edit; ?>" class="edit" title="<?php _e("Edit", "gigpress"); ?>"><?php _e("Edit", "gigpress"); ?></a> | <a href="<?php echo $copy; ?>" class="edit" title="<?php _e("Copy", "gigpress"); ?>"><?php _e("Copy", "gigpress"); ?></a> | <a href="<?php echo $delete; ?>" class="delete" title="<?php _e("Delete", "gigpress"); ?>"><?php _e("Delete", "gigpress"); ?></a>
			</td>
		</tr>
		<?php if( (!empty($show->show_notes)) || ($show->show_related != 0) ) { ?>
			<tr<?php echo $style; ?>>
				<td>&nbsp;</td>
				<td colspan="6" class="gigpress-notes"><?php echo gigpress_sanitize($show->show_notes); ?>
				<?php echo gigpress_related_link($show->show_related, 'edit'); ?></td>
			</tr>
		<?php }
	
	} // end foreach
}