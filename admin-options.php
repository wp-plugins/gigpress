<?php

function gigpress_options() {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');

	// This gives us the magic fading menu when we update.  Yes - magic.
	require(ABSPATH . 'wp-admin/options-head.php');
	
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "trash") {
		require_once('admin-handlers.php');
		gigpress_empty_trash();		
	}
	
	?>

	<div class="wrap gigpress <?php echo $gigpress['admin_class']; ?> gp-options">

	<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>			
	<h2><?php _e("Settings", "gigpress"); ?></h2>
	
	<form method="post" action="options.php">
	
	<table class="optiontable editform gp-table form-table">
		<tr>
			<th scope="row"><?php _e("Name of your band", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[band]" value="<?php echo stripslashes($gpo['band']); ?>" /><br />

				<small class="gp-instructions"><?php _e("This is used in your RSS feed and hCalendar data.", "gigpress") ?></small>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php _e("Full URL to your 'Upcoming Shows' page", "gigpress") ?>:</th>
			<td>
				<input type="text" size="48" name="gigpress_settings[shows_page]" value="<?php echo stripslashes($gpo['shows_page']); ?>" /><br />

				<p><label><input type="checkbox" name="gigpress_settings[sidebar_link]" value="1" <?php if($gpo['sidebar_link'] == 1) echo('checked="checked"'); ?> /> <?php _e("Show a link to this page below my sidebar listing.", "gigpress"); ?></label></p>
			</td>
		</tr>	
		<tr>
			<th scope="row"><?php _e("Upcoming Shows link phrase", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[upcoming_phrase]" size="48" value="<?php echo stripslashes($gpo['upcoming_phrase']); ?>" />
			</td>
		</tr>	
		<tr>
			<th scope="row"><?php _e("No upcoming shows message", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[noupcoming]" size="48" value="<?php echo stripslashes($gpo['noupcoming']); ?>" />
			</td>
		</tr>	
		<tr>
			<th scope="row"><?php _e("No past shows message", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[nopast]" size="48" value="<?php echo stripslashes($gpo['nopast']); ?>" />
			</td>
		</tr>

		<tr>
			<th scope="row"><?php _e("User level required to use GigPress", "gigpress") ?>:</th>
			<td>
				<select name="gigpress_settings[user_level]">
					<option value="8"<?php if ($gpo['user_level'] == '8') echo (' selected="selected"'); ?>><?php _e("Administrator", "gigpress"); ?></option>
					<option value="5"<?php if ($gpo['user_level'] == '5') echo (' selected="selected"'); ?>><?php _e("Editor", "gigpress"); ?></option>
					<option value="2"<?php if ($gpo['user_level'] == '2') echo (' selected="selected"'); ?>><?php _e("Author", "gigpress"); ?></option>
					<option value="1"<?php if ($gpo['user_level'] == '1') echo (' selected="selected"'); ?>><?php _e("Contributor", "gigpress"); ?></option>
				</select>
			</td>
		</tr>
		
		<tr>
			<th scope="row"><?php _e("Short Date Format", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[date_format]" value="<?php echo $gpo['date_format']; ?>" /><br />
				<small class="gp-instructions"><?php _e("Used in the main show listings.", "gigpress") ?></small> <?php _e("Output", "gigpress") ?>: <strong><?php echo mysql2date($gpo['date_format'], current_time('mysql')); ?></strong>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Long Date Format", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[date_format_long]" value="<?php echo $gpo['date_format_long']; ?>" /><br />
				<small class="gp-instructions"><?php _e("Used in the Related Post entry.", "gigpress") ?></small> <?php _e("Output", "gigpress") ?>: <strong><?php echo mysql2date($gpo['date_format_long'], current_time('mysql')); ?></strong>
			</td>
		</tr>			
		<tr>
			<th scope="row"><?php _e("Time Format", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[time_format]" value="<?php echo $gpo['time_format']; ?>" /> &nbsp; <label><input type="checkbox" name="gigpress_settings[alternate_clock]" value="1"<?php if($gpo['alternate_clock'] == 1) echo(' checked="checked"'); ?> />&nbsp;<?php _e("I use a 24 hour clock", "gigpress"); ?></label><br />
				<?php _e("Output", "gigpress") ?>: <strong><?php echo gmdate($gpo['time_format'], current_time('timestamp')); ?></strong><br />
				<small class="gp-instructions"><a href="http://codex.wordpress.org/Formatting_Date_and_Time"><?php _e("Here's some documentation on date and time formatting", "gigpress") ?></a>. <?php _e("Click 'Update options' to update the sample output.", "gigpress") ?></small>
			
			</td>
		</tr>	
		<tr>
			<th scope="row"><?php _e("Related Posts", "gigpress") ?></th>
			<td>
				<p><?php _e("Display gig info in Related Post entries", "gigpress"); ?> &hellip;</p>
				<p><label><input type="radio" name="gigpress_settings[related_position]" value="before"<?php if($gpo['related_position'] == "before") echo(' checked="checked"'); ?> /> <?php _e("before the post content", "gigpress"); ?></label> &hellip; 
				<label><input type="radio" name="gigpress_settings[related_position]" value="after"<?php if($gpo['related_position'] == "after") echo(' checked="checked"'); ?> /> <?php _e("after the post content", "gigpress"); ?></label> &hellip; 
				<label><input type="radio" name="gigpress_settings[related_position]" value="nowhere"<?php if($gpo['related_position'] == "nowhere") echo(' checked="checked"'); ?> /> <?php _e("nowhere at all", "gigpress"); ?></label></p>
				<p class="gp-instructions"><?php _e("If a gig has a Related Post, that gig's details will appear at the specified position in that post.", "gigpress"); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Related show heading", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[related_heading]" size="48" value="<?php echo stripslashes($gpo['related_heading']); ?>" /><br />
				<small class="gp-instructions"><?php _e("This appears before the gig details in your Related Post entry.", "gigpress") ?></small>
			</td>
		</tr>			
		<tr>
			<th scope="row"><?php _e("Related Posts Category", "gigpress") ?></th>
			<td>
				<p><label><input type="checkbox" name="gigpress_settings[autocreate_post]" value="1"<?php if($gpo['autocreate_post'] == 1) echo(' checked="checked"'); ?> /> <?php _e("Automatically create a Related Post for every new show I enter.", "gigpress"); ?></label></p>
				<p><label><?php _e("When creating Related Posts, put them in this category", "gigpress"); ?>: &nbsp; 
				<select name="gigpress_settings[related_category]">
					<?php $categories = get_categories('hide_empty=0');
					foreach($categories as $cat) {
						$title = $cat->cat_name; ?>
						<option value="<?php echo $cat->cat_ID; ?>"<?php if ( $gpo['related_category'] == $cat->cat_ID) echo(' selected="selected"'); ?>><?php echo apply_filters("the_title", $title); ?></option>
					<?php } ?> 
				</select></label></p>
				<p><label><input type="checkbox" name="gigpress_settings[category_exclude]" value="1"<?php if($gpo['category_exclude'] == 1) echo(' checked="checked"'); ?> /> <?php _e("Exclude this category from my normal post listings.", "gigpress"); ?></label></p>
			</td>
		</tr>
		
		<tr>
			<th scope="row"><?php _e("Related Posts Linking", "gigpress") ?></th>
			<td>
				<p><?php _e("Place a link to each show's Related Post in the following fields", "gigpress"); ?> &hellip;</p>
				<p><label><input type="checkbox" name="gigpress_settings[relatedlink_date]" value="1"<?php if($gpo['relatedlink_date'] == "1") echo(' checked="checked"'); ?> /> <?php _e("Date", "gigpress"); ?></label> &nbsp; 
				<label><input type="checkbox" name="gigpress_settings[relatedlink_city]" value="1"<?php if($gpo['relatedlink_city'] == "1") echo(' checked="checked"'); ?> /> <?php _e("City", "gigpress"); ?></label> &nbsp; 
				<label><input type="checkbox" name="gigpress_settings[relatedlink_notes]" value="1"<?php if($gpo['relatedlink_notes'] == "1") echo(' checked="checked"'); ?> /> <?php _e("Notes", "gigpress"); ?></label> &nbsp; </p>
			</td>
		</tr>				
		<tr>
			<th scope="row"><?php _e("Related post phrase", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[related]" size="48" value="<?php echo stripslashes($gpo['related']); ?>" /><br />
				<small class="gp-instructions"><?php _e("This appears in your shows listing.", "gigpress") ?></small>
			</td>
		</tr>	
		<tr>
			<th scope="row"><?php _e("RSS Feed", "gigpress") ?></th>
			<td>
				<p><?php _e("Please include a link to my upcoming shows RSS feed", "gigpress"); ?> &hellip;</p>
				<p><label><input type="checkbox" name="gigpress_settings[rss_upcoming]" value="1" <?php if($gpo['rss_upcoming'] == 1) echo('checked="checked"'); ?> /> <?php _e("below my upcoming shows table", "gigpress"); ?></label> &hellip; 
				<label><input type="checkbox" name="gigpress_settings[rss_list]" value="1" <?php if($gpo['rss_list'] == 1) echo('checked="checked"'); ?> /> <?php _e("below my sidebar listing", "gigpress"); ?></label> &hellip; 
				<label><input type="checkbox" name="gigpress_settings[rss_head]" value="1" <?php if($gpo['rss_head'] == 1) echo('checked="checked"'); ?> /> <?php _e("in the <code>head</code> portion of my page", "gigpress"); ?></label></p>
				<p class="gp-instructions"><?php _e("Your RSS feed for upcoming shows is", "gigpress"); ?><code><?php echo ' ' . $gigpress['rss']; ?></code>. <?php _e("Note that the FeedBurner FeedSmith plugin is not compatible with GigPress.", "gigpress"); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("RSS Feed title", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[rss_title]" size="48" value="<?php echo stripslashes($gpo['rss_title']); ?>" /><br />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Tour grouping", "gigpress") ?></th>
			<td>
				<p><label><input type="checkbox" name="gigpress_settings[tour_segment]" value="1" <?php if($gpo['tour_segment'] == 1) echo('checked="checked"'); ?> /> <?php _e("Group shows into tours", "gigpress") ?> &hellip;</label></p>
				
				<p><label><input type="radio" name="gigpress_settings[tour_location]" value="before" <?php if($gpo['tour_location'] == "before") echo('checked="checked"'); ?> class="tog" /> &hellip; <?php _e("display tours <b>before</b> non-tour shows", "gigpress") ?></label><br />
				<label><input type="radio" name="gigpress_settings[tour_location]" value="after" <?php if($gpo['tour_location'] == "after") echo('checked="checked"'); ?> class="tog" /> &hellip; <?php _e("display tours <b>after</b> non-tour shows", "gigpress") ?></label></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Tour label", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[tour_label]" size="48" value="<?php echo stripslashes($gpo['tour_label']); ?>" /><br />
			</td>
		</tr>	
		<tr>
			<th scope="row"><?php _e("Individual Shows heading", "gigpress") ?>:</th>
			<td>
				<input type="text" name="gigpress_settings[individual_heading]" size="48" value="<?php echo stripslashes($gpo['individual_heading']); ?>" /><br />
			</td>
		</tr>				
		<tr>
			<th scope="row"><?php _e("Heading level of tour names", "gigpress") ?>:</th>
			<td>
				<select name="gigpress_settings[tour_heading]">
					<option value="h1"<?php if ($gpo['tour_heading'] == 'h1') echo (' selected="selected"'); ?>>h1</option>
					<option value="h2"<?php if ($gpo['tour_heading'] == 'h2') echo (' selected="selected"'); ?>>h2</option>
					<option value="h3"<?php if ($gpo['tour_heading'] == 'h3') echo (' selected="selected"'); ?>>h3</option>
					<option value="h4"<?php if ($gpo['tour_heading'] == 'h4') echo (' selected="selected"'); ?>>h4</option>
					<option value="h5"<?php if ($gpo['tour_heading'] == 'h5') echo (' selected="selected"'); ?>>h5</option>
				</select>
				<br />
				<small class="gp-instructions"><?php _e("Depending on the semantic structure of your page template, you may want the headings which display the name of your tour(s) to be a different level.", "gigpress"); ?> (<a href="http://en.wikipedia.org/wiki/HTML_element#Headings"><?php _e("More on HTML headings.", "gigpress"); ?></a>)</small>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Country display", "gigpress") ?>:</th>
			<td>	
				<p><label><input type="checkbox" name="gigpress_settings[display_country]" value="1" <?php if($gpo['display_country'] == 1) echo('checked="checked"'); ?> /> <?php _e("Display 'Country' column.", "gigpress") ?></label></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Link behaviour", "gigpress") ?>:</th>
			<td><p><label><input type="checkbox" name="gigpress_settings[target_blank]" value="1" <?php if($gpo['target_blank'] == 1) echo('checked="checked"'); ?> /> <?php _e("Open external links in new windows.", "gigpress") ?></label><br />
				<small class="gp-instructions"><?php _e("This will add the", "gigpress"); ?> <code>target="_blank"</code> <?php _e("attribute to all venue, ticket and Google Maps links. Keep in mind that this behaviour can be annoying to users who like to control their own web browser, and that this attribute does not validate under strict HTML doctypes.", "gigpress"); ?></small></p>
			</td>
		</tr>
		
	</table>
			
		<?php // We need to populate the form with the options not represented here, or else they'll get deleted ?>
		<input type="hidden" name="gigpress_settings[db_version]" value="<?php echo $gpo['db_version']; ?>" />
		<input type="hidden" name="gigpress_settings[default_country]" value="<?php echo $gpo['default_country']; ?>" />
		<input type="hidden" name="gigpress_settings[default_date]" value="<?php echo $gpo['default_date']; ?>" />
		<input type="hidden" name="gigpress_settings[default_time]" value="<?php echo $gpo['default_time']; ?>" />
		<input type="hidden" name="gigpress_settings[default_tour]" value="<?php echo $gpo['default_tour']; ?>" />
		<input type="hidden" name="gigpress_settings[welcome]" value="<?php echo $gpo['welcome']; ?>" />
		<input type="hidden" name="gigpress_settings[widget_heading]" value="<?php echo $gpo['widget_heading']; ?>" />
		<input type="hidden" name="gigpress_settings[widget_number]" value="<?php echo $gpo['widget_number']; ?>" />
		<input type="hidden" name="gigpress_settings[widget_segment]" value="<?php echo $gpo['widget_segment']; ?>" />
		
		<?php if ( function_exists('settings_fields') ) {
		
			settings_fields('gigpress');
			
		} else { ?>
		
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="gigpress_settings" />
			<?php wp_nonce_field('update-options');
		
		} ?>
		
		<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e("Save Changes", "gigpress") ?>" /></p>

	</form>
		
	<?php
		if($tour_count = $wpdb->get_var("
		SELECT count(*) FROM ". $gigpress['tours_table'] ." WHERE tour_status = 'deleted'
		")) {
			$tours = $tour_count;
		} else {
			$tours = 0;
		}
		
		if($show_count = $wpdb->get_var("
		SELECT count(*) FROM ". $gigpress['gigs_table'] ." WHERE show_status = 'deleted'
		")) {
			$shows = $show_count;
		} else {
			$shows = 0;
		}
		
		$trashit = get_bloginfo('wpurl').'/wp-admin/admin.php?page=gigpress-options&amp;gpaction=trash';
		$trashit = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($trashit, 'gigpress-action') : $trashit;

	echo('<p class="gp-trash">'. __("You currently have", "gigpress"). ' <strong>'. $shows .' '. __("shows", "gigpress"). '</strong> '. __("and", "gigpress"). ' <strong>'. $tours .' '. __("tours", "gigpress") .'</strong> '. __("in your trash", "gigpress").'.');
	if($shows != 0 || $tours != 0) {
		echo(' <a href="'. $trashit .'">'. __("Take out the trash now", "gigpress") .'</a>.');
	}
	echo('</p>');
	?>

	</div>
<?php }