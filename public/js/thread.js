$(document).ready(function() {
	var base_url = $('#base_url').text();
	var match_id = $('#match_id').text();
	var match_type = $('#match_type').text();
	// console.log(match_id);
	// console.log(match_type); 

	var data = 'id='+ match_id;

	// Load the previous threads in the conversation
    $('#thread_load').load(base_url +'matches/ThreadBackend', data, function() {
    	$('.ajax-loader').fadeOut();
	});

    if(match_type == 'single') { 
    	// Send the message if the form is submitted
		$('form').submit(function(e) {
			e.preventDefault();
	        var msg = $('#msg_to_match').val();
	        // console.log(msg);

	        if(msg != '') {
		        $.ajax({
		            url: base_url +'users/SendMessage',
		            type: 'POST',
		            data: {
		                id: match_id,
		                msg: msg
		            },
		            success: function(data) {
		                // console.log(data);
		                var string = 'id='+ match_id +'&page=0';
		                $('#matches_load').load(base_url +'matches/ThreadBackend', string);
		            }
		        });
		   	} 
	    });	
	}
});