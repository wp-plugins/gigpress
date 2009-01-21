<?php

function gigpress_feed() {
	// BUILD THE RSS FEED
	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;	
	header('Content-type: text/xml; charset='.get_bloginfo('charset'));
	echo('<?xml version="1.0" encoding="'.get_bloginfo('charset').'"?>
	<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
			<title>'.gigpress_sanitize($gpo['rss_title']).'</title>
			<description>'.gigpress_sanitize($gpo['rss_title']).'</description>
			<link>');
			if($gpo['shows_page'] != "") { echo(gigpress_check_url($gpo['shows_page'])); } else { bloginfo('home'); }
			echo('</link>');

	$shows = $wpdb->get_results("
		SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire >= '". $now ."' AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') ORDER BY show_date ASC
	");

	if($shows != FALSE) {
		foreach ($shows as $show) {

			$date = mysql2date($gpo['date_format'], $show->show_date);
			$longdate = mysql2date($gpo['date_format_long'], $show->show_date);
			$expire = mysql2date($gpo['date_format_long'], $show->show_expire);
			$time = gigpress_timefy($show->show_time);
			$secs = $show->show_time{7};
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
			
			$admittance = gigpress_admittance($show->show_ages);
			
			// Are we part of a tour?
			$tourname = $wpdb->get_var("SELECT tour_name from ".$gigpress['tours_table']." WHERE tour_id = ".$show->show_tour_id." LIMIT 1");

			// See if there's a buy link
			// if so, make magic happen with the clicking and such
			if(!empty($show->show_tix_url) && $show->show_status = 'active') {
			
				if($gpo['target_blank'] == 1) {
					$buy = "<a href=\"$tix_url\" target=\"_blank\" title=\"(".__("opens in a new window", "gigpress").")\">".__("Buy Tickets", "gigpress")."</a>. ";
				} else {
					$buy = "<a href=\"$tix_url\">".__("Buy tickets", "gigpress")."</a>. ";
				}		
			
			} elseif ($show->show_status == 'cancelled') {
					$buy = '<strong>' . __("Cancelled", "gigpress") . '</strong> ';
			} elseif($show->show_status == 'soldout') {
					$buy = '<strong>' . __("Sold Out", "gigpress") . '</strong>';
			} else {
				$buy = '';
			}

			echo('<item>
				<title><![CDATA['.stripslashes($gpo['band']).' '.__("in", "gigpress").' '.stripslashes($show->show_locale).' '.__("on", "gigpress").' '.$date.']]></title>
				<description><![CDATA['); ?>
<ul>
<?php if($tourname) { ?>
	<li><strong><?php echo stripslashes($gpo['tour_label']); ?>:</strong> <?php echo stripslashes($tourname); ?></li>
<?php } ?>
	<li><strong><?php _e("Date", "gigpress"); ?>:</strong> <?php echo $longdate; ?><?php if($show->show_multi == 1) { ?>&ndash; <?php echo $expire; ?><?php } ?></li>
<?php if($secs != "1") { ?>
	<li><strong><?php _e("Time", "gigpress"); ?>:</strong> <?php echo $time; ?></li>
<?php } ?>
	<li><strong><?php _e("City", "gigpress"); ?>:</strong> <?php echo stripslashes($show->show_locale); ?></li>
	<li><strong><?php _e("Venue", "gigpress"); ?>:</strong> <?php echo $venue; ?></li>
<?php if($show->show_status != 'cancelled') { ?>
	<?php if($address) { ?>
		<li><strong><?php _e("Address", "gigpress"); ?>:</strong> <?php echo $address; ?></li>
	<?php } ?>
	<?php if($show->show_venue_phone) { ?>
		<li><strong><?php _e("Venue phone", "gigpress"); ?>:</strong> <?php echo stripslashes($show->show_venue_phone); ?></li>
	<?php } ?>	
		<li><strong><?php _e("Country", "gigpress"); ?>:</strong> <?php echo $show->show_country; ?></li>
	<?php if($show->show_price) { ?>
		<li><strong><?php _e("Admission", "gigpress"); ?>:</strong> <?php echo stripslashes($show->show_price); ?></li>
	<?php } ?>
	<?php if($show->show_ages != "Not sure") { ?>
		<li><strong><?php _e("Age restrictions", "gigpress"); ?>:</strong> <?php echo stripslashes($admittance); ?></li>
	<?php } ?>
	<?php if($show->show_tix_phone) { ?>
		<li><strong><?php _e("Box office", "gigpress"); ?>:</strong> <?php echo stripslashes($show->show_tix_phone); ?></li>
	<?php } ?>	
	<?php if( ($show->show_date <= $now && $buy != "") || ($show->show_status != 'active') ) { ?>
		<li><?php echo $buy; ?></li>
	<?php } ?>
<?php } else { ?>
	<li><?php echo $buy; ?></li>
<?php } ?>
	<?php if($show->show_notes) { ?>
	<li><strong><?php _e("Notes", "gigpress"); ?>:</strong> <?php echo stripslashes($show->show_notes); ?></li>
	<?php } ?>
	<?php if($show->show_related != 0) { ?>
		<li><?php echo gigpress_related_link($show->show_related, 'view'); ?></li>
	<?php } ?>	
</ul>
<?php echo(']]></description>
				<link>');
				if($show->show_related) {
					echo gigpress_related_link($show->show_related, 'url');
				} elseif($gpo['shows_page'] != "") {
					echo gigpress_check_url($gpo['shows_page']);
				} else {
					bloginfo('home');
				}
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


function gigpress_head_feed() {
	// RSS feed in the <head>
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	echo('<link href="'.$gigpress['rss'].'" rel="alternate" type="application/rss+xml" title="'.gigpress_sanitize($gpo['rss_title']).'" />
	');
}


function add_gigpress_feed() {
	// Tell WP about the feed
	add_feed('gigpress','gigpress_feed');
}