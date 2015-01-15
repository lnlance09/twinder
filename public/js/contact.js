$(document).ready(function() {
	var base_url = $('#base_url').text();

	$('#contact_form').submit(function(e) {
		e.preventDefault();
        var msg = $('textarea').val();
		
		if(msg != '') {
			$.ajax({
		        url : base_url +'contact/Send',
		        type: 'POST',
		        data : {
		            msg: msg
		        },
		        success: function(data) {
		        	console.log(data);
		            $('#contact_modal').modal('show');

		            $('#wipe_text').click(function() {
		            	$('textarea').val('');
		            });
		        }
		    });	 
		} else {
			$('#contact_form').effect('shake');
		}
	});
});