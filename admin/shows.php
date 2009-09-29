<?php

function gigpress_admin_shows() {
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "delete") {
		require_once('handlers.php');
		gigpress_delete_show();		
	}
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "undo") {
		require_once('handlers.php');
		gigpress_undo('show');		
	}
	
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "update") {
		require_once('handlers.php');
		gigpress_update_show();
	}
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "trash") {
		require_once('handlers.php');
		gigpress_empty_trash();		
	}	
	
	global $wpdb, $gpo;
	
	// Checks for filtering and pagination
	$url_args = '';
	$further_where = '';
	$pagination_args = array();
	
	switch($_GET['scope']) {
		case 'upcoming':
			$condition = ">= '" . GIGPRESS_NOW . "'";
			$url_args .= '&amp;scope=upcoming';
			$pagination_args['scope'] = 'upcoming';
			break;
		case 'past':
			$condition = "< '" . GIGPRESS_NOW . "'";
			$url_args .= '&amp;scope=past';
			$pagination_args['scope'] = 'past';
			break;
		default:
			$condition = 'IS NOT NULL';
	}

	switch($_GET['sort']) {
		case 'asc':
			$sort = 'ASC';
			$url_args .= '&amp;sort=asc';
			$pagination_args['sort'] = 'asc';
			break;
		case 'desc':
			$sort = 'DESC';
			$url_args .= '&amp;sort=desc';
			$pagination_args['sort'] = 'desc';
			break;
		default:
			$sort = 'DESC';
	}
	
	if(isset($_GET['gp-page'])) $url_args .= '&amp;gp-page=' . $_GET['gp-page'];
	
	if(isset($_GET['artist_id']) && $_GET['artist_id'] != '-1') {
		$further_where .= ' AND s.show_artist_id = ' . $wpdb->prepare('%d', $_GET['artist_id']) . ' ';
		$pagination_args['artist_id'] = $_GET['artist_id'];
		$url_args .= '&amp;artist_id=' . $_GET['artist_id'];
	}
	
	if(isset($_GET['tour_id']) && $_GET['tour_id'] != '-1') {
		$further_where .= ' AND s.show_tour_id = ' . $wpdb->prepare('%d', $_GET['tour_id']) . ' ';
		$pagination_args['tour_id'] = $_GET['tour_id'];		
		$url_args .= '&amp;tour_id=' . $_GET['tour_id'];
	}
	
	if(isset($_GET['venue_id']) && $_GET['venue_id'] != '-1') {
		$further_where .= ' AND s.show_venue_id = ' . $wpdb->prepare('%d', $_GET['venue_id']) . ' ';
		$pagination_args['venue_id'] = $_GET['venue_id'];		
		$url_args .= '&amp;venue_id=' . $_GET['venue_id'];
	}
	
	// Build Mr. Query	
	$shows = $wpdb->get_results("
		SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_expire " . $condition . " AND show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id " . $further_where . "ORDER BY show_date " . $sort . ",show_time " . $sort . "");

	if($shows) {
		$pagination_args['page'] = 'gigpress-shows';
		$pagination = gigpress_admin_pagination(count($shows), 10, $pagination_args);			
	}

	?>
		
	<div class="wrap gigpress">

		<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>		
		<h2><?php _e("Shows", "gigpress"); ?></h2>
		
		<ul class="subsubsub">
		<?php
			$all = $wpdb->get_var("SELECT count(*) FROM " . GIGPRESS_SHOWS . " WHERE show_status != 'deleted'");
			$upcoming = $wpdb->get_var("SELECT count(*) FROM " . GIGPRESS_SHOWS . " WHERE show_expire >= '" . GIGPRESS_NOW . "' AND show_status != 'deleted'");
			$past = $wpdb->get_var("SELECT count(*) FROM " . GIGPRESS_SHOWS . " WHERE show_expire < '" . GIGPRESS_NOW . "' AND show_status != 'deleted'");
			echo('<li><a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gigpress-shows"');
			if(!isset($_GET['scope'])) echo(' class="current"');
			echo('>' . __("All", "gigpress") . '</a> <span class="count">(' . $all	. ')</span> | </li>');
			echo('<li><a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gigpress-shows&amp;scope=upcoming"');
			if(isset($_GET['scope']) && $_GET['scope'] == 'upcoming') echo(' class="current"');
			echo('>' . __("Upcoming", "gigpress") . '</a> <span class="count">(' . $upcoming	. ')</span> | </li>');
			echo('<li><a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gigpress-shows&amp;scope=past"');
			if(isset($_GET['scope']) && $_GET['scope'] == 'past') echo(' class="current"');
			echo('>' . __("Past", "gigpress") . '</a> <span class="count">(' . $past	. ')</span></li>');
		?>
		</ul>
		
		<div class="tablenav">
			<div class="alignleft">
				<form action="" method="get">
					<div>
						<input type="hidden" name="page" value="gigpress-shows" />
						<?php if(isset($_GET['scope'])) : ?>
						<input type="hidden" name="scope" value="<?php echo $_GET['scope']; ?>" />
						<?php endif; ?>
						<select name="artist_id">
							<option value="-1"><?php _e("View all artists", "gigpress"); ?></option>
						<?php $artistdata = $wpdb->get_results("SELECT artist_id, artist_name from ". GIGPRESS_ARTISTS ." ORDER BY artist_name ASC");
						if($artistdata) {
							foreach($artistdata as $artist) {
								$selected = (isset($_GET['artist_id']) && $_GET['artist_id'] == $artist->artist_id) ? ' selected="selected"' : '';
								echo('<option value="' . $artist->artist_id . '"' . $selected . '>' . gigpress_db_out($artist->artist_name) . '</option>');
							}
						} else {
							echo('<option value="-1">' . __("No artists in the database", "gigpress") . '</option>');
						}
						?>
						</select>
						
						<select name="tour_id">
							<option value="-1"><?php _e("View all tours", "gigpress"); ?></option>
						<?php $tourdata = $wpdb->get_results("SELECT tour_id, tour_name from ". GIGPRESS_TOURS ." WHERE tour_status = 'active' ORDER BY tour_name ASC");
						if($tourdata) {
							foreach($tourdata as $tour) {
								$selected = (isset($_GET['tour_id']) && $_GET['tour_id'] == $tour->tour_id) ? ' selected="selected"' : '';
								echo('<option value="' . $tour->tour_id . '"' . $selected . '>' . gigpress_db_out($tour->tour_name) . '</option>');
							}
						} else {
							echo('<option value="-1">' . __("No tours in the database", "gigpress") . '</option>');
						}
						?>
						</select>

						<select name="venue_id">
							<option value="-1"><?php _e("View all venues", "gigpress"); ?></option>
						<?php $venuedata = $wpdb->get_results("SELECT venue_id, venue_name from ". GIGPRESS_VENUES ." ORDER BY venue_name ASC");
						if($venuedata) {
							foreach($venuedata as $venue) {
								$selected = (isset($_GET['venue_id']) && $_GET['venue_id'] == $venue->venue_id) ? ' selected="selected"' : '';
								echo('<option value="' . $venue->venue_id . '"' . $selected . '>' . gigpress_db_out($venue->venue_name) . '</option>');
							}
						} else {
							echo('<option value="-1">' . __("No venues in the database", "gigpress") . '</option>');
						}
						?>
						</select>
								
						<select name="sort">
							<option value="desc"<?php if(isset($_GET['sort']) && $_GET['sort'] == 'desc') echo(' selected="selected"'); ?>><?php _e("Descending", "gigpress"); ?></option>
							<option value="asc"<?php if(isset($_GET['sort']) && $_GET['sort'] == 'asc') echo(' selected="selected"'); ?>><?php _e("Ascending", "gigpress"); ?></option>
						</select>
						<input type="submit" value="Filter" class="button-secondary" />
					</div>
				</form>
			</div>
			<?php if($pagination)
			{
				$shows = array_slice($shows, $pagination['offset'], $pagination['records_per_page']);
				echo $pagination['output'];
			}	
			?>
			<div class="clear"></div>
		</div>

		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e("Date", "gigpress"); ?></th>
					<th scope="col"><?php _e("Artist", "gigpress"); ?></th>
					<th scope="col"><?php _e("City", "gigpress"); ?></th>
					<th scope="col"><?php _e("Venue", "gigpress"); ?></th>
					<th scope="col"><?php _e("Country", "gigpress"); ?></th>
					<th scope="col"><?php _e("Tour", "gigpress") ?></th>					
					<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
				</tr>
			</thead>
			<tbody>
		<?php
		
		// Do we have dates?
		if($shows != FALSE) {
		
			foreach($shows as $show) {
		
				$showdata = gigpress_prepare($show, 'admin');

				?>
				<tr class="<?php echo 'gigpress-' . $showdata['status']; ?>">
					<td><span class="gigpress-date"><?php echo $showdata['date']; if($showdata['end_date']) { echo(' - ') . $showdata['end_date']; } ?></span>
					</td>
					<td><?php echo $showdata['artist']; ?></td>
					<td><?php echo $showdata['city']; ?></td>
					<td><?php echo $showdata['venue']; if($showdata['address']) echo(' (' . $showdata['address'] . ')'); ?></td>
					<td><?php echo $showdata['country']; ?></td>
					<td><?php echo $showdata['tour']; ?></td>
					<td class="gp-centre">
						<a href="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=edit&amp;show_id=<?php echo $show->show_id; ?>" class="edit" title="<?php _e("Edit", "gigpress"); ?>"><?php _e("Edit", "gigpress"); ?></a> | 
						<a href="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=copy&amp;show_id=<?php echo $show->show_id; ?>" class="edit" title="<?php _e("Copy", "gigpress"); ?>"><?php _e("Copy", "gigpress"); ?></a> | 
						<a href="<?php echo wp_nonce_url(get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-shows&amp;gpaction=delete&amp;show_id=' . $show->show_id . $url_args, 'gigpress-action'); ?>" class="delete" title="<?php _e("Delete", "gigpress"); ?>"><?php _e("Delete", "gigpress"); ?></a>
					</td>
				</tr>
				<tr class="<?php echo 'alternate' . ' gigpress-' . $showdata['status']; ?>">
					<td colspan="7"><small>
					<?php
						if($showdata['time']) echo $showdata['time'] . '. ';
						if($showdata['price']) echo __("Price", "gigpress") . ': ' . $showdata['price'] . '. ';
						if($showdata['admittance']) echo $showdata['admittance'] . '. ';
						if($showdata['ticket_link']) echo $showdata['ticket_link'] . '. ';
						if($showdata['ticket_phone']) echo __('Box office', "gigpress") . ': ' . $showdata['ticket_phone'] . '. ';
						echo $showdata['notes'] . ' ';
						echo $showdata['related_edit'];
					?>
					</small></td>
				</tr>	
			<?php } // end foreach			
		} else { // No results from the query
		?>
			<tr><td colspan="7"><?php _e("Sorry, no shows to display based on your criteria.", "gigpress"); ?></td></tr>
		<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="col"><?php _e("Date", "gigpress"); ?></th>
					<th scope="col"><?php _e("Artist", "gigpress"); ?></th>
					<th scope="col"><?php _e("City", "gigpress"); ?></th>
					<th scope="col"><?php _e("Venue", "gigpress"); ?></th>
					<th scope="col"><?php _e("Country", "gigpress"); ?></th>
					<th scope="col"><?php _e("Tour", "gigpress") ?></th>					
					<th class="gp-centre" scope="col"><?php _e("Actions", "gigpress"); ?></th>
				</tr>
			</tfoot>
		</table>
		<div class="tablenav">
		<?php
			if($tour_count = $wpdb->get_var("SELECT count(*) FROM ". GIGPRESS_TOURS ." WHERE tour_status = 'deleted'")) {
				$tours = $tour_count;
			} else {
				$tours = 0;
			}
			
			if($show_count = $wpdb->get_var("SELECT count(*) FROM ". GIGPRESS_SHOWS ." WHERE show_status = 'deleted'")) {
				$shows = $show_count;
			} else {
				$shows = 0;
			}
			if($tour_count || $show_count) {					
				echo('<div class="alignleft"><small>'. __("You have", "gigpress"). ' <strong>'. $shows .' '. __("shows", "gigpress"). '</strong> '. __("and", "gigpress"). ' <strong>'. $tours .' '. __("tours", "gigpress") .'</strong> '. __("in your trash", "gigpress").'.');
				if($shows != 0 || $tours != 0) {
					echo(' <a href="'. wp_nonce_url(get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-shows&amp;gpaction=trash' . $url_args, 'gigpress-action') .'">'. __("Take out the trash now", "gigpress") .'</a>.');
				}
				echo('</small></div>');
			}
			?>
	
			<?php if($pagination) echo $pagination['output']; ?>

		</div>
	</div>
<?php } ?>