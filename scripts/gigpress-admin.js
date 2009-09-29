$gp=jQuery.noConflict();

$gp(document).ready(function()
	{
		// If we're using the 12-hour clock, then do this magic
		if ( $gp('select#gp_hh.twelve').length > 0 ) {
			var time = $gp('select#gp_hh option:selected').parent().attr('label');
			$gp('span#ampm').text(time);
			
			$gp('select#gp_hh.twelve').change(function()
				{
					var time = $gp('select#gp_hh option:selected').parent().attr('label');
					$gp('span#ampm').text(time);
				}
			);
		}
		
		$gp('tr.gigpress-inactive, tbody.gigpress-inactive').hide();
		
		$gp('input#show_multi').click(function()
			{
				// $gp('tr#expire').toggle();
				// Workaround for IE 8 nonsense
				$gp('tr#expire').toggle($gp('tr#expire').css('display') == 'none');
				this.blur();
			}
		);
		
		$gp('select.can-add-new').change(function()
			{
				var scope = $gp(this);
				var target = $gp(this).attr('id') + '_new';
				if ( $gp('option:selected', scope).val() == 'new') {
					$gp('tbody#' + target).fadeIn();
				} else {
					$gp('tbody#' + target).fadeOut();
				}
			}
		);
					
	}
);