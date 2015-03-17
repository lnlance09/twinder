$(document).ready(function() {
	var base_url = '/wetinder/';
	// $('#sync_modal').modal('show'); 

	/**
	 * Validate the login form and get the auth token and sync the user's account with AJAX
	 */
	$('#signin_form').submit(function(e) {
		e.preventDefault();
        var username = $('input[name=username]').val();
        var password = $('input[name=password]').val();

        if(username != '' && password != '') {
	        $.ajax({
	            url: base_url +'admin/Login',
	            type: 'POST',
	            data: {
	                username: username,
	                password: password,
	                submit: 'submit'
	            },
	            success: function(data) {
	                // console.log(data);
	                if(data.trim() == 'true') {
	                	window.location = base_url +'hot';
	                } else if(data == 'error') {
	                	$('#signin_form').effect('shake');
	                } else {
	                	console.log("Didn't submit");
	                }
	            }
	        });
	   	} else {
	   		$('#signin_form').effect('shake');
	   	}
    });	
});