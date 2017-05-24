function confl(message, url) {
	if(confirm(message)) location.href = url;
}

$(document).ready(function() {
	$(".menu .deleteIcon").easyconfirm({ locale: { title: 'Upozornenie', button: ['No','Yes']}});
});

$(function() {
	$("a.confirm-ajax").live('click', function(e) {
		e.preventDefault();
		var href = this.href;
		var event = e;
		bootbox.confirm("Naozaj chcete položku vymazať?", function(confirmed) {
			if(confirmed){
				$.nette.ajax(href);
				// $.post(href, $.nette.success);

				// $.nette.spinner.css({
				// 	position: 'absolute',
				// 	left: event.pageX,
				// 	top: event.pageY
				// });
			}
		});
	});
	
	
	
});