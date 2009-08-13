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
		
		$gp('tr#expire.inactive').hide();
		
		$gp('input#multi').click(function()
			{
				$gp('tr#expire').toggle();
				this.blur();
			}
		);
		
		if(!jQuery.browser.msie || (jQuery.browser.msie && jQuery.browser.version >= 8.0)) {
			// IE 7 chokes on this for some reason
			$gp('tr span.moreVenue').show();
			$gp('tbody#venueInfo').hide();
			
			$gp('tr span.moreVenue a').click(function()
				{
					$gp('tbody#venueInfo').toggle();
					return false;
				}
			);
		}			

		
		$gp('input.required').each(function(){
		
			$gp(this).blur(function(){
			  
			  var e = $gp(this);
			  
			  if (e.val() == "") {
				e.addClass("missing");
			  }
			  
			  if (e.val() != "") {
				e.removeClass("missing");
			  }	
			  		  
			});
			
		});
					
	}
);