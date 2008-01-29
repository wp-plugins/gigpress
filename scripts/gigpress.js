$(document).ready(function()
	{

		var time = $('select#hh option:selected').parent().attr('label');
		$('span#ampm').text(time);
		
		$('select#hh').change(function()
			{
				var time = $('select#hh option:selected').parent().attr('label');
				$('span#ampm').text(time);
			}
		);
		
		$('tr#expire.inactive').hide();
		
		$('input#multi').click(function()
			{
				$('tr#expire').toggle();
				this.blur();
			}
		);
	}
);