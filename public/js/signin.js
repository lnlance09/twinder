$(document).ready(function() {
	var base_url = $('#base_url').text().trim(); 
	// $('#sync_modal').modal('show'); 

	/**
	 * Validate the login form and get the auth token and sync the user's account with AJAX
	 */
	$('#signin_form').submit(function(e) {
		e.preventDefault();
        var username = $('input[name=username]').val();
        var password = $('input[name=password]').val();

        if(username != '' && password != '') {
        	$('#sync_modal').modal('show');

	        $.ajax({
	            url: base_url +'signin/Login',
	            type: 'POST',
	            data: {
	                username: username,
	                password: password,
	                submit: 'submit'
	            },
	            success: function(data) {
	                console.log(data);
	                if(data.trim() == 'true') {
	                	window.location = base_url +'users/discover';
	                } else {
	                	$('input[name=username]').css('border', 'solid 1px red');
	                	$('input[name=password]').css('border', 'solid 1px red');

	                	switch(data) {
	                		case'login error':

	                			$('#login_error').text('Incorrect Facebook login credentials');
	                			break;

	                		case'token error':
	                		case'authentication':

	                			$('#login_error').text("Couldn't get an auth token from Tinder");
	                	}

	                	$('#login_error').slideDown();
	                	$('#sync_modal').modal('hide');
	                }
	            }
	        });
	   	} else {
	   		$('#signin_form').effect('shake');
	   	}
    });	

	/**
	 * Show the modal width of the terms of service upon click of the terms of serice link
	 */
    $('#accept_terms').click(function(e) {
    	$(this).text('Accepted');
    	$(this).prepend('<i class="fa fa-check"></i> ');
    	$('#terms_modal').modal('hide');
    });
});