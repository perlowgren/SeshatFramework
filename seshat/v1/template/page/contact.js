

(function($) {

	$.extend({
		sendMessage: function() {
			var name = $('name').value();
			var email = $('email').value();
			var cc = $('cc').value()? '&cc' : '';
			var message = $('message').value();
			name = encodeURIComponent(name);
			email = encodeURIComponent(email);
			$.ajax({
				url: 'contact?name='+name+'&email='+email+cc,
				method: 'POST',
				accept: 'json',
				data: message,
				success: function(data) {
					$('message').value('');
					$.contactMessageBox(data.message,'success',3000);
				},
				fail: function(status,data) {
					$.contactMessageBox(data.error,'fail',3000);
				}
			});
		},

		contactMessageBox: function(message,type,tm) {
			var cl = 'contact-message';
			if(type) cl = cl+' '+type;
			$('#contact-message').openMessageBox('contact-message-box',message,{class:cl},tm);
		}
	});

	$(function() {
		$('message').expandingTextarea();
		$('#send-message').click($.sendMessage);
	});

})($seshat);


