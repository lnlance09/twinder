$(document).ready(function() {
    var base_url = $('#base_url').text().trim(); 
    var tinder_id = $('#user_tinder_id').text();
    var match_id = $('#match_id').text();

    // Load the results
    var data = 'id='+ match_id +'&page=0';
    $('#match_load').load(base_url +'matches/Thread', data, function() {
        $('#match_load .ajax-loader').fadeOut();
    });

    $('form#send_msg').submit(function(e) {
        e.preventDefault();
        var msg = $('textarea').val();

        if(msg != '') {
            $.ajax({
                url: base_url +'users/SendMessage',
                type: 'POST',
                data: {
                    msg: msg,
                    id: match_id,
                    submit: 'submit'
                },
                success: function(data) {
                    // console.log(data);
                    
                    // Reload the thread
                    if(data == 'true') {
                        var data = 'id='+ match_id +'&page=0';
                        $('#match_load').load(base_url +'matches/Thread', data, function() {
                            $('html, body').animate({
                                scrollTop: $('form#send_msg').top
                            }, 2000);  
                        });
                    }
                }
            });
        } else {
            $(this).effect('shake');
        }
    }); 
});