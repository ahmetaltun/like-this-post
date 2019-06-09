jQuery(document).ready(function($) {
	$('*[id=ltp-like-button]').each(function() {
		$(this).click(function() {
			var _btn = $(this);
			$.ajax({
				url: ltp_params.ajaxUrl,
				type: 'POST',
				cache: false,
				dataType: 'json',
				data: {
					action: 'ltpAddLike',
					postId: $(this).data('post-id'),
					userId: $(this).data('user-id')
				},
				beforeSend: function() {
					_btn.attr('disabled', true);
				},
				complete: function() {
					_btn.attr('disabled', false);
				},
				success: function(data) {
					console.log(data.status);
				},
				error: function(msg) {
					console.log(msg);
				}
			});
		});
	});
});
