$(document).ready(function() {
	$('.ui-button').each(function(){
		var icon = $(this).attr('data-icon');
		var icon2 = $(this).attr('data-icon-secondary');
		$(this).button({
			icons: {primary: icon,secondary:icon2}
		});	
	});
});