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
					<option value="01"<?php if($dd == "01") echo(' selected="selected"'); ?>>01</option>
					<option value="02"<?php if($dd == "02") echo(' selected="selected"'); ?>>02</option>
					<option value="03"<?php if($dd == "03") echo(' selected="selected"'); ?>>03</option>
					<option value="04"<?php if($dd == "04") echo(' selected="selected"'); ?>>04</option>
					<option value="05"<?php if($dd == "05") echo(' selected="selected"'); ?>>05</option>
					<option value="06"<?php if($dd == "06") echo(' selected="selected"'); ?>>06</option>
					<option value="07"<?php if($dd == "07") echo(' selected="selected"'); ?>>07</option>
					<option value="08"<?php if($dd == "08") echo(' selected="selected"'); ?>>08</option>
					<option value="09"<?php if($dd == "09") echo(' selected="selected"'); ?>>09</option>
					<option value="10"<?php if($dd == "10") echo(' selected="selected"'); ?>>10</option>
					<option value="11"<?php if($dd == "11") echo(' selected="selected"'); ?>>11</option>
					<option value="12"<?php if($dd == "12") echo(' selected="selected"'); ?>>12</option>
					<option value="13"<?php if($dd == "13") echo(' selected="selected"'); ?>>13</option>
					<option value="14"<?php if($dd == "14") echo(' selected="selected"'); ?>>14</option>
					<option value="15"<?php if($dd == "15") echo(' selected="selected"'); ?>>15</option>
					<option value="16"<?php if($dd == "16") echo(' selected="selected"'); ?>>16</option>
					<option value="17"<?php if($dd == "17") echo(' selected="selected"'); ?>>17</option>
					<option value="18"<?php if($dd == "18") echo(' selected="selected"'); ?>>18</option>
					<option value="19"<?php if($dd == "19") echo(' selected="selected"'); ?>>19</option>
					<option value="20"<?php if($dd == "20") echo(' selected="selected"'); ?>>20</option>
					<option value="21"<?php if($dd == "21") echo(' selected="selected"'); ?>>21</option>
					<option value="22"<?php if($dd == "22") echo(' selected="selected"'); ?>>22</option>
					<option value="23"<?php if($dd == "23") echo(' selected="selected"'); ?>>23</option>
					<option value="24"<?php if($dd == "24") echo(' selected="selected"'); ?>>24</option>
					<option value="25"<?php if($dd == "25") echo(' selected="selected"'); ?>>25</option>
					<option value="26"<?php if($dd == "26") echo(' selected="selected"'); ?>>26</option>
					<option value="27"<?php if($dd == "27") echo(' selected="selected"'); ?>>27</option>
					<option value="28"<?php if($dd == "28") echo(' selected="selected"'); ?>>28</option>
					<option value="29"<?php if($dd == "29") echo(' selected="selected"'); ?>>29</option>
					<option value="30"<?php if($dd == "30") echo(' selected="selected"'); ?>>30</option>
					<option value="31"<?php if($dd == "31") echo(' selected="selected"'); ?>>31</option>
				  </select>
				  <select name="gp_yy" id="gp_yy">
						<option value="1960"<?php if($yy == "1960") echo(' selected="selected"'); ?>>1960</option>
						<option value="1961"<?php if($yy == "1961") echo(' selected="selected"'); ?>>1961</option>
						<option value="1962"<?php if($yy == "1962") echo(' selected="selected"'); ?>>1962</option>
						<option value="1963"<?php if($yy == "1963") echo(' selected="selected"'); ?>>1963</option>
						<option value="1964"<?php if($yy == "1964") echo(' selected="selected"'); ?>>1964</option>
						<option value="1965"<?php if($yy == "1965") echo(' selected="selected"'); ?>>1965</option>
						<option value="1966"<?php if($yy == "1966") echo(' selected="selected"'); ?>>1966</option>
						<option value="1967"<?php if($yy == "1967") echo(' selected="selected"'); ?>>1967</option>
						<option value="1968"<?php if($yy == "1968") echo(' selected="selected"'); ?>>1968</option>
						<option value="1969"<?php if($yy == "1969") echo(' selected="selected"'); ?>>1969</option>
						<option value="1970"<?php if($yy == "1970") echo(' selected="selected"'); ?>>1970</option>
						<option value="1971"<?php if($yy == "1971") echo(' selected="selected"'); ?>>1971</option>
						<option value="1972"<?php if($yy == "1972") echo(' selected="selected"'); ?>>1972</option>
						<option value="1973"<?php if($yy == "1973") echo(' selected="selected"'); ?>>1973</option>
						<option value="1974"<?php if($yy == "1974") echo(' selected="selected"'); ?>>1974</option>
						<option value="1975"<?php if($yy == "1975") echo(' selected="selected"'); ?>>1975</option>
						<option value="1976"<?php if($yy == "1976") echo(' selected="selected"'); ?>>1976</option>
						<option value="1977"<?php if($yy == "1977") echo(' selected="selected"'); ?>>1977</option>
						<option value="1978"<?php if($yy == "1978") echo(' selected="selected"'); ?>>1978</option>
						<option value="1979"<?php if($yy == "1979") echo(' selected="selected"'); ?>>1979</option>
						<option value="1980"<?php if($yy == "1980") echo(' selected="selected"'); ?>>1980</option>
						<option value="1981"<?php if($yy == "1981") echo(' selected="selected"'); ?>>1981</option>
						<option value="1982"<?php if($yy == "1982") echo(' selected="selected"'); ?>>1982</option>
						<option value="1983"<?php if($yy == "1983") echo(' selected="selected"'); ?>>1983</option>
						<option value="1984"<?php if($yy == "1984") echo(' selected="selected"'); ?>>1984</option>
						<option value="1985"<?php if($yy == "1985") echo(' selected="selected"'); ?>>1985</option>
						<option value="1986"<?php if($yy == "1986") echo(' selected="selected"'); ?>>1986</option>
						<option value="1987"<?php if($yy == "1987") echo(' selected="selected"'); ?>>1987</option>
						<option value="1988"<?php if($yy == "1988") echo(' selected="selected"'); ?>>1988</option>
						<option value="1989"<?php if($yy == "1989") echo(' selected="selected"'); ?>>1989</option>
						<option value="1990"<?php if($yy == "1990") echo(' selected="selected"'); ?>>1990</option>
						<option value="1991"<?php if($yy == "1991") echo(' selected="selected"'); ?>>1991</option>
						<option value="1992"<?php if($yy == "1992") echo(' selected="selected"'); ?>>1992</option>
						<option value="1993"<?php if($yy == "1993") echo(' selected="selected"'); ?>>1993</option>
						<option value="1994"<?php if($yy == "1994") echo(' selected="selected"'); ?>>1994</option>
						<option value="1995"<?php if($yy == "1995") echo(' selected="selected"'); ?>>1995</option>
						<option value="1996"<?php if($yy == "1996") echo(' selected="selected"'); ?>>1996</option>
						<option value="1997"<?php if($yy == "1997") echo(' selected="selected"'); ?>>1997</option>
						<option value="1998"<?php if($yy == "1998") echo(' selected="selected"'); ?>>1998</option>
						<option value="1999"<?php if($yy == "1999") echo(' selected="selected"'); ?>>1999</option>
						<option value="2000"<?php if($yy == "2000") echo(' selected="selected"'); ?>>2000</option>
						<option value="2001"<?php if($yy == "2001") echo(' selected="selected"'); ?>>2001</option>
						<option value="2002"<?php if($yy == "2002") echo(' selected="selected"'); ?>>2002</option>
						<option value="2003"<?php if($yy == "2003") echo(' selected="selected"'); ?>>2003</option>
						<option value="2004"<?php if($yy == "2004") echo(' selected="selected"'); ?>>2004</option>
						<option value="2005"<?php if($yy == "2005") echo(' selected="selected"'); ?>>2005</option>
						<option value="2006"<?php if($yy == "2006") echo(' selected="selected"'); ?>>2006</option>
						<option value="2007"<?php if($yy == "2007") echo(' selected="selected"'); ?>>2007</option>
						<option value="2008"<?php if($yy == "2008") echo(' selected="selected"'); ?>>2008</option>
						<option value="2009"<?php if($yy == "2009") echo(' selected="selected"'); ?>>2009</option>
						<option value="2010"<?php if($yy == "2010") echo(' selected="selected"'); ?>>2010</option>
						<option value="2011"<?php if($yy == "2011") echo(' selected="selected"'); ?>>2011</option>
						<option value="2012"<?php if($yy == "2012") echo(' selected="selected"'); ?>>2012</option>

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
						<option value="01"<?php if($exp_dd == "01") echo(' selected="selected"'); ?>>01</option>
						<option value="02"<?php if($exp_dd == "02") echo(' selected="selected"'); ?>>02</option>
						<option value="03"<?php if($exp_dd == "03") echo(' selected="selected"'); ?>>03</option>
						<option value="04"<?php if($exp_dd == "04") echo(' selected="selected"'); ?>>04</option>
						<option value="05"<?php if($exp_dd == "05") echo(' selected="selected"'); ?>>05</option>
						<option value="06"<?php if($exp_dd == "06") echo(' selected="selected"'); ?>>06</option>
						<option value="07"<?php if($exp_dd == "07") echo(' selected="selected"'); ?>>07</option>
						<option value="08"<?php if($exp_dd == "08") echo(' selected="selected"'); ?>>08</option>
						<option value="09"<?php if($exp_dd == "09") echo(' selected="selected"'); ?>>09</option>
						<option value="10"<?php if($exp_dd == "10") echo(' selected="selected"'); ?>>10</option>
						<option value="11"<?php if($exp_dd == "11") echo(' selected="selected"'); ?>>11</option>
						<option value="12"<?php if($exp_dd == "12") echo(' selected="selected"'); ?>>12</option>
						<option value="13"<?php if($exp_dd == "13") echo(' selected="selected"'); ?>>13</option>
						<option value="14"<?php if($exp_dd == "14") echo(' selected="selected"'); ?>>14</option>
						<option value="15"<?php if($exp_dd == "15") echo(' selected="selected"'); ?>>15</option>
						<option value="16"<?php if($exp_dd == "16") echo(' selected="selected"'); ?>>16</option>
						<option value="17"<?php if($exp_dd == "17") echo(' selected="selected"'); ?>>17</option>
						<option value="18"<?php if($exp_dd == "18") echo(' selected="selected"'); ?>>18</option>
						<option value="19"<?php if($exp_dd == "19") echo(' selected="selected"'); ?>>19</option>
						<option value="20"<?php if($exp_dd == "20") echo(' selected="selected"'); ?>>20</option>
						<option value="21"<?php if($exp_dd == "21") echo(' selected="selected"'); ?>>21</option>
						<option value="22"<?php if($exp_dd == "22") echo(' selected="selected"'); ?>>22</option>
						<option value="23"<?php if($exp_dd == "23") echo(' selected="selected"'); ?>>23</option>
						<option value="24"<?php if($exp_dd == "24") echo(' selected="selected"'); ?>>24</option>
						<option value="25"<?php if($exp_dd == "25") echo(' selected="selected"'); ?>>25</option>
						<option value="26"<?php if($exp_dd == "26") echo(' selected="selected"'); ?>>26</option>
						<option value="27"<?php if($exp_dd == "27") echo(' selected="selected"'); ?>>27</option>
						<option value="28"<?php if($exp_dd == "28") echo(' selected="selected"'); ?>>28</option>
						<option value="29"<?php if($exp_dd == "29") echo(' selected="selected"'); ?>>29</option>
						<option value="30"<?php if($exp_dd == "30") echo(' selected="selected"'); ?>>30</option>
						<option value="31"<?php if($exp_dd == "31") echo(' selected="selected"'); ?>>31</option>
					  </select>
					  <select name="exp_yy" id="exp_yy">
						<option value="1960"<?php if($exp_yy == "1960") echo(' selected="selected"'); ?>>1960</option>
						<option value="1961"<?php if($exp_yy == "1961") echo(' selected="selected"'); ?>>1961</option>
						<option value="1962"<?php if($exp_yy == "1962") echo(' selected="selected"'); ?>>1962</option>
						<option value="1963"<?php if($exp_yy == "1963") echo(' selected="selected"'); ?>>1963</option>
						<option value="1964"<?php if($exp_yy == "1964") echo(' selected="selected"'); ?>>1964</option>
						<option value="1965"<?php if($exp_yy == "1965") echo(' selected="selected"'); ?>>1965</option>
						<option value="1966"<?php if($exp_yy == "1966") echo(' selected="selected"'); ?>>1966</option>
						<option value="1967"<?php if($exp_yy == "1967") echo(' selected="selected"'); ?>>1967</option>
						<option value="1968"<?php if($exp_yy == "1968") echo(' selected="selected"'); ?>>1968</option>
						<option value="1969"<?php if($exp_yy == "1969") echo(' selected="selected"'); ?>>1969</option>
						<option value="1970"<?php if($exp_yy == "1970") echo(' selected="selected"'); ?>>1970</option>
						<option value="1971"<?php if($exp_yy == "1971") echo(' selected="selected"'); ?>>1971</option>
						<option value="1972"<?php if($exp_yy == "1972") echo(' selected="selected"'); ?>>1972</option>
						<option value="1973"<?php if($exp_yy == "1973") echo(' selected="selected"'); ?>>1973</option>
						<option value="1974"<?php if($exp_yy == "1974") echo(' selected="selected"'); ?>>1974</option>
						<option value="1975"<?php if($exp_yy == "1975") echo(' selected="selected"'); ?>>1975</option>
						<option value="1976"<?php if($exp_yy == "1976") echo(' selected="selected"'); ?>>1976</option>
						<option value="1977"<?php if($exp_yy == "1977") echo(' selected="selected"'); ?>>1977</option>
						<option value="1978"<?php if($exp_yy == "1978") echo(' selected="selected"'); ?>>1978</option>
						<option value="1979"<?php if($exp_yy == "1979") echo(' selected="selected"'); ?>>1979</option>
						<option value="1980"<?php if($exp_yy == "1980") echo(' selected="selected"'); ?>>1980</option>
						<option value="1981"<?php if($exp_yy == "1981") echo(' selected="selected"'); ?>>1981</option>
						<option value="1982"<?php if($exp_yy == "1982") echo(' selected="selected"'); ?>>1982</option>
						<option value="1983"<?php if($exp_yy == "1983") echo(' selected="selected"'); ?>>1983</option>
						<option value="1984"<?php if($exp_yy == "1984") echo(' selected="selected"'); ?>>1984</option>
						<option value="1985"<?php if($exp_yy == "1985") echo(' selected="selected"'); ?>>1985</option>
						<option value="1986"<?php if($exp_yy == "1986") echo(' selected="selected"'); ?>>1986</option>
						<option value="1987"<?php if($exp_yy == "1987") echo(' selected="selected"'); ?>>1987</option>
						<option value="1988"<?php if($exp_yy == "1988") echo(' selected="selected"'); ?>>1988</option>
						<option value="1989"<?php if($exp_yy == "1989") echo(' selected="selected"'); ?>>1989</option>
						<option value="1990"<?php if($exp_yy == "1990") echo(' selected="selected"'); ?>>1990</option>
						<option value="1991"<?php if($exp_yy == "1991") echo(' selected="selected"'); ?>>1991</option>
						<option value="1992"<?php if($exp_yy == "1992") echo(' selected="selected"'); ?>>1992</option>
						<option value="1993"<?php if($exp_yy == "1993") echo(' selected="selected"'); ?>>1993</option>
						<option value="1994"<?php if($exp_yy == "1994") echo(' selected="selected"'); ?>>1994</option>
						<option value="1995"<?php if($exp_yy == "1995") echo(' selected="selected"'); ?>>1995</option>
						<option value="1996"<?php if($exp_yy == "1996") echo(' selected="selected"'); ?>>1996</option>
						<option value="1997"<?php if($exp_yy == "1997") echo(' selected="selected"'); ?>>1997</option>
						<option value="1998"<?php if($exp_yy == "1998") echo(' selected="selected"'); ?>>1998</option>
						<option value="1999"<?php if($exp_yy == "1999") echo(' selected="selected"'); ?>>1999</option>
						<option value="2000"<?php if($exp_yy == "2000") echo(' selected="selected"'); ?>>2000</option>
						<option value="2001"<?php if($exp_yy == "2001") echo(' selected="selected"'); ?>>2001</option>
						<option value="2002"<?php if($exp_yy == "2002") echo(' selected="selected"'); ?>>2002</option>
						<option value="2003"<?php if($exp_yy == "2003") echo(' selected="selected"'); ?>>2003</option>
						<option value="2004"<?php if($exp_yy == "2004") echo(' selected="selected"'); ?>>2004</option>
						<option value="2005"<?php if($exp_yy == "2005") echo(' selected="selected"'); ?>>2005</option>
						<option value="2006"<?php if($exp_yy == "2006") echo(' selected="selected"'); ?>>2006</option>
						<option value="2007"<?php if($exp_yy == "2007") echo(' selected="selected"'); ?>>2007</option>
						<option value="2008"<?php if($exp_yy == "2008") echo(' selected="selected"'); ?>>2008</option>
						<option value="2009"<?php if($exp_yy == "2009") echo(' selected="selected"'); ?>>2009</option>
						<option value="2010"<?php if($exp_yy == "2010") echo(' selected="selected"'); ?>>2010</option>
						<option value="2011"<?php if($exp_yy == "2011") echo(' selected="selected"'); ?>>2011</option>
						<option value="2012"<?php if($exp_yy == "2012") echo(' selected="selected"'); ?>>2012</option>
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
				<td><select name="show_country" id="show_country">
						<option value="AU"<?php if($country == "AU") echo(' selected="selected"'); ?>>Australia</option>
						<option value="AT"<?php if($country == "AT") echo(' selected="selected"'); ?>>Austria</option>
						<option value="BR"<?php if($country == "BR") echo(' selected="selected"'); ?>>Brazil</option>
						<option value="CA"<?php if($country == "CA") echo(' selected="selected"'); ?>>Canada</option>
						<option value="DK"<?php if($country == "DK") echo(' selected="selected"'); ?>>Denmark</option>
						<option value="FR"<?php if($country == "FR") echo(' selected="selected"'); ?>>France</option>
						<option value="DE"<?php if($country == "DE") echo(' selected="selected"'); ?>>Germany</option>
						<option value="IT"<?php if($country == "IT") echo(' selected="selected"'); ?>>Italy</option>
						<option value="JP"<?php if($country == "JP") echo(' selected="selected"'); ?>>Japan</option>
						<option value="MX"<?php if($country == "MX") echo(' selected="selected"'); ?>>Mexico</option>
						<option value="ES"<?php if($country == "ES") echo(' selected="selected"'); ?>>Spain</option>
						<option value="SE"<?php if($country == "SE") echo(' selected="selected"'); ?>>Sweden</option>
						<option value="CH"<?php if($country == "CH") echo(' selected="selected"'); ?>>Switzerland</option>
						<option value="US"<?php if($country == "US") echo(' selected="selected"'); ?>>United States</option>
						<option value="GB"<?php if($country == "GB") echo(' selected="selected"'); ?>>United Kingdom</option>
						<option value="">-------------</option>
						<option value="AF"<?php if($country == "AF") echo(' selected="selected"'); ?>>Afghanistan</option>
						<option value="AG"<?php if($country == "AG") echo(' selected="selected"'); ?>>Antigua and Barbuda</option>
						<option value="AI"<?php if($country == "AI") echo(' selected="selected"'); ?>>Anguilla</option>
						<option value="AL"<?php if($country == "AL") echo(' selected="selected"'); ?>>Albania</option>
						<option value="AM"<?php if($country == "AM") echo(' selected="selected"'); ?>>Armenia</option>
						<option value="DZ"<?php if($country == "DZ") echo(' selected="selected"'); ?>>Algeria</option>
						<option value="AD"<?php if($country == "AD") echo(' selected="selected"'); ?>>Andorra</option>
						<option value="AO"<?php if($country == "AO") echo(' selected="selected"'); ?>>Angola</option>
						<option value="AR"<?php if($country == "AR") echo(' selected="selected"'); ?>>Argentina</option>
						<option value="AW"<?php if($country == "AW") echo(' selected="selected"'); ?>>Aruba</option>
						<option value="BS"<?php if($country == "BS") echo(' selected="selected"'); ?>>Bahamas</option>
						<option value="BH"<?php if($country == "BH") echo(' selected="selected"'); ?>>Bahrain</option>
						<option value="BD"<?php if($country == "BD") echo(' selected="selected"'); ?>>Bangladesh</option>
						<option value="BB"<?php if($country == "BB") echo(' selected="selected"'); ?>>Barbados</option>
						<option value="BY"<?php if($country == "BY") echo(' selected="selected"'); ?>>Belarus</option>
						<option value="BE"<?php if($country == "BE") echo(' selected="selected"'); ?>>Belgium</option>
						<option value="BZ"<?php if($country == "BZ") echo(' selected="selected"'); ?>>Belize</option>
						<option value="BM"<?php if($country == "BM") echo(' selected="selected"'); ?>>Bermuda</option>
						<option value="BT"<?php if($country == "BT") echo(' selected="selected"'); ?>>Bhutan</option>
						<option value="BO"<?php if($country == "BO") echo(' selected="selected"'); ?>>Bolivia</option>
						<option value="BA"<?php if($country == "BA") echo(' selected="selected"'); ?>>Bosnia and Herzegovina</option>
						<option value="BW"<?php if($country == "BW") echo(' selected="selected"'); ?>>Botswana</option>
						<option value="BN"<?php if($country == "BN") echo(' selected="selected"'); ?>>Brunei</option>
						<option value="BG"<?php if($country == "BG") echo(' selected="selected"'); ?>>Bulgaria</option>
						<option value="BI"<?php if($country == "BI") echo(' selected="selected"'); ?>>Burundi</option>
						<option value="KH"<?php if($country == "KH") echo(' selected="selected"'); ?>>Cambodia</option>
						<option value="CV"<?php if($country == "CV") echo(' selected="selected"'); ?>>Cape Verde</option>
						<option value="KY"<?php if($country == "KY") echo(' selected="selected"'); ?>>Cayman Islands</option>
						<option value="CF"<?php if($country == "CF") echo(' selected="selected"'); ?>>Central African Republic</option>
						<option value="CL"<?php if($country == "CL") echo(' selected="selected"'); ?>>Chile</option>
						<option value="CN"<?php if($country == "CN") echo(' selected="selected"'); ?>>China</option>
						<option value="CO"<?php if($country == "CO") echo(' selected="selected"'); ?>>Colombia</option>
						<option value="KM"<?php if($country == "KM") echo(' selected="selected"'); ?>>Comoros</option>
						<option value="CR"<?php if($country == "CR") echo(' selected="selected"'); ?>>Costa Rica</option>
						<option value="HR"<?php if($country == "HR") echo(' selected="selected"'); ?>>Croatia</option>
						<option value="CU"<?php if($country == "CU") echo(' selected="selected"'); ?>>Cuba</option>
						<option value="CY"<?php if($country == "CY") echo(' selected="selected"'); ?>>Cyprus</option>
						<option value="CZ"<?php if($country == "CZ") echo(' selected="selected"'); ?>>Czech Republic</option>
						<option value="DJ"<?php if($country == "DJ") echo(' selected="selected"'); ?>>Djibouti</option>
						<option value="DO"<?php if($country == "DO") echo(' selected="selected"'); ?>>Dominican Republic</option>
						<option value="EC"<?php if($country == "EC") echo(' selected="selected"'); ?>>Ecuador</option>
						<option value="EG"<?php if($country == "EG") echo(' selected="selected"'); ?>>Egypt</option>
						<option value="SV"<?php if($country == "SV") echo(' selected="selected"'); ?>>El Salvador</option>
						<option value="EE"<?php if($country == "EE") echo(' selected="selected"'); ?>>Estonia</option>
						<option value="ET"<?php if($country == "ET") echo(' selected="selected"'); ?>>Ethiopia</option>
						<option value="FK"<?php if($country == "FK") echo(' selected="selected"'); ?>>Falkland Islands</option>
						<option value="FJ"<?php if($country == "FJ") echo(' selected="selected"'); ?>>Fiji</option>
						<option value="FI"<?php if($country == "FI") echo(' selected="selected"'); ?>>Finland</option>
						<option value="GM"<?php if($country == "GM") echo(' selected="selected"'); ?>>Gambia</option>
						<option value="GH"<?php if($country == "GH") echo(' selected="selected"'); ?>>Ghana</option>
						<option value="GI"<?php if($country == "GI") echo(' selected="selected"'); ?>>Gibraltar</option>
						<option value="GR"<?php if($country == "GR") echo(' selected="selected"'); ?>>Greece</option>
						<option value="GT"<?php if($country == "GT") echo(' selected="selected"'); ?>>Guatemala</option>
						<option value="GN"<?php if($country == "GN") echo(' selected="selected"'); ?>>Guinea</option>
						<option value="GY"<?php if($country == "GY") echo(' selected="selected"'); ?>>Guyana</option>
						<option value="HT"<?php if($country == "HT") echo(' selected="selected"'); ?>>Haiti</option>
						<option value="HN"<?php if($country == "HN") echo(' selected="selected"'); ?>>Honduras</option>
						<option value="HK"<?php if($country == "HK") echo(' selected="selected"'); ?>>Hong Kong</option>
						<option value="HU"<?php if($country == "HU") echo(' selected="selected"'); ?>>Hungary</option>
						<option value="IS"<?php if($country == "IS") echo(' selected="selected"'); ?>>Iceland</option>
						<option value="IN"<?php if($country == "IN") echo(' selected="selected"'); ?>>India</option>
						<option value="ID"<?php if($country == "ID") echo(' selected="selected"'); ?>>Indonesia</option>
						<option value="IR"<?php if($country == "IR") echo(' selected="selected"'); ?>>Iran</option>
						<option value="IQ"<?php if($country == "IQ") echo(' selected="selected"'); ?>>Iraq</option>
						<option value="IE"<?php if($country == "IE") echo(' selected="selected"'); ?>>Ireland</option>
						<option value="IL"<?php if($country == "IL") echo(' selected="selected"'); ?>>Israel</option>
						<option value="JM"<?php if($country == "JM") echo(' selected="selected"'); ?>>Jamaica</option>
						<option value="JO"<?php if($country == "JO") echo(' selected="selected"'); ?>>Jordan</option>
						<option value="KZ"<?php if($country == "KZ") echo(' selected="selected"'); ?>>Kazakhstan</option>
						<option value="KE"<?php if($country == "KE") echo(' selected="selected"'); ?>>Kenya</option>
						<option value="KW"<?php if($country == "KW") echo(' selected="selected"'); ?>>Kuwait</option>
						<option value="LA"<?php if($country == "LA") echo(' selected="selected"'); ?>>Laos</option>
						<option value="LV"<?php if($country == "LV") echo(' selected="selected"'); ?>>Latvia</option>
						<option value="LB"<?php if($country == "LB") echo(' selected="selected"'); ?>>Lebanon</option>
						<option value="LS"<?php if($country == "LS") echo(' selected="selected"'); ?>>Lesotho</option>
						<option value="LR"<?php if($country == "LR") echo(' selected="selected"'); ?>>Liberia</option>
						<option value="LY"<?php if($country == "LY") echo(' selected="selected"'); ?>>Libya</option>
						<option value="LT"<?php if($country == "LT") echo(' selected="selected"'); ?>>Lithuania</option>
						<option value="LU"<?php if($country == "LU") echo(' selected="selected"'); ?>>Luxembourg</option>
						<option value="MO"<?php if($country == "MO") echo(' selected="selected"'); ?>>Macao</option>
						<option value="MG"<?php if($country == "MG") echo(' selected="selected"'); ?>>Madagascar</option>
						<option value="MW"<?php if($country == "MW") echo(' selected="selected"'); ?>>Malawi</option>
						<option value="MY"<?php if($country == "MY") echo(' selected="selected"'); ?>>Malaysia</option>
						<option value="MV"<?php if($country == "MV") echo(' selected="selected"'); ?>>Maldives</option>
						<option value="MT"<?php if($country == "MT") echo(' selected="selected"'); ?>>Malta</option>
						<option value="MR"<?php if($country == "MR") echo(' selected="selected"'); ?>>Mauritania</option>
						<option value="MU"<?php if($country == "MU") echo(' selected="selected"'); ?>>Mauritius</option>
						<option value="MN"<?php if($country == "MN") echo(' selected="selected"'); ?>>Mongolia</option>
						<option value="MC"<?php if($country == "MC") echo(' selected="selected"'); ?>>Monaco</option>
						<option value="ME"<?php if($country == "ME") echo(' selected="selected"'); ?>>Montenegro</option>
						<option value="MA"<?php if($country == "MA") echo(' selected="selected"'); ?>>Morocco</option>
						<option value="MZ"<?php if($country == "MZ") echo(' selected="selected"'); ?>>Mozambique</option>
						<option value="MM"<?php if($country == "MM") echo(' selected="selected"'); ?>>Myanmar</option>
						<option value="NA"<?php if($country == "NA") echo(' selected="selected"'); ?>>Namibia</option>
						<option value="NP"<?php if($country == "NP") echo(' selected="selected"'); ?>>Nepal</option>
						<option value="NL"<?php if($country == "NL") echo(' selected="selected"'); ?>>Netherlands</option>
						<option value="AN"<?php if($country == "AN") echo(' selected="selected"'); ?>>Netherlands Antilles</option>
						<option value="NZ"<?php if($country == "NZ") echo(' selected="selected"'); ?>>New Zealand</option>
						<option value="NI"<?php if($country == "NI") echo(' selected="selected"'); ?>>Nicaragua</option>
						<option value="NG"<?php if($country == "NG") echo(' selected="selected"'); ?>>Nigeria</option>
						<option value="KP"<?php if($country == "KP") echo(' selected="selected"'); ?>>North Korea</option>
						<option value="NO"<?php if($country == "NO") echo(' selected="selected"'); ?>>Norway</option>
						<option value="OM"<?php if($country == "OM") echo(' selected="selected"'); ?>>Oman</option>
						<option value="PK"<?php if($country == "PK") echo(' selected="selected"'); ?>>Pakistan</option>
						<option value="PA"<?php if($country == "PA") echo(' selected="selected"'); ?>>Panama</option>
						<option value="PG"<?php if($country == "PG") echo(' selected="selected"'); ?>>Papua New Guinea</option>
						<option value="PY"<?php if($country == "PY") echo(' selected="selected"'); ?>>Paraguay</option>
						<option value="PE"<?php if($country == "PE") echo(' selected="selected"'); ?>>Peru</option>
						<option value="PH"<?php if($country == "PH") echo(' selected="selected"'); ?>>Philippines</option>
						<option value="PL"<?php if($country == "PL") echo(' selected="selected"'); ?>>Poland</option>
						<option value="PT"<?php if($country == "PT") echo(' selected="selected"'); ?>>Portugal</option>
						<option value="QA"<?php if($country == "QA") echo(' selected="selected"'); ?>>Qatar</option>
						<option value="RO"<?php if($country == "RO") echo(' selected="selected"'); ?>>Romania</option>
						<option value="RU"<?php if($country == "RU") echo(' selected="selected"'); ?>>Russia</option>
						<option value="WS"<?php if($country == "WS") echo(' selected="selected"'); ?>>Samoa</option>
						<option value="ST"<?php if($country == "ST") echo(' selected="selected"'); ?>>Sao Tome/Principe</option>
						<option value="SA"<?php if($country == "SA") echo(' selected="selected"'); ?>>Saudi Arabia</option>
						<option value="RS"<?php if($country == "RS") echo(' selected="selected"'); ?>>Serbia</option>
						<option value="SC"<?php if($country == "SC") echo(' selected="selected"'); ?>>Seychelles</option>
						<option value="SL"<?php if($country == "SL") echo(' selected="selected"'); ?>>Sierra Leone</option>
						<option value="SG"<?php if($country == "SG") echo(' selected="selected"'); ?>>Singapore</option>
						<option value="SK"<?php if($country == "SK") echo(' selected="selected"'); ?>>Slovakia</option>
						<option value="SI"<?php if($country == "SI") echo(' selected="selected"'); ?>>Slovenia</option>
						<option value="SB"<?php if($country == "SB") echo(' selected="selected"'); ?>>Solomon Islands</option>
						<option value="SO"<?php if($country == "SO") echo(' selected="selected"'); ?>>Somalia</option>
						<option value="ZA"<?php if($country == "ZA") echo(' selected="selected"'); ?>>South Africa</option>
						<option value="KR"<?php if($country == "KR") echo(' selected="selected"'); ?>>South Korea</option>
						<option value="LK"<?php if($country == "LK") echo(' selected="selected"'); ?>>Sri Lanka</option>
						<option value="SH"<?php if($country == "SH") echo(' selected="selected"'); ?>>Saint Helena</option>
						<option value="SD"<?php if($country == "SD") echo(' selected="selected"'); ?>>Sudan</option>
						<option value="SR"<?php if($country == "SR") echo(' selected="selected"'); ?>>Suriname</option>
						<option value="SZ"<?php if($country == "SZ") echo(' selected="selected"'); ?>>Swaziland</option>
						<option value="SY"<?php if($country == "SY") echo(' selected="selected"'); ?>>Syria</option>
						<option value="TW"<?php if($country == "TW") echo(' selected="selected"'); ?>>Taiwan</option>
						<option value="TZ"<?php if($country == "TZ") echo(' selected="selected"'); ?>>Tanzania</option>
						<option value="TH"<?php if($country == "TH") echo(' selected="selected"'); ?>>Thailand</option>
						<option value="TO"<?php if($country == "TO") echo(' selected="selected"'); ?>>Tonga</option>
						<option value="TT"<?php if($country == "TT") echo(' selected="selected"'); ?>>Trinidad/Tobago</option>
						<option value="TN"<?php if($country == "TN") echo(' selected="selected"'); ?>>Tunisia</option>
						<option value="TR"<?php if($country == "TR") echo(' selected="selected"'); ?>>Turkey</option>
						<option value="UG"<?php if($country == "UG") echo(' selected="selected"'); ?>>Uganda</option>
						<option value="UA"<?php if($country == "UA") echo(' selected="selected"'); ?>>Ukraine</option>
						<option value="UY"<?php if($country == "UY") echo(' selected="selected"'); ?>>Uruguay</option>
						<option value="AE"<?php if($country == "AE") echo(' selected="selected"'); ?>>United Arab Emirates</option>
						<option value="VU"<?php if($country == "VU") echo(' selected="selected"'); ?>>Vanuatu</option>
						<option value="VE"<?php if($country == "VE") echo(' selected="selected"'); ?>>Venezuela</option>
						<option value="VN"<?php if($country == "VN") echo(' selected="selected"'); ?>>Vietnam</option>
						<option value="ZM"<?php if($country == "ZM") echo(' selected="selected"'); ?>>Zambia</option>
						<option value="ZW"<?php if($country == "ZW") echo(' selected="selected"'); ?>>Zimbabwe</option>
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