<?php

function gigpress_add() {

	global $wpdb;
	global $gigpress;
		
	if(isset($_POST['gpaction']) && $_POST['gpaction'] == "add") {
		// This is for when we've just POST-ed a new show ...
		require_once('admin-handlers.php');
		gigpress_add_show();
	}

	$gpo = get_option('gigpress_settings');
		
	// If they're done with the welcome message, kill it
	if(isset($_GET['gpaction']) && $_GET['gpaction'] == "killwelcome") {
		$gpo['welcome'] = "no";
		update_option('gigpress_settings', $gpo);
		$gpo = get_option('gigpress_settings');
	}
	
	// If the welcome message is to be displayed, then do so
	if($gpo['welcome'] == "yes") { ?>
	
		<div id="message" class="updated">
			<p><?php _e("<strong>Welcome to GigPress!</strong> You should first", "gigpress"); ?> <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gigpress-options"><?php _e("customize some of the options", "gigpress"); ?></a>, <?php _e("then get right into adding shows below. Questions?  Please check out the", "gigpress"); ?> <a href="http://gigpress.com/docs"><?php _e("documentation", "gigpress"); ?></a> <?php _e("and", "gigpress"); ?> <a href="http://gigpress.com/faq"><?php _e("FAQ", "gigpress"); ?></a> <?php _e("on the GigPress website. Enjoy!", "gigpress"); ?> <small>(<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gigpress/gigpress.php&amp;gpaction=killwelcome"><?php _e("Don't show this again", "gigpress"); ?>.</a>)</small></p>
			
		</div>
		
	<?php } ?>
	
	<div class="wrap gigpress <?php echo $gigpress['admin_class']; ?>">
	
	<?php 	// We're about to edit an existing show ...
			
		if(isset($_GET['gpaction']) && ($_GET['gpaction'] == "edit" || $_GET['gpaction'] == "copy")) {

		// Load the previous show info into the edit form, and so forth 
		$showdata = $wpdb->get_results("
		SELECT * from ". $gigpress['gigs_table'] ." WHERE show_id = ". $_GET['show_id'] ." LIMIT 1
		");
			if($showdata) {
				// We got the goods from the DB, so let's get the date ready to
				// populate our date picker, get our variables jimmy-jazzed(?)
				// and proceed with the edit form ...
				
				foreach($showdata as $show) {
					
					$date = explode('-', $show->show_date);
						$mm = $date[1];
						$dd = $date[2];
						$yy = $date[0];
					
					$time = explode(':', $show->show_time);
						$hh = $time[0];
						$min = $time[1];
						$ss = $time[2];
						
						if($ss == "01") {
							$hh = "na";
							$min = "na";
						}
						
					$expire = explode('-', $show->show_expire);
						$exp_mm = $expire[1];
						$exp_dd = $expire[2];
						$exp_yy = $expire[0];
					
					$multi = $show->show_multi;	
					$locale = stripslashes($show->show_locale);
					$address = stripslashes($show->show_address);
					$venue = stripslashes($show->show_venue);
					$venue_url = stripslashes($show->show_venue_url);
					$venue_phone = stripslashes($show->show_venue_phone);
					$country = $show->show_country;
					$admittance = $show->show_ages;
					$tixurl = stripslashes($show->show_tix_url);
					$tixphone = stripslashes($show->show_tix_phone);
					$notes = stripslashes($show->show_notes);
					$price = stripslashes($show->show_price);
					$showtour = $show->show_tour_id;
					$related = $show->show_related;			
				}
				
			} else {
		
				// Couldn't grab the show's details from the database
				// for some reason
				echo('<div id="message" class="error fade"><p>');
				_e("Sorry, but we had trouble loading that show for editing.", "gigpress");
				echo('</div>');
			}
		?>
	
		<?php if($_GET['gpaction'] == "edit") { ?>
		
		<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>		
		<h2><?php _e("Edit this show", "gigpress"); ?></h2>
		
		<form method="post" action="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=<?php echo $_GET['gpreturn']; ?>">
			<?php wp_nonce_field('gigpress-action') ?>
				<input type="hidden" name="gpaction" value="update" />
				<input type="hidden" name="show_id" value="<?php echo $_GET['show_id']; ?>" />
		<?php } ?>
	
	<?php } else {
	
	// We're adding a new show
	
	$date = explode('-', $gpo['default_date']);
		$mm = $date[1];
		$dd = $date[2];
		$yy = $date[0];
	
	$time = explode(':', $gpo['default_time']);
		$hh = $time[0];
		$min = $time[1];
		$ss = $time[2];
		
		if($ss == "01") {
			$hh = "na";
			$min = "na";
		}
		
	$expire = explode('-', $gpo['default_date']);
		$exp_mm = $expire[1];
		$exp_dd = $expire[2];
		$exp_yy = $expire[0];
		
	$showtour = $gpo['default_tour'];
	$country = $gpo['default_country'];
	
	} ?>
	
	<?php if(!isset($_GET['gpaction']) || $_GET['gpaction'] == "copy" || $_GET['gpaction'] == "killwelcome") { ?>
	
	<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>
	<h2><?php _e("Add a show", "gigpress"); ?></h2>
		
	<form method="post" action="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=gigpress/gigpress.php">
		<?php wp_nonce_field('gigpress-action') ?>
		<input type="hidden" name="gpaction" value="add" />
	<?php } ?>
		
		<table class="optiontable editform gp-table">
		<tbody>
		  <tr>
			<th scope="row"><label for="gp_mm"><?php _e("Date", "gigpress") ?>:<span class="gp-required">*</span></label></th>
				<td><select name="gp_mm" id="gp_mm">
				  <option value="01"<?php if($mm == "01") echo(' selected="selected"'); ?>><?php _e("January"); ?></option>
				  <option value="02"<?php if($mm == "02") echo(' selected="selected"'); ?>><?php _e("February"); ?></option>
				  <option value="03"<?php if($mm == "03") echo(' selected="selected"'); ?>><?php _e("March"); ?></option>
				  <option value="04"<?php if($mm == "04") echo(' selected="selected"'); ?>><?php _e("April"); ?></option>
				  <option value="05"<?php if($mm == "05") echo(' selected="selected"'); ?>><?php _e("May"); ?></option>
				  <option value="06"<?php if($mm == "06") echo(' selected="selected"'); ?>><?php _e("June"); ?></option>
				  <option value="07"<?php if($mm == "07") echo(' selected="selected"'); ?>><?php _e("July"); ?></option>
				  <option value="08"<?php if($mm == "08") echo(' selected="selected"'); ?>><?php _e("August"); ?></option>
				  <option value="09"<?php if($mm == "09") echo(' selected="selected"'); ?>><?php _e("September"); ?></option>
				  <option value="10"<?php if($mm == "10") echo(' selected="selected"'); ?>><?php _e("October"); ?></option>
				  <option value="11"<?php if($mm == "11") echo(' selected="selected"'); ?>><?php _e("November"); ?></option>
				  <option value="12"<?php if($mm == "12") echo(' selected="selected"'); ?>><?php _e("December"); ?></option>
				</select>
				  <select name="gp_dd" id="gp_dd">
				  	<?php for($i = 1; $i <= 31; $i++) {
				  	$i = ($i < 10) ? '0' . $i : $i;
				  	echo('<option value="' . $i . '"');
				  	if($dd == $i) echo(' selected="selected"');
				  	echo('>' . $i . '</option>');
				  	} ?>
				  </select>
				  
				  <select name="gp_yy" id="gp_yy">
				  	<?php for($i = 1900; $i <= 2050; $i++) {
				  	echo('<option value="' . $i . '"');
				  	if($yy == $i) echo(' selected="selected"');
				  	echo('>' . $i . '</option>');
				  	} ?>
				</select>
				
				&nbsp; <?php _e("at", "gigpress"); ?> &nbsp; 
				
				<?php if ( $gpo['alternate_clock'] == 1 ) { ?>
				<select name="gp_hh" id="gp_hh" class="twentyfour">
					<option value="na"<?php if($hh == "na") echo(' selected="selected"'); ?>>--</option>
					<option value="00"<?php if($hh == "00") echo(' selected="selected"'); ?>>00</option>
					<option value="01"<?php if($hh == "01") echo(' selected="selected"'); ?>>01</option>
					<option value="02"<?php if($hh == "02") echo(' selected="selected"'); ?>>02</option>
					<option value="03"<?php if($hh == "03") echo(' selected="selected"'); ?>>03</option>
					<option value="04"<?php if($hh == "04") echo(' selected="selected"'); ?>>04</option>
					<option value="05"<?php if($hh == "05") echo(' selected="selected"'); ?>>05</option>
					<option value="06"<?php if($hh == "06") echo(' selected="selected"'); ?>>06</option>
					<option value="07"<?php if($hh == "07") echo(' selected="selected"'); ?>>07</option>
					<option value="08"<?php if($hh == "08") echo(' selected="selected"'); ?>>08</option>
					<option value="09"<?php if($hh == "09") echo(' selected="selected"'); ?>>09</option>
					<option value="10"<?php if($hh == "10") echo(' selected="selected"'); ?>>10</option>
					<option value="11"<?php if($hh == "11") echo(' selected="selected"'); ?>>11</option>
					<option value="12"<?php if($hh == "12") echo(' selected="selected"'); ?>>12</option>
					<option value="13"<?php if($hh == "13") echo(' selected="selected"'); ?>>13</option>
					<option value="14"<?php if($hh == "14") echo(' selected="selected"'); ?>>14</option>
					<option value="15"<?php if($hh == "15") echo(' selected="selected"'); ?>>15</option>
					<option value="16"<?php if($hh == "16") echo(' selected="selected"'); ?>>16</option>
					<option value="17"<?php if($hh == "17") echo(' selected="selected"'); ?>>17</option>
					<option value="18"<?php if($hh == "18") echo(' selected="selected"'); ?>>18</option>
					<option value="19"<?php if($hh == "19") echo(' selected="selected"'); ?>>19</option>
					<option value="20"<?php if($hh == "20") echo(' selected="selected"'); ?>>20</option>
					<option value="21"<?php if($hh == "21") echo(' selected="selected"'); ?>>21</option>
					<option value="22"<?php if($hh == "22") echo(' selected="selected"'); ?>>22</option>
					<option value="23"<?php if($hh == "23") echo(' selected="selected"'); ?>>23</option>
				</select>
				<?php } else { ?>
				<select name="gp_hh" id="gp_hh" class="twelve">
					<optgroup label="<?php _e("N/A", "gigpress"); ?>">
					  <option value="na"<?php if($hh == "na") echo(' selected="selected"'); ?>>--</option>
					</optgroup>
					<optgroup id="am" label="AM">
					  <option value="00"<?php if($hh == "00") echo(' selected="selected"'); ?>>12</option>
					  <option value="01"<?php if($hh == "01") echo(' selected="selected"'); ?>>01</option>
					  <option value="02"<?php if($hh == "02") echo(' selected="selected"'); ?>>02</option>
					  <option value="03"<?php if($hh == "03") echo(' selected="selected"'); ?>>03</option>
					  <option value="04"<?php if($hh == "04") echo(' selected="selected"'); ?>>04</option>
					  <option value="05"<?php if($hh == "05") echo(' selected="selected"'); ?>>05</option>
					  <option value="06"<?php if($hh == "06") echo(' selected="selected"'); ?>>06</option>
					  <option value="07"<?php if($hh == "07") echo(' selected="selected"'); ?>>07</option>
					  <option value="08"<?php if($hh == "08") echo(' selected="selected"'); ?>>08</option>
					  <option value="09"<?php if($hh == "09") echo(' selected="selected"'); ?>>09</option>
					  <option value="10"<?php if($hh == "10") echo(' selected="selected"'); ?>>10</option>
					  <option value="11"<?php if($hh == "11") echo(' selected="selected"'); ?>>11</option>
					</optgroup>
					<optgroup id="pm" label="PM">
					  <option value="12"<?php if($hh == "12") echo(' selected="selected"'); ?>>12</option>
					  <option value="13"<?php if($hh == "13") echo(' selected="selected"'); ?>>01</option>
					  <option value="14"<?php if($hh == "14") echo(' selected="selected"'); ?>>02</option>
					  <option value="15"<?php if($hh == "15") echo(' selected="selected"'); ?>>03</option>
					  <option value="16"<?php if($hh == "16") echo(' selected="selected"'); ?>>04</option>
					  <option value="17"<?php if($hh == "17") echo(' selected="selected"'); ?>>05</option>
					  <option value="18"<?php if($hh == "18") echo(' selected="selected"'); ?>>06</option>
					  <option value="19"<?php if($hh == "19") echo(' selected="selected"'); ?>>07</option>
					  <option value="20"<?php if($hh == "20") echo(' selected="selected"'); ?>>08</option>
					  <option value="21"<?php if($hh == "21") echo(' selected="selected"'); ?>>09</option>
					  <option value="22"<?php if($hh == "22") echo(' selected="selected"'); ?>>10</option>
					  <option value="23"<?php if($hh == "23") echo(' selected="selected"'); ?>>11</option>
					</optgroup>
				</select>
				<?php } ?>
				<select name="gp_min" id="gp_min">
					  	<option value="na"<?php if($min == "na") echo(' selected="selected"'); ?>>--</option>
						<option value="00"<?php if($min == "00") echo(' selected="selected"'); ?>>00</option>
						<option value="05"<?php if($min == "05") echo(' selected="selected"'); ?>>05</option>
						<option value="10"<?php if($min == "10") echo(' selected="selected"'); ?>>10</option>
						<option value="15"<?php if($min == "15") echo(' selected="selected"'); ?>>15</option>
						<option value="20"<?php if($min == "20") echo(' selected="selected"'); ?>>20</option>
						<option value="25"<?php if($min == "25") echo(' selected="selected"'); ?>>25</option>
						<option value="30"<?php if($min == "30") echo(' selected="selected"'); ?>>30</option>
						<option value="35"<?php if($min == "35") echo(' selected="selected"'); ?>>35</option>
						<option value="40"<?php if($min == "40") echo(' selected="selected"'); ?>>40</option>
						<option value="45"<?php if($min == "45") echo(' selected="selected"'); ?>>45</option>
						<option value="50"<?php if($min == "50") echo(' selected="selected"'); ?>>50</option>
						<option value="55"<?php if($min == "55") echo(' selected="selected"'); ?>>55</option>
				</select>
			
					<span id="ampm">&nbsp;</span>
					<p class="gp-instructions">&nbsp;<label><input type="checkbox" value="1"<?php if($multi == 1) echo(' checked="checked"'); ?> id="multi" name="multi" />&nbsp;<?php _e("This is a multi-day event", "gigpress"); ?></label></p>
					</td>
				</tr>
				<!-- For multiple-day events -->
				<tr id="expire"<?php if($multi != 1) echo(' class="inactive"'); ?>>
					<th scope="row"><label for="exp_mm"><?php _e("End date", "gigpress") ?>:</label></th>
					<td><select name="exp_mm" id="exp_mm">
						  <option value="01"<?php if($exp_mm == "01") echo(' selected="selected"'); ?>><?php _e("January"); ?></option>
						  <option value="02"<?php if($exp_mm == "02") echo(' selected="selected"'); ?>><?php _e("February"); ?></option>
						  <option value="03"<?php if($exp_mm == "03") echo(' selected="selected"'); ?>><?php _e("March"); ?></option>
						  <option value="04"<?php if($exp_mm == "04") echo(' selected="selected"'); ?>><?php _e("April"); ?></option>
						  <option value="05"<?php if($exp_mm == "05") echo(' selected="selected"'); ?>><?php _e("May"); ?></option>
						  <option value="06"<?php if($exp_mm == "06") echo(' selected="selected"'); ?>><?php _e("June"); ?></option>
						  <option value="07"<?php if($exp_mm == "07") echo(' selected="selected"'); ?>><?php _e("July"); ?></option>
						  <option value="08"<?php if($exp_mm == "08") echo(' selected="selected"'); ?>><?php _e("August"); ?></option>
						  <option value="09"<?php if($exp_mm == "09") echo(' selected="selected"'); ?>><?php _e("September"); ?></option>
						  <option value="10"<?php if($exp_mm == "10") echo(' selected="selected"'); ?>><?php _e("October"); ?></option>
						  <option value="11"<?php if($exp_mm == "11") echo(' selected="selected"'); ?>><?php _e("November"); ?></option>
						  <option value="12"<?php if($exp_mm == "12") echo(' selected="selected"'); ?>><?php _e("December"); ?></option>
					</select>
					
					  <select name="exp_dd" id="exp_dd">
					  	<?php for($i = 1; $i <= 31; $i++) {
					  	$i = ($i < 10) ? '0' . $i : $i;
					  	echo('<option value="' . $i . '"');
					  	if($exp_dd == $i) echo(' selected="selected"');
					  	echo('>' . $i . '</option>');
					  	} ?>
					  </select>
					  
					  <select name="exp_yy" id="exp_yy">
					  	<?php for($i = 1900; $i <= 2050; $i++) {
					  	echo('<option value="' . $i . '"');
					  	if($exp_yy == $i) echo(' selected="selected"');
					  	echo('>' . $i . '</option>');
					  	} ?>
				  	</select>
				  	
					</td>
				</tr>
			<tr>
				<th scope="row"><label for="show_locale"><?php _e("City", "gigpress") ?>:<span class="gp-required">*</span></label></th>
				<td><input type="text" size="48" name="show_locale" id="show_locale" value="<?php echo $locale; ?>" class="required" /><br />
				<p class="gp-instructions"><?php _e("I'd suggest a format along the lines of: <strong>Winnipeg, MB</strong>.", "gigpress"); ?></p></td>
			  </tr>

			<?php if ( $_GET['gpaction'] == 'edit' ) { ?>
			
			<tr>
				<th scope="row"><label for="show_status"><?php _e("Status", "gigpress") ?>:</label></th>
				<td><select name="show_status" id="show_status">
						<option value="active"<?php if($show->show_status == 'active') echo(' selected="selected"'); ?>><?php _e("Active", "gigpress"); ?></option>
						<option value="soldout"<?php if($show->show_status == 'soldout') echo(' selected="selected"'); ?>><?php _e("Sold Out", "gigpress"); ?></option>
						<option value="cancelled"<?php if($show->show_status == 'cancelled') echo(' selected="selected"'); ?>><?php _e("Cancelled", "gigpress"); ?></option>
					</select>
				</td>
			</tr>
			
			<?php } ?>
			  <tr>
				<th scope="row"><label for="show_venue"><?php _e("Venue", "gigpress") ?>:<span class="gp-required">*</span></label>
				<span class="moreVenue" style="display:none;">(<a href="#"><?php _e("toggle venue info", "gigpress"); ?></a>)</span>
				</th>
				<td><input type="text" size="48" name="show_venue" id="show_venue" value="<?php echo $venue; ?>" class="required" /><br />
				<p class="gp-instructions"><?php _e("This is a required field, so if you don't know, just put <strong>TBA</strong>.", "gigpress"); ?></p></td>
			  </tr>
			</tbody>
			
			<tbody id="venueInfo"<?php if ($address || $venue_url || $venue_phone) echo(' class="active"'); ?>>
			<tr>
				<th scope="row"><label for="show_address"><?php _e("Venue address", "gigpress") ?>:</label></th>
				<td><input type="text" size="48" name="show_address" id="show_address" value="<?php echo $address; ?>" /><br />
				<p class="gp-instructions"><?php _e("If you enter a street address, it will enable auto-linkage to Google Maps.  True that, double true.", "gigpress"); ?></p></td>
			  </tr>			  
			  <tr>
				<th scope="row"><label for="show_venue_url"><?php _e("Venue website", "gigpress") ?>:</label></th>
				<td><input type="text" size="48" name="show_venue_url" id="show_venue_url" value="<?php echo $venue_url; ?>" /><br />
				<p class="gp-instructions"><?php _e("Any value entered here will be added as a hyperlink attached to the name of the venue.", "gigpress"); ?></p></td>
			  </tr>
			  <tr>
				<th scope="row"><label for="show_venue_phone"><?php _e("Venue phone", "gigpress") ?>:</label></th>
				<td><input type="text" size="48" name="show_venue_phone" id="show_venue_phone" value="<?php echo $venue_phone; ?>" /></td>
			  </tr>			  
			</tbody> 
			
			<tbody>
			 <tr>
				<th scope="row"><label for="show_country"><?php _e("Country", "gigpress") ?>:</label></th>
				<td>
					<select name="show_country" id="show_country">
					<?php $gp_countries = array("US" => "United States", "CA" => "Canada", "AF" => "Afghanistan", "AX" => "Aland Islands", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua and Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BA" => "Bosnia and Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "IO" => "British Indian Ocean Territory", "BN" => "Brunei Darussalam", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos (Keeling) Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo", "CD" => "Congo (DR)", "CK" => "Cook Islands", "CR" => "Costa Rica", "CI" => "Cote D'Ivoire", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands (Malvinas)", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GG" => "Guernsey", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard and McDonald Islands", "VA" => "Holy See (Vatican City State)", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IQ" => "Iraq", "IE" => "Ireland", "IM" => "Isle of Man", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JE" => "Jersey", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KP" => "Korea (North)", "KR" => "Korea (South)", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macau", "MK" => "Macedonia", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestinian Territory (Occupied)", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RE" => "Reunion", "RO" => "Romania", "RU" => "Russian Federation", "RW" => "Rwanda", "BL" => "Saint Barthelemy", "SH" => "Saint Helena", "KN" => "Saint Kitts and Nevis", "LC" => "Saint Lucia", "MF" => "Saint Martin (French)", "PM" => "Saint Pierre and Miquelon", "VC" => "Saint Vincent and the Grenadines", "WS" => "Samoa", "SM" => "San Marino", "ST" => "Sao Tome and Principe", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia and South Sandwich Islands", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard and Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syria", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad and Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks and Caicos Islands", "TV" => "Tuvalu", "UG" => "Uganda", "UA" => "Ukraine", "AE" => "United Arab Emirates", "UK" => "United Kingdom", "UM" => "United States Minor Outlying Islands", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Vietnam", "VG" => "Virgin Islands (British)", "VI" => "Virgin Islands (US)", "WF" => "Wallis and Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");
				foreach ($gp_countries as $code => $name) {
					$sel = ($code == $country) ? ' selected="selected"' : '';
					echo('<option value="' . $code . '"' . $sel . '>' . $name . '</option>');
				} ?>
				</select>
			</td>
			  </tr>
			  <tr>
				<th scope="row"><label for="show_ages"><?php _e("Admittance", "gigpress") ?>:</label></th>
				<td><select name="show_ages" id="show_ages">
				  <option value="Not sure"<?php if($admittance == "Not sure") echo(' selected="selected"'); ?>><?php _e("Not sure", "gigpress") ?></option>
				  <option value="No minors"<?php if($admittance == "No minors") echo(' selected="selected"'); ?>><?php _e("No minors", "gigpress") ?></option>
				  <option value="All Ages/Licensed"<?php if($admittance == "All Ages/Licensed") echo(' selected="selected"'); ?>><?php _e("All Ages/Licensed", "gigpress") ?></option>
				  <option value="All Ages"<?php if($admittance == "All Ages") echo(' selected="selected"'); ?>><?php _e("All Ages", "gigpress") ?></option>
				  </select>
				</td>
			  </tr>
			  <tr>
				<th scope="row"><label for="show_price"><?php _e("Price", "gigpress") ?>:</label></th>
				<td><input type="text" size="10" name="show_price" id="show_price" value="<?php echo $price; ?>" /><br />
				<p class="gp-instructions"><?php _e("To accomodate various currency display formats, no auto-formatting will be applied to what you enter here. Keep that in mind!", "gigpress"); ?></p></td>
			  </tr>
			  <tr>
				<th scope="row"><label for="show_tix_url"><?php _e("Ticket URL", "gigpress") ?>:</label></th>
				<td><input type="text" size="48" name="show_tix_url" id="show_tix_url" value="<?php echo $tixurl; ?>" /><br />
				<p class="gp-instructions"><?php _e("This will enable the purchase link when displaying your tour dates.", "gigpress"); ?></p></td>
			  </tr>
 				<tr>
				<th scope="row"><label for="show_tix_phone"><?php _e("Ticket phone", "gigpress") ?>:</label></th>
				<td><input type="text" size="48" name="show_tix_phone" id="show_tix_phone" value="<?php echo $tixphone; ?>" /></td>
			  </tr>
			  <tr>
				<th scope="row"><label for="show_notes"><?php _e("Notes", "gigpress") ?>:</label></th>
				<td><textarea name="show_notes" id="show_notes" cols="45" rows="5"><?php echo $notes; ?></textarea>
				<br />
				<p class="gp-instructions"><?php _e("Use this space to list opening (or headlining) bands, 'presented by' info, or whatever other info you like.", "gigpress"); ?></p></td>
			  </tr>
			  <tr>
				<th scope="row"><label for="show_tour_id"><?php _e("Part of a tour?", "gigpress"); ?></label></th>
				<td>
					<select name="show_tour_id" id="show_tour_id">
				  		<option value="0">--</option>
				  	<?php $tours = $wpdb->get_results("
						SELECT * FROM ". $gigpress['tours_table'] ." WHERE tour_status = 'active' ORDER BY tour_order DESC
						");
						if($tours != FALSE) {
							foreach($tours as $tour) {
								$tourname = stripslashes($tour->tour_name);
								echo("<option value=\"$tour->tour_id\"");
								if($showtour == $tour->tour_id) echo(' selected="selected"');
								echo(">$tourname</option>\n\t\t\t");
							}
						} else {
							echo("<option value=\"0\">".__("No tours in the database", "gigpress")."</option>/n/t/t/t");
						}
					?>
				  </select>
				  <br />
				<p class="gp-instructions"><?php _e("If this show is part of a tour you've already entered into GigPress, select it here.<br />No tours yet?", "gigpress"); ?> <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gigpress-tours"><?php _e("Create them here.", "gigpress"); ?></a></p>
				</td>
			  </tr>
		<tr>
				<th scope="row"><label for="show_related"><?php _e("Related post", "gigpress") ?>:</label></th>
				<td>
					<select name="show_related" id="show_related">
				  		<option value="0"><?php _e("None", "gigpress") ?></option>
				  		<option value="new"<?php if($_GET['gpaction'] != "edit" && $gpo['autocreate_post'] == "1") echo(' selected="selected"'); ?>><?php _e("Create a new post", "gigpress") ?></option>
				  		<option value="0">-------------</option>
				  	<?php 
				  	$entries = $wpdb->get_results("SELECT ID, post_title, post_date FROM " . $wpdb->prefix . "posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY ID DESC LIMIT 100");
						foreach($entries as $entry) {
							$date = mysql2date($gpo['date_format'], $entry->post_date);
						?>
							<option value="<?php echo $entry->ID; ?>"<?php if($entry->ID == $related) echo(' selected="selected"'); ?>><?php echo apply_filters("the_title", $entry->post_title); ?> (<?php echo $date; ?>)</option>
							
						<?php } ?>
				  </select>
				  <br />
				<p class="gp-instructions"><?php _e("You can link any show to a WordPress post, useful for show recaps, tour journals, uploading pictures, and allowing user comments.", "gigpress"); ?></p>
				</td>
			  </tr>
			</tbody>
		</table>		
			<?php if(isset($_GET['gpaction']) && $_GET['gpaction'] == "edit") { ?>
				<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e("Update show", "gigpress") ?>" /> <?php _e("or", "gigpress"); ?> <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=<?php echo $_GET['gpreturn']; ?>"><?php _e("cancel", "gigpress"); ?></a></p>
			<?php } else { ?>
				<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e("Add show", "gigpress") ?>" /></p>
			<?php } ?>

	</form>
	</div>
<?php }