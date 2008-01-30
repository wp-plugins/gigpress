$j=jQuery.noConflict();

$j(document).ready(function()
	{

		var time = $j('select#hh option:selected').parent().attr('label');
		$j('span#ampm').text(time);
		
		$j('select#hh').change(function()
			{
				var time = $j('select#hh option:selected').parent().attr('label');
				$j('span#ampm').text(time);
			}
		);
		
		$j('tr#expire.inactive').hide();
		
		$j('input#multi').click(function()
			{
				$j('tr#expire').toggle();
				this.blur();
			}
		);
	}
);