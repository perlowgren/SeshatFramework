

(function($) {

	$.extend({
/*		sqlExecQuery: function() {
			$.ajax({
				url: 'wiki-save?pid='+$.wikiPID+'&name='+$.wikiPage,
				method: 'POST',
				data: $('text').value(),
				success: function(data) {
					$.wikiBoxOpen('message',data);
				},
				fail: function(status,message) {
					$.wikiBoxOpen('message',message);
				}
			});
		}*/
		sqlExecQuery: function() { $('#sql-form').submit(); }
	});

	$(function() {
		$('sql').expandingTextarea();
		$('#sql-exec-query').attr('type','button').click($.sqlExecQuery);
	});

})($seshat);


