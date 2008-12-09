<?php

function gigpress_tours() {

	global $wpdb;
	global $gigpress;
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "add") {
		require_once('admin-handlers.php');
		gigpress_add_tour();		
	}
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "update") {
		require_once('admin-handlers.php');
		gigpress_update_tour();
	}
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "delete") {
		require_once('admin-handlers.php');
		gigpress_delete_tour();		
	}

	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "undo") {
		require_once('admin-handlers.php');
		gigpress_undo('tour');		
	}

	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "setorder") {
		require_once('admin-handlers.php');
		gigpress_setorder();		
	}
	?>

	<div class="wrap gigpress <?php echo $gigpress['admin_class']; ?> gp-tours">

	<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>		
	<h2><?php _e("Tours", "gigpress"); ?></h2>
	
	<p><?php _e("A tour is simply a collection of shows that you want to group together.", "gigpress"); ?></p>
	
	
	<?php // Looks like we're editing a tour
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") {
	
		// Load the previous show info into the edit form, and so forth 
		$tourdata = $wpdb->get_results("
		SELECT * from ". $gigpress['tours_table'] ." WHERE tour_id = ". $_GET['tour_id'] ." LIMIT 1
		");
		if($tourdata) {
			// We got the goods from the DB - proceed with the edit form ...
			
			foreach($tourdata as $tour) {
				$tourname = stripslashes($tour->tour_name);		
			}
	
		?>
		
		<h3><?php _e("Edit this tour", "gigpress"); ?></h3>
	
		<form method="post" action="<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?page=gigpress-tours"; ?>">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="update" />
		<input type="hidden" name="tour_id" value="<?php echo $_GET['tour_id']; ?>" />
			
		<table class="optiontable editform gp-table">
			<tr>
				<th scope="row"><label for="tour_name"><?php _e("Tour name", "gigpress"); ?>:</label></th>
				<td><input name="tour_name" id="tour_name" type="text" size="48" value="<?php echo $tourname; ?>" />
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e("Update tour", "gigpress") ?>" /> <?php _e("or", "gigpress"); ?> <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gigpress-tours"><?php _e("cancel", "gigpress"); ?></a></p>
		
		</form>
	
		<?php
			
		} else {
		
			// Couldn't grab the show's details from the database
			// for some reason
			echo('<div id="message" class="error fade"><p>');
			_e("Sorry, but we had trouble loading that tour for editing.", "gigpress");
			echo('</div>');
		}
		
	} else {
	// Display the "add a tour" form
	?>
		<h3><?php _e("Add a tour", "gigpress"); ?></h3>
		
		<?php
			$order = $wpdb->get_var("
			SELECT count(*) FROM ". $gigpress['tours_table'] ." WHERE tour_status = 'active'
			");
			$order++;
		?>
		
		<form method="post" action="<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?page=gigpress-tours"; ?>">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="add" />
		<input type="hidden" name="tour_order" value="<?php echo $order; ?>" />
			
		<table class="optiontable editform gp-table">
			<tr>
				<th scope="row"><label for="tour_name"><?php _e("Tour name", "gigpress") ?>:</label></th>
				<td><input name="tour_name" id="tour_name" type="text" size="48" />
				</td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e("Add tour", "gigpress") ?>" /></p>
		
		</form>
	
	<?php } ?>
	
	<?php // Looks like we're reordering our tours
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "reorder") { ?>
	
	<h3><?php _e("Reorder tours", "gigpress"); ?></h3>
	
	<form method="post" action="<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?page=gigpress-tours&gpaction=setorder"; ?>">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="setorder" />
	
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e("Display order", "gigpress"); ?></th>
				<th scope="col"><?php _e("Tour name", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Tour ID", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Number of shows", "gigpress"); ?></th>
				<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php
		// Get all upcoming tours from the DB
		$tours = $wpdb->get_results("SELECT * from ". $gigpress['tours_table'] ." WHERE tour_status = 'active' ORDER BY tour_order ASC");
		
		// Do we have tours?
		if($tours) {
			$num = 0;
			foreach($tours as $tour) {
			
				if($n = $wpdb->get_var("
					SELECT count(*) FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ." AND (show_status = 'active' OR show_status = 'soldout' OR show_status = 'cancelled')
				")) {
					$count = $n;
				} else {
					$count = 0;
				}

				
				// Create our edit and delete links
				$edit = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-tours&amp;gpaction=edit&amp;tour_id='.$tour->tour_id;
				$delete = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-tours&amp;gpaction=delete&amp;tour_id='.$tour->tour_id;
				$delete = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($delete, 'gigpress-action') : $delete;

				
				++ $i;
				$style = ($i % 2) ? '' : ' class="alternate"';
				// Print out our rows.
				?>
				<tr<?php echo $style; ?>>
					<td>
						<input type="hidden" name="id[<?php echo $num; ?>]" value="<?php echo $tour->tour_id; ?>" />
						<input type="text" size="2" name="order[<?php echo $num; ?>]" value="<?php echo $tour->tour_order; ?>" />
					</td>
					<td><?php echo gigpress_sanitize($tour->tour_name); ?></td>
					<td class="gp-centre"><?php echo $tour->tour_id; ?></td>
					<td class="gp-centre"><?php echo $count; ?></td>
					<td class="gp-centre"><a href="<?php echo $edit; ?>" class="edit"><?php _e("Edit", "gigpress"); ?></a> | <a href="<?php echo $delete; ?>" class="delete"><?php _e("Delete", "gigpress"); ?></a>
					</td>
				</tr>
				
				<?php $num++; }
			} else {

			// We don't have any tours, so let's say so
			?>
			<tr><td colspan="5"><strong><?php _e("No tours in the database!", "gigpress"); ?></strong></td></tr>
	<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<th scope="col"><?php _e("Display order", "gigpress"); ?></th>
				<th scope="col"><?php _e("Tour name", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Tour ID", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Number of shows", "gigpress"); ?></th>
				<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
			</tr>
		</tfoot>
	</table>
			<p class="submit noindent"><input type="submit" name="submit" class="button-primary" value="<?php _e("Save order", "gigpress") ?>" /> <?php _e("or", "gigpress"); ?> <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gigpress-tours"><?php _e("cancel", "gigpress"); ?></a></p>
	</form>

	<?php } else { 
	// List all of our tours
	?>
	
	<h3><?php _e("All tours", "gigpress"); ?></h3>
	
	<p><?php _e("Note that deleting a tour will <strong>NOT</strong> delete the shows associated with that tour.", "gigpress"); ?></p>
	
	<?php
		$reorder = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-tours&amp;gpaction=reorder';
	?>
	
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e("Display order", "gigpress"); ?> <small>(<a href="<?php echo $reorder; ?>"><?php _e("edit", "gigpress"); ?></a>)</small></th>
				<th scope="col"><?php _e("Tour name", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Tour ID", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Number of shows", "gigpress"); ?></th>
				<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php
		// Get all upcoming tours from the DB
		$tours = $wpdb->get_results("SELECT * from ". $gigpress['tours_table'] ." WHERE tour_status = 'active' ORDER BY tour_order ASC");
		
		// Do we have tours?
		if($tours) {
				
			foreach($tours as $tour) {
			
				if($n = $wpdb->get_var("
					SELECT count(*) FROM ". $gigpress['gigs_table'] ." WHERE show_tour_id = ". $tour->tour_id ."
				")) {
					$count = $n;
				} else {
					$count = 0;
				}

				
				// Create our edit and delete links
				$edit = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-tours&amp;gpaction=edit&amp;tour_id='.$tour->tour_id;			
				$delete = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-tours&amp;gpaction=delete&amp;tour_id='.$tour->tour_id;
				$delete = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($delete, 'gigpress-action') : $delete;

				++ $i;
				$style = ($i % 2) ? '' : ' class="alternate"';
				// Print out our rows.
				?>
				<tr<?php echo $style; ?>>
					<td><?php echo $tour->tour_order; ?></td>
					<td><?php echo gigpress_sanitize($tour->tour_name); ?></td>
					<td class="gp-centre"><?php echo $tour->tour_id; ?></td>
					<td class="gp-centre"><?php echo $count; ?></td>
					<td class="gp-centre"><a href="<?php echo $edit; ?>" class="edit"><?php _e("Edit", "gigpress"); ?></a> | <a href="<?php echo $delete; ?>" class="delete"><?php _e("Delete", "gigpress"); ?></a></td>
				</tr>
				<?php }
		} else {

			// We don't have any tours, so let's say so
			?>
			<tr><td colspan="5"><strong><?php _e("No tours in the database!", "gigpress"); ?></strong></td></tr>
	<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<th scope="col"><?php _e("Display order", "gigpress"); ?> <small>(<a href="<?php echo $reorder; ?>"><?php _e("edit", "gigpress"); ?></a>)</small></th>
				<th scope="col"><?php _e("Tour name", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Tour ID", "gigpress"); ?></th>
				<th scope="col" class="gp-centre"><?php _e("Number of shows", "gigpress"); ?></th>
				<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
			</tr>
		</tfoot>
	</table>
	<?php } ?>
	
	</div>
<?php }