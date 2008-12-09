<?php

// HANDLER: ADD A SHOW
// ===================


function gigpress_add_show() {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	
	$wpdb->show_errors();

	// Check the nonce
	check_admin_referer('gigpress-action');
	
	// If our required fields are empty, stop the presses (no pun intended)
	// and say why
	if(empty($_POST['show_locale'])) $errors['locale'] = __("You must enter a city.", "gigpress");
	if(empty($_POST['show_venue'])) $errors['venue'] = __("You must enter a venue (perhaps it's TBA?)", "gigpress");
	
	if($errors) {
		echo('<div id="message" class="error fade">');
		if($errors['locale']) echo("<p>".$errors['locale']."</p>");
		if($errors['venue']) echo("<p>".$errors['venue']."</p>");
		echo("</div>");
		
	} else {
	
		// Looks like we're all here, so let's add to the DB
		$date = $_POST['gp_yy'] . '-' . $_POST['gp_mm'] . '-' . $_POST['gp_dd'];
		if($_POST['gp_hh'] == "na" || $_POST['gp_min'] == "na") {
			$time = "00:00:01";
		} else {	
			$time = $_POST['gp_hh'] . ':' . $_POST['gp_min'] . ':00';
		}
		// If it's not a multi-day show, we need to set the expire date to match the show date
		// We also have to explicitly set the value of 'show_multi' to 0 or MySQL 5 strict will throw a fit
		if($_POST['multi'] != 1) {
			$expire = $date;
			$multi = 0;
		} else {
			$expire = $_POST['exp_yy'] . '-' . $_POST['exp_mm'] . '-' . $_POST['exp_dd'];
			$multi = 1;
		}
		
		if ($_POST['show_related'] == "new") {
			// Prepare a new WP post
			$category = array($gpo['related_category']);
			global $user_ID;

			$related_post = array();
				$related_post['post_title'] = $_POST['show_locale'] . ", " . mysql2date($gpo['date_format'], $date);
				$related_post['post_name'] = sanitize_title($_POST['show_locale']) . "-" . $date;
				$related_post['post_author'] = $user_ID;
				$related_post['post_category'] = $category;
				$related_post['post_status'] = "publish";

			$insert = wp_insert_post($related_post);

			if ( $insert == 0 ) {
				$related = 0;
				$errors['newpost'] = __("We had trouble creating your Related Post. Sorry.", "gigpress");
			} else {
				$related = $insert;
			}
			
		} else {
			$related = $_POST['show_related'];
		}
		
		$tourid = $_POST['show_tour_id'];
		$country = $_POST['show_country'];
		
		$addshow = $wpdb->query("
			INSERT INTO ". $gigpress['gigs_table'] ." (show_date, show_time, show_expire, show_multi, show_address, show_locale, show_country, show_price, show_tix_url, show_tix_phone, show_venue, show_venue_url, show_venue_phone, show_ages, show_notes, show_tour_id, show_related)
			VALUES ('". $date ."', '". $time ."', '". $expire ."', '". $multi ."','". $_POST['show_address'] ."', '". $_POST['show_locale'] ."', '". $country ."', '". $_POST['show_price'] ."', '". $_POST['show_tix_url'] ."', '". $_POST['show_tix_phone'] . "', '" . $_POST['show_venue'] ."', '". $_POST['show_venue_url'] ."', '". $_POST['show_venue_phone'] . "', '" . $_POST['show_ages'] ."', '". $_POST['show_notes'] ."', '". $tourid ."', '". $related ."')
		");
		// Sticky stuff for the next entry
		$gpo['default_date'] = $date;
		$gpo['default_time'] = $time;
		$gpo['default_tour'] = $tourid;
		$gpo['default_country'] = $country;
		update_option('gigpress_settings', $gpo);
		
		// Was the query successful?
		if($addshow != FALSE) { ?>
		
			<div id="message" class="updated fade"><p><?php echo __("Your show  in", "gigpress") . ' ' . gigpress_sanitize($_POST['show_locale']) . ' ' . __("on", "gigpress") . ' ' . mysql2date($gpo['date_format_long'], $date) . ' ' . __("was successfully added.", "gigpress"); ?></p>
			<?php if($errors['newpost']) {
				echo('<p><strong>' . $errors['newpost'] . '</strong></p>');
			} ?>
			</div>
			
	<?php } elseif($addshow === FALSE) { ?>
	
			<div id="message" class="error fade"><p><?php _e("Something ain't right - try again?", "gigpress"); ?></p></div>			
	
	<?php }
	}
}


// HANDLER: EDIT A SHOW
// ====================


function gigpress_update_show() {

	// We use this to update a show that's already in the DB
	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');
			
	// If our required fields are empty, stop the presses (no pun intended)
	// (not funny the 2nd time) (or the first time) and say why
	if(empty($_POST['show_locale'])) $errors['locale'] = __("You must enter a city.", "gigpress");
	if(empty($_POST['show_venue'])) $errors['venue'] = __("You must enter a venue (perhaps it's TBA?)", "gigpress");
	
	if($errors) {
		echo('<div id="message" class="error fade">');
		if($errors['locale']) echo("<p>".$errors['locale']."</p>");
		if($errors['venue']) echo("<p>".$errors['venue']."</p>");
		echo("</div>");
		
	} else {
	
		// Looks like we're all here, so let's update the DB
		$date = $_POST['gp_yy'] . '-' . $_POST['gp_mm'] . '-' . $_POST['gp_dd'];
		if($_POST['gp_hh'] == "na" || $_POST['gp_min'] == "na") {
			$time = "00:00:01";
		} else {	
			$time = $_POST['gp_hh'] . ':' . $_POST['gp_min'] . ':00';
		}		
		// If it's not a multi-day show, we need to set the expire date to match the show date
		// We also have to explicitly set the value of 'show_multi' to 0 or MySQL 5 will throw a fit
		if($_POST['multi'] != 1) {
			$expire = $date;
			$multi = 0;
		} else {
			$expire = $_POST['exp_yy'] . '-' . $_POST['exp_mm'] . '-' . $_POST['exp_dd'];
			$multi = 1;
		}
		
		if ($_POST['show_related'] == "new") {
			// Prepare a new WP post
			$category = array($gpo['related_category']);
			global $user_ID;

			$related_post = array();
				$related_post['post_title'] = $_POST['show_locale'] . ", " . mysql2date($gpo['date_format'], $date);
				$related_post['post_name'] = sanitize_title($_POST['show_locale']) . "-" . $date;
				$related_post['post_author'] = $user_ID;
				$related_post['post_category'] = $category;
				$related_post['post_status'] = "publish";

			$insert = wp_insert_post($related_post);

			if ( $insert == 0 ) {
				$related = 0;
				$errors['newpost'] = __("We had trouble creating your Related Post. Sorry.", "gigpress");
			} else {
				$related = $insert;
			}
			
		} else {
			$related = $_POST['show_related'];
		}
		
		$updateshow = $wpdb->query("
			UPDATE ". $gigpress['gigs_table'] ." SET show_date = '". $date ."',show_time = '". $time ."',show_expire = '". $expire ."',show_multi = '". $multi ."',show_address = '". $_POST['show_address'] ."',show_locale = '". $_POST['show_locale'] ."',show_country = '". $_POST['show_country'] ."',show_price = '". $_POST['show_price'] ."',show_tix_url = '". $_POST['show_tix_url'] ."',show_tix_phone = '". $_POST['show_tix_phone'] . "',show_venue = '". $_POST['show_venue'] ."',show_venue_url = '". $_POST['show_venue_url'] ."',show_venue_phone = '". $_POST['show_venue_phone'] ."',show_ages = '". $_POST['show_ages'] ."',show_notes = '". $_POST['show_notes'] ."',show_tour_id = '". $_POST['show_tour_id'] ."',show_related = '". $related ."',show_status = '". $_POST['show_status'] ."' WHERE show_id = ". $_POST['show_id']."
		");
		
		// Was the query successful?
		if($updateshow != FALSE) { ?>			
			<div id="message" class="updated fade"><p><?php echo __("Your show  in", "gigpress") . ' ' . gigpress_sanitize($_POST['show_locale']) . ' ' . __("on", "gigpress") . ' ' . mysql2date($gpo['date_format_long'], $date) . ' ' . __("was successfully updated.", "gigpress"); ?></p>
			<?php if($errors['newpost']) {
				echo('<p><strong>' . $errors['newpost'] . '</strong></p>');
			} ?>
			</div>
		<?php } elseif($updateshow === FALSE) { ?>
			<div id="message" class="error fade"><p><?php _e("Something ain't right - try again?", "gigpress"); ?></p></div>
	<?php }
	}
}


// HANDLER: DELETE A SHOW
// ======================


function gigpress_delete_show() {

	// We use this to delete shows
	global $wpdb;
	global $gigpress;
	
	$wpdb->show_errors();
		
	$undo = get_bloginfo('wpurl').'/wp-admin/admin.php?page='.$_GET['gpreturn'].'&amp;gpaction=undo&amp;show_id='.$_GET['show_id'];
	$undo = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($undo, 'gigpress-action') : $undo;
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');	
	
	// Delete the show
	$trashshow = $wpdb->query("
		UPDATE ". $gigpress['gigs_table'] ." SET show_status = 'deleted' WHERE show_id = ". $_GET['show_id'] ." LIMIT 1
		");
	if($trashshow != FALSE) { ?>
			
		<div id="message" class="updated fade"><p><?php _e("Show successfully deleted.", "gigpress"); ?> <small>(<a href="<?php echo $undo; ?>"><?php _e("Undo", "gigpress"); ?></a>)</small></p></div>
		
	<?php } elseif($trashshow === FALSE) { ?>
		
		<div id="message" class="error fade"><p><?php _e("We ran into some trouble deleting the show. Sorry.", "gigpress"); ?></p></div>				
	<?php }
}

// HANDLER: ADD A TOUR
// ===================


function gigpress_add_tour() {

	global $wpdb;
	global $gigpress;
	global $now;	
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');
	
	// If our required fields are empty, stop the presses (no pun intended) 
	// (good god stop it) and say why
	if(empty($_POST['tour_name']))  {
		echo('<div id="message" class="error fade"><p>');
		_e("You need to enter a name for the tour. It's the only field for crying out loud!", "gigpress");
		echo('</p></div>');
	} else {
	
		// Looks like we're all here, so let's add to the DB
		$addtour = $wpdb->query("
			INSERT into ". $gigpress['tours_table'] ." (tour_order, tour_name)
			VALUES ('". $_POST['tour_order'] ."', '". $_POST['tour_name'] ."')
		");
		
		// Was the query successful?
		if($addtour != FALSE) { ?>
			<div id="message" class="updated fade"><p><?php echo gigpress_sanitize($_POST['tour_name']) .' '. __("was successfully added to the database.", "gigpress"); ?></p></div>
	<?php } elseif($addtour === FALSE) { ?>
			<div id="message" class="error fade"><p><?php _e("Something ain't right - try again?", "gigpress"); ?></p></div>
	<?php }
	}
}


// HANDLER: UPDATE A TOUR
// ======================


function gigpress_update_tour() {

	// We use this to update a tour that's already in the DB
	global $wpdb;
	global $gigpress;
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');
			
	// If our required field is empty, chastize
	if(empty($_POST['tour_name']))  {
		echo('<div id="message" class="error fade"><p>');
		_e("You need to enter a name for the tour!", "gigpress");
		echo('</p></div>');
	} else {
			
		// Looks like we're all here, so let's update the DB
		$updatetour = $wpdb->query("
			UPDATE ". $gigpress['tours_table'] ." SET tour_name = '". $_POST['tour_name'] ."' WHERE tour_id = ". $_POST['tour_id']."
		");
		
		// Was the query successful?
		if($updatetour != FALSE) { ?>
			<div id="message" class="updated fade"><p><?php _e("Tour name successfully changed to", "gigpress"); echo ': ' . gigpress_sanitize($_POST['tour_name']); ?></p></div>
	<?php } elseif($updatetour === FALSE) { ?>
			<div id="message" class="error fade"><p><?php _e("Something ain't right - try again?", "gigpress"); ?></p></div>
	<?php }
	}
}


// HANDLER: DELETE A TOUR
// ======================


function gigpress_delete_tour() {

	// We use this to delete tours
	global $wpdb;
	global $gigpress;
	
	$undo = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-tours&amp;gpaction=undo&amp;tour_id='.$_GET['tour_id'];
	$undo = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($undo, 'gigpress-action') : $undo;
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');	
	
	// Delete the tour
	$trashtour = $wpdb->query("
		UPDATE ". $gigpress['tours_table'] ." SET tour_status = 'deleted' WHERE tour_id = ". $_GET['tour_id'] ." LIMIT 1
		");
	if($trashtour != FALSE) {
	
		// Find any shows associated with that tour, log them in the temp DB, then remove their foreign key
		$cleanup = $wpdb->query("
		UPDATE ". $gigpress['gigs_table'] ." SET show_tour_restore = 0;
		");

		$restore = $wpdb->query("
		UPDATE ". $gigpress['gigs_table'] ." SET show_tour_id = 0, show_tour_restore = 1 WHERE show_tour_id = ". $_GET['tour_id'] ."
		"); ?>
		
		<div id="message" class="updated fade"><p><?php _e("Tour successfully deleted.", "gigpress"); ?> <small>(<a href="<?php echo $undo; ?>"><?php _e("Undo", "gigpress"); ?></a>)</small></p></div>
		
	<?php } elseif($trashtour === FALSE) { ?>
		
		<div id="message" class="error fade"><p><?php _e("We ran into some trouble deleting the tour. Sorry.", "gigpress"); ?></p></div>				
	<?php }
}

// HANDLER: UNDO DELETING SOMETHING
// ======================


function gigpress_undo($type) {

	global $wpdb;
	global $gigpress;
		
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');	
	
	if($type == "show") {
		
		$undo = $wpdb->query("
			UPDATE ". $gigpress['gigs_table'] ." SET show_status = 'active' WHERE show_id = ". $_GET['show_id']." LIMIT 1
			");
		if($undo != FALSE) { ?>
			<div id="message" class="updated fade"><p><?php _e("Show successfully restored.", "gigpress"); ?></p></div>
		<?php } elseif($undo === FALSE) { ?>
			<div id="message" class="error fade"><p><?php _e("We ran into some trouble restoring that show. Sorry.", "gigpress"); ?></p></div>				
		<?php }
	}
	
	if($type == "tour") {
		
		// Restore the tour
		$undo = $wpdb->query("
			UPDATE ". $gigpress['tours_table'] ." SET tour_status = 'active' WHERE tour_id = ". $_GET['tour_id'] ." LIMIT 1
			");
		
		// Update the shows that need it to associate with this tour	
		$restore = $wpdb->query("
			UPDATE ". $gigpress['gigs_table'] ." SET show_tour_id = ". $_GET['tour_id'] .", show_tour_restore = 0 WHERE show_tour_restore = 1
		");
		
		if($undo != FALSE) { ?>
			<div id="message" class="updated fade"><p><?php _e("Tour successfully restored from the database.", "gigpress"); ?></p></div>
		<?php } elseif($undo === FALSE) { ?>
			<div id="message" class="error fade"><p><?php _e("We ran into some trouble restoring the tour. Sorry.", "gigpress"); ?></p></div>
		<?php }
	}
}

// HANDLER: REORDER TOURS
// ======================


function gigpress_setorder() {

	// We use this to reorder our tours
	global $wpdb;
	global $gigpress;
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');
			
	// Looks like we're all here, so let's update the DB
	$tours = count($_POST['id']);
	$i = 0;
	
	while($i < $tours) {

		$id = $_POST['id'][$i];
		$order = $_POST['order'][$i];
		
		$setorder = $wpdb->query("
		UPDATE ". $gigpress['tours_table'] ." SET tour_order = ". $order ." WHERE tour_id = ". $id ."
		");

		$i++;
	}
	
	// Was the query successful?
	if($setorder != FALSE) {
	?>
		<div id="message" class="updated fade"><p><?php _e("Tours successfully reordered.", "gigpress"); ?></p></div>
	<?php } elseif($setorder === FALSE) { ?>
		<div id="message" class="error fade"><p><?php _e("Something ain't right - try again?", "gigpress"); ?></p></div>
	<?php }
	
}


// HANDLER: EMPTY TRASH
// ======================


function gigpress_empty_trash() {

	global $wpdb;
	global $gigpress;
	
	$wpdb->show_errors();
	
	// Check the nonce
	check_admin_referer('gigpress-action');

	// Delete trashy shows
	$trashshows = $wpdb->query("
		DELETE FROM ". $gigpress['gigs_table'] ." WHERE show_status = 'deleted';
		");
	if($trashshows != FALSE) { ?>
		<div id="message" class="updated fade"><p><?php _e("All shows in the trash have been permanently deleted.", "gigpress"); ?></p></div>
	<?php } elseif($trashshows === FALSE) { ?>
		<div id="message" class="error fade"><p><?php _e("We ran into some trouble deleting shows from the trash. Sorry.", "gigpress"); ?></p></div>				
	<?php }
			
	// Delete trashy tours
	$trashtour = $wpdb->query("
		DELETE FROM ". $gigpress['tours_table'] ." WHERE tour_status = 'deleted';
		");
	if($trashtour != FALSE) { ?>
		<div id="message" class="updated fade"><p><?php _e("All tours in the trash have been permanently deleted.", "gigpress"); ?></p></div>
	<?php } elseif($trashtour === FALSE) { ?>
		<div id="message" class="error fade"><p><?php _e("We ran into some trouble deleting tours from the trash. Sorry.", "gigpress"); ?></p></div>				
	<?php }
	
}