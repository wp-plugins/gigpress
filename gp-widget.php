<?php

function gigpress_widget_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;
		
	function gigpress_widget($args) {
		
		extract($args);
		$gpo = get_option('gigpress_settings');
	
		$title = gigpress_sanitize($gpo['widget_heading']);
		$number = $gpo['widget_number'];
		$segment = $gpo['widget_segment'];
	
		echo $before_widget . $before_title . $title . $after_title;
		
		// Call our sidebar function
		gigpress_sidebar($number, $segment);
		
		echo $after_widget;
	}
	
	function gigpress_widget_control() {
	
		$gpo = get_option('gigpress_settings');
		
		if ( $_POST['gigpress_widget_submit'] ) {
			$gpo['widget_heading'] = strip_tags(stripslashes($_POST['gigpress_widget_heading']));
			$gpo['widget_number'] = strip_tags(stripslashes($_POST['gigpress_widget_number']));
			$gpo['widget_segment'] = strip_tags(stripslashes($_POST['gigpress_widget_segment']));
			update_option('gigpress_settings', $gpo);
		}
				
		echo '<p><label for="gigpress_widget_heading">' . __('Title') . ': <input class="widefat" id="gigpress_widget_heading" name="gigpress_widget_heading" type="text" value="' . gigpress_sanitize($gpo['widget_heading']) . '" /></label></p>';
		echo '<p><label for="gigpress_widget_number">' . __('Number of shows to list', 'gigpress') . ': <input style="width: 25px; text-align: center;" id="gigpress_widget_number" name="gigpress_widget_number" type="text" value="' . $gpo['widget_number'] . '" /></label></p>';
		echo '<p><label for="gigpress_widget_segment"><input id="gigpress_widget_segment" name="gigpress_widget_segment" type="checkbox" value="1"';
		if ( $gpo['widget_segment'] == 1 ) echo ' checked="checked"';
		echo ' /> &nbsp; ' . __('Segment list into tours?', 'gigpress') . '</label></p>';
		echo '<input type="hidden" id="gigpress_widget_submit" name="gigpress_widget_submit" value="1" />';		
	
	}
	
	register_sidebar_widget(array('GigPress', 'widgets'), 'gigpress_widget');
	register_widget_control(array('GigPress', 'widgets'), 'gigpress_widget_control', 250, 100);
}