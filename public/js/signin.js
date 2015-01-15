$(document).ready(function() {
	$('#signin_form').submit(function(e) {
        var username = $('input[name=username]').val();
        var password = $('input[name=password]').val();

        if(username != ''
        && password != '') {
	        $.ajax({
	            url : base_url +'users/UpdateSettings',
	            type: 'POST',
	            data : {
	                username: username,
	                password: password
	            },
	            success: function(data) {
	                console.log(data);
	            }
	        });
	   	} else {
	   		$('#signin_form').effect('shake');
	   		e.preventDefault();
	   	}
    });	

    $('#accept_terms').click(function(e) {
    	$(this).addClass('btn-success');
    	$(this).prepend('<i class="fa fa-check"></i> ');
    });
});