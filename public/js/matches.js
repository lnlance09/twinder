$(document).ready(function() {
	var base_url = $('#base_url').text();
	var match_id = $('#match_id').text();
	var match_type = $('#match_type').text();
	console.log(match_id);
	console.log(match_type);

	if(match_type ==  'all') {
		var url = base_url +'matches/MatchesBackend';
		var data = 'page=0';
	} else {
		var url = base_url +'matches/ThreadBackend';
		var data = 'id='+ match_id;
	}

    $('#matches_load').load(url, data, function() {
    	$('.ajax-loader').fadeOut();
	});

    if(match_type == 'single') { 
		$('#send_message').submit(function(e) {
	        var msg = $('#msg_to_match').val();
	        e.preventDefault();
	        // console.log(msg);

	        if(msg != '') {
		        $.ajax({
		            url : base_url +'users/SendMessage',
		            type: 'POST',
		            data : {
		                id: match_id,
		                msg: msg
		            },
		            success: function(data) {
		                console.log(data);

		                $('#matches_load').load(url, 'id='+ match_id +'&page=0', function() {

		                });
		            }
		        });
		   	} 
	    });	
	}
});