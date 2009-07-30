<?php

function gigpress_admin_past() {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	global $now;
	global $tourshows;
	$tourshows = FALSE;
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "delete") {
		require_once('admin-handlers.php');
		gigpress_delete_show();		
	}
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "undo") {
		require_once('admin-handlers.php');
		gigpress_undo('show');	
	}
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "update") {
		require_once('admin-handlers.php');
		gigpress_update_show();
	} ?>

	<div class="wrap gigpress <?php echo $gigpress['admin_class']; ?> gp-past">

	<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>		
	<h2><?php _e("Past shows", "gigpress"); ?></h2>
	
	<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e("Date", "gigpress"); ?></th>
					<th scope="col"><?php _e("City", "gigpress"); ?></th>
					<th scope="col"><?php _e("Venue", "gigpress"); ?></th>
					<th scope="col"><?php _e("Admittance", "gigpress"); ?></th>
					<th scope="col" class="gp-centre"><?php _e("Price", "gigpress"); ?></th>
					<th scope="col"><?php _e("Buy link", "gigpress"); ?></th>
					<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
				</tr>
			</thead>
			<tbody>
	<?php
		
		// Retrieve all tours from the DB
		$tours = $wpdb->get_results("SELECT * FROM ". $gigpress['tours_table'] ." WHERE tour_status = 'active' ORDER BY tour_order ASC");
		
		// If there are indeed tours to be found
		if($tours != FALSE) {
		
			foreach($tours as $tour) {
			
				// See if each tour actually has any shows assigned to it
				$shows = $wpdb->get_results("
					SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') AND show_expire < '". $now ."' ORDER BY show_date DESC
				");
				
				// If there are shows for the tour, display that shiznatt
				if($shows != FALSE) { ?>
				
				<tr>
					<th colspan="7" class="gp-thead"><h3><?php echo gigpress_sanitize($gpo['tour_label']); echo ': ' . $tour->tour_name; ?></h3></th>
				</tr>
		
				<?php gigpress_admin_table($shows, "tour", "past");
			
				} // if there are no shows, do nothing
				
			} // end foreach tour

		} // And if there aren't any tours, do nothing
		
		// Get all upcoming dates from the DB that are NOT part of a tour
		
		$past = $wpdb->get_results("
			SELECT * FROM ". $gigpress['gigs_table'] ." WHERE show_expire < '". $now ."' AND show_tour_id = 0 AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled') ORDER BY show_date DESC");
		
		// Do we have dates?
		if($past != FALSE) { ?>
			
			<?php if($tourshows == TRUE) { ?>
			<tr>
				<th colspan="7" class="gp-thead"><h3><?php echo gigpress_sanitize($gpo['individual_heading']); ?></h3></th>
			</tr>
			
			<?php }
		
			gigpress_admin_table($past, "individual", "past");
			
		} // And if there aren't any individual shows, move on
		
		if($tourshows != TRUE && $past == FALSE) {
		// We don't have shows of any kind to show you
		?>
			<tr><td colspan="7"><strong><?php echo gigpress_sanitize($gpo['nopast']); ?></strong></td></tr>
		<?php }
		unset($tourshows); ?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="col"><?php _e("Date", "gigpress"); ?></th>
					<th scope="col"><?php _e("City", "gigpress"); ?></th>
					<th scope="col"><?php _e("Venue", "gigpress"); ?></th>
					<th scope="col"><?php _e("Admittance", "gigpress"); ?></th>
					<th scope="col" class="gp-centre"><?php _e("Price", "gigpress"); ?></th>
					<th scope="col"><?php _e("Buy link", "gigpress"); ?></th>
					<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
				</tr>
			</tfoot>
		</table>
	</div>
<?php }