<?php

function gigpress_export() {

	global $wpdb;
	global $gigpress;
	$gpo = get_option('gigpress_settings');
	
	?>

	<div class="wrap gigpress <?php echo $gigpress['admin_class']; ?> gp-options">

	<?php if ( function_exists('screen_icon') ) screen_icon('gigpress'); ?>			
	<h2><?php _e("Export", "gigpress"); ?></h2>
	
	<p><?php _e("Download your complete show database as a tab-separated CSV file (compatible with programs such as Microsoft Excel.)", "gigpress"); ?></p>
	
	<?php
		$download = WP_PLUGIN_URL . "/gigpress/gp-export.php";
		$download = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($download, 'gigpress-action') : $download;
		$dl_link = __("Download file.", "gigpress");
		echo("<p class=\"gigpress-download\"><a href=\"$download\">$dl_link</a></p>");
	?>

	</div>
<?php }