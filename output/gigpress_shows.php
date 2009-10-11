<?php

// These two functions are for backwards-compatibility with GigPress < 2.0
function gigpress_upcoming($filter = null, $content = null) {
	if(!is_array($filter)) $filter = array();
	$filter['scope'] = 'upcoming';
	return gigpress_shows($filter, $content);
}
function gigpress_archive($filter = null, $content = null) {
	if(!is_array($filter)) $filter = array();
	$filter['scope'] = 'past';
	return gigpress_shows($filter, $content);
}


function gigpress_shows($filter = null, $content = null) {
	
	global $wpdb, $gpo;
	$further_where = $limit = $orderby = '';
	
	extract(shortcode_atts(array(
		'tour' => FALSE,
		'artist' => FALSE,
		'venue' => FALSE,
		'limit' => FALSE,
		'scope' => 'upcoming',
		'group_artists' => 'yes',
		'artist_order' => 'custom'
	), $filter));
	
	$total_artists = $wpdb->get_var("SELECT count(*) from " . GIGPRESS_ARTISTS);
	
	// Establish the variable parts of the query
	switch($scope) {
		case 'upcoming':
			$condition = ">= '" . GIGPRESS_NOW . "'";
			$sort = 'asc';
			break;
		case 'past':
			$condition = "< '" . GIGPRESS_NOW . "'";
			$sort = 'desc';
			break;
	}	
	if($artist) $further_where .= ' AND show_artist_id = ' . $wpdb->prepare('%d', $artist);
	if($tour) $further_where .= ' AND show_tour_id = ' . $wpdb->prepare('%d', $tour);
	if($venue) $further_where .= ' AND show_venue_id = ' . $wpdb->prepare('%d', $venue);
	if($limit) $limit = ' LIMIT ' . $limit;
	$artist_order = ($artist_order == 'custom') ?  "artist_order ASC," : '';
	
	$no_results_message = ($scope == 'upcoming') ? wptexturize($gpo['noupcoming']) : wptexturize($gpo['nopast']);
	
	ob_start();
	
	// If we're grouping by artist, we'll unfortunately have to first get all artists
	// Then  make a query for each one. Looking for a better way to do this.
	
	if($group_artists == 'yes' && !$tour && !$artist && $total_artists > 1) { 
		
		$artists = $wpdb->get_results("SELECT * FROM " . GIGPRESS_ARTISTS . " ORDER BY " . $artist_order . "artist_name ASC");
		
		foreach($artists as $artist_group) {
			$shows = $wpdb->get_results("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_expire " . $condition . " AND show_status != 'deleted' AND s.show_artist_id = " . $artist_group->artist_id . " AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id ORDER BY s.show_date " . $sort . ",s.show_time " . $sort . $limit);
			
			if($shows) {
				// For each artist group
				
				$some_results = TRUE;
				$current_tour = '';
				$i = 0;
				$showdata = array(
					'artist' => wptexturize($artist_group->artist_name),
					'artist_id' => $artist_group->artist_id
				);
			
				include gigpress_template('shows-artist-heading');
				include gigpress_template('shows-list-start');
											
				foreach($shows as $show) {
				
					// For each individual show
					
					$showdata = gigpress_prepare($show, 'public');
					
					if($showdata['tour'] && $showdata['tour'] != $current_tour && !$tour) {
						$current_tour = $showdata['tour'];
						include gigpress_template('shows-tour-heading');
					}
					
					$class = $showdata['status'];
					++ $i; $class .= ($i % 2) ? '' : ' gigpress-alt';
					if(!$showdata['tour'] && $current_tour) {
						$current_tour = '';
						$class .= ' divider';
					}
					$class .= ($showdata['tour'] && !$tour) ? ' gigpress-tour' : '';
					
					include gigpress_template('shows-list');

				}
				
				include gigpress_template('shows-list-end');						
			}
		}
		
		if($some_results) {
		// After all artist groups
			include gigpress_template('shows-list-footer');
		} else {	
			// No shows from any artist
			include gigpress_template('shows-list-empty');
		}
		
	} else {

		// Not grouping by artists

		$shows = $wpdb->get_results("
			SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_expire " . $condition . " AND show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id" . $further_where . " ORDER BY " . $orderby . "show_date " . $sort . ",show_time " . $sort . $limit);
				
		if($shows) {
		
			$current_tour = '';
			$i = 0;

			ob_start();
			
			include gigpress_template('shows-list-start');
			
			foreach($shows as $show) {
			
				// For each individual show
				$showdata = gigpress_prepare($show, 'public');
				
				if($showdata['tour'] && $showdata['tour'] != $current_tour && !$tour) {
					$current_tour = $showdata['tour'];
					include gigpress_template('shows-tour-heading');
				}
				
				$class = $showdata['status'];
				++ $i; $class .= ($i % 2) ? '' : ' gigpress-alt';
				if(!$showdata['tour'] && $current_tour) {
					$current_tour = '';
					$class .= ' divider';
				}
				$class .= ($showdata['tour'] && !$tour) ? ' gigpress-tour' : '';
				
				include gigpress_template('shows-list');
			}
			
			include gigpress_template('shows-list-end');
			include gigpress_template('shows-list-footer');			
			
		} else {
			// No shows to display
			include gigpress_template('shows-list-empty');
		}	

	}
	
	echo('<!-- Generated by GigPress ' . GIGPRESS_VERSION . ' -->
	');
	return ob_get_clean();	
}