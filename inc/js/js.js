jQuery(document).ready(function($) {
	$('*[id=ltp-like-button]').each(function() {
		$(this).click(function() {
			alert(ltp_params.ajaxUrl);
		});
	});
});
