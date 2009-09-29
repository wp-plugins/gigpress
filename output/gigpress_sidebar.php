<?php

function gigpress_widget_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;
		
	function gigpress_widget($args) {
		
		extract($args);
		global $gpo;
			
		$title = wptexturize($gpo['widget_heading']);
		$limit = $gpo['widget_number'];
		$show_tours = $gpo['widget_segment'];
		$group_by_artist = $gpo['widget_group_by_artist'];
	
		echo $before_widget . $before_title . $title . $after_title;
		
		// Call our sidebar function
		gigpress_sidebar($limit, $show_tours, $group_by_artist);
		
		echo $after_widget;
	}
	
	function gigpress_widget_control() {
	
		global $gpo;
		
		if ( $_POST['gigpress_widget_submit'] ) {
			$gpo['widget_heading'] = gigpress_db_in($_POST['gigpress_widget_heading']);
			$gpo['widget_number'] = gigpress_db_in($_POST['gigpress_widget_number']);
			$gpo['widget_segment'] = gigpress_db_in($_POST['gigpress_widget_segment']);
			$gpo['widget_group_by_artist'] = gigpress_db_in($_POST['gigpress_widget_group_by_artist']);
			$gpo['widget_feeds'] = gigpress_db_in($_POST['gigpress_widget_feeds']);
			$gpo['sidebar_link'] = gigpress_db_in($_POST['gigpress_widget_link']);
			$gpo['upcoming_phrase'] = gigpress_db_in($_POST['gigpress_widget_upcoming_phrase']);
			update_option('gigpress_settings', $gpo);
		}
				
		echo '<p><label for="gigpress_widget_heading">' . __('Title') . ': <input class="widefat" id="gigpress_widget_heading" name="gigpress_widget_heading" type="text" value="' . $gpo['widget_heading'] . '" /></label></p>';

		echo '<p><label for="gigpress_widget_number">' . __('Number of shows to list', 'gigpress') . ': <input style="width: 25px; text-align: center;" id="gigpress_widget_number" name="gigpress_widget_number" type="text" value="' . $gpo['widget_number'] . '" /></label></p>';

		echo '<p><label><input id="gigpress_widget_group_by_artist" name="gigpress_widget_group_by_artist" type="checkbox" value="1"';
		if ( $gpo['widget_group_by_artist'] == 1 ) echo ' checked="checked"';
		echo ' />  ' . __('Group by artist', 'gigpress') . '</label><br />';
		
		echo '<label><input id="gigpress_widget_segment" name="gigpress_widget_segment" type="checkbox" value="1"';
		if ( $gpo['widget_segment'] == 1 ) echo ' checked="checked"';
		echo ' />  ' . __('Group by tour', 'gigpress') . '</label><br />';

		echo '<label><input id="gigpress_widget_feeds" name="gigpress_widget_feeds" type="checkbox" value="1"';
		if ( $gpo['widget_feeds'] == 1 ) echo ' checked="checked"';
		echo ' />  ' . __('Show RSS and iCal feeds', 'gigpress') . '</label><br />';
		
		echo '<label><input id="gigpress_widget_link" name="gigpress_widget_link" type="checkbox" value="1"';
		if ( $gpo['sidebar_link'] == 1 ) echo ' checked="checked"';
		echo ' />  ' . __('Show link to shows page', 'gigpress') . '</label></p>';

		echo '<p><label for="gigpress_widget_upcoming_phrase">' . __('Shows page link text') . ': <input class="widefat" id="gigpress_widget_upcoming_phrase" name="gigpress_widget_upcoming_phrase" type="text" value="' . $gpo['upcoming_phrase'] . '" /></label></p>';
		
		echo '<input type="hidden" id="gigpress_widget_submit" name="gigpress_widget_submit" value="1" />';		
	
	}
	
	register_sidebar_widget(array('GigPress', 'widgets'), 'gigpress_widget');
	register_widget_control(array('GigPress', 'widgets'), 'gigpress_widget_control', 250, 300);
}


function gigpress_sidebar($limit = 5, $show_tours = 1, $group_artists = 0, $artist = NULL, $tour = NULL) {

	global $wpdb, $gpo;
	$total_artists = $wpdb->get_var("SELECT count(*) from " . GIGPRESS_ARTISTS);
	$further_where = $orderby = '';

	// Establish the variable parts of the query
	if($artist) $further_where .= ' AND show_artist_id = ' . $wpdb->prepare('%d', $artist);
	if($tour) $further_where .= ' AND show_tour_id = ' . $wpdb->prepare('%d', $tour);
	if($group_artists == 1) $orderby = 'a.artist_name ASC,';
		
	// If we're grouping by artist, we'll unfortunately have to first get all artists
	// Then  make a query for each one. Looking for a better way to do this.
	
	if($group_artists == 1 && !$tour && !$artist && $total_artists > 1) { 
		
		$artists = $wpdb->get_results("SELECT * FROM " . GIGPRESS_ARTISTS . " ORDER BY artist_name");
		
		foreach($artists as $artist_group) {
			$shows = $wpdb->get_results("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_expire >= '" . GIGPRESS_NOW . "' AND show_status != 'deleted' AND s.show_artist_id = " . $artist_group->artist_id . " AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id ORDER BY s.show_date ASC,s.show_time ASC LIMIT " . $limit);
			
			if($shows) {
				// For each artist group
				
				$some_results = TRUE;
				$current_tour = '';
				$i = 0;
				$showdata = array(
					'artist' => wptexturize($artist_group->artist_name),
					'artist_id' => $artist_group->artist_id
				);
			
				include gigpress_template('sidebar-artist-heading');
				include gigpress_template('sidebar-list-start');
											
				foreach($shows as $show) {
				
					// For each individual show
					
					$showdata = gigpress_prepare($show, 'public');
					
					// Close the previous tour if needed
					if($show_tours && $current_tour && $showdata['tour'] != $current_tour) {
						include gigpress_template('sidebar-tour-end');					
					}
					
					// Open the current tour if needed
					if($show_tours && $showdata['tour'] && $showdata['tour'] != $current_tour && !$tour) {
						$current_tour = $showdata['tour'];
						include gigpress_template('sidebar-tour-heading');
					}
					
					// Prepare the class
					$class = ($i % 2) ? 'gigpress-alt ' : ''; $i++;
					$class .= ($showdata['tour'] && $show_tours) ? 'gipress-tour ' . $showdata['status'] : $showdata['status'];
					
					// Display the show
					include gigpress_template('sidebar-list');
				
				}
				
				// Close the current tour if needed
				if($show_tours && $current_tour) {
					include gigpress_template('sidebar-tour-end');					
				}
				
				// Close the list
				include gigpress_template('sidebar-list-end');
				
			}
		}
		
		if($some_results) {
		
		// After all artist groups
			
			// Display the list footer
			include gigpress_template('sidebar-list-footer');	

		} else {
			// No shows from any artist
			include gigpress_template('sidebar-list-empty');
		}	
			
	} else {

		// Not grouping by artists

		$shows = $wpdb->get_results("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_expire >= '" . GIGPRESS_NOW . "' AND show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id ORDER BY s.show_date ASC,s.show_time ASC LIMIT " . $limit);
			
		if($shows) {
			
			$current_tour = '';
			$i = 0;
			
			include gigpress_template('sidebar-list-start');
										
			foreach($shows as $show) {
			
				// For each individual show
				
				$showdata = gigpress_prepare($show, 'public');
				
				// Close the previous tour if needed
				if($show_tours && $current_tour && $showdata['tour'] != $current_tour && !$tour) {
					include gigpress_template('sidebar-tour-end');						
				}
				
				// Open the current tour if needed
				if($show_tours && $showdata['tour'] && $showdata['tour'] != $current_tour && !$tour) {
					$current_tour = $showdata['tour'];
					include gigpress_template('sidebar-tour-heading');
				}
				
				if(!$showdata['tour']) $current_tour = '';
				
				// Prepare the class
				$class = ($i % 2) ? 'gigpress-alt ' : ''; $i++;
				$class .= ($showdata['tour'] && $show_tours) ? 'gipress-tour ' . $showdata['status'] : $showdata['status'];
				
				// Display the show
				include gigpress_template('sidebar-list');
			}
			
			// Close the current tour if needed
			if($show_tours && $current_tour && !$tour) {
				include gigpress_template('sidebar-tour-end');						
			}
			
			// Close the list
			include gigpress_template('sidebar-list-end');
			
			// Display the list footer
			include gigpress_template('sidebar-list-footer');
												
		} else {
			// No shows from any artist
			include gigpress_template('sidebar-list-empty');
		}	
	}

	echo('<!-- Generated by GigPress ' . GIGPRESS_VERSION . ' -->
	');
}