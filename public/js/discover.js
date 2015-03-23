$(document).ready(function() {
    var base_url = $('#base_url').text().trim(); 
    var auth = $('#auth').text().trim();
    
    /**
     * Draw the radiating circle while loading a new batch of users
     */
    function AddCircle() {
        var $circle = $('<div class="circle"></div>');
        
        $circle.animate({
            'width': '600px',
            'height': '600px',
            'margin-top': '-460px',
            'margin-left': '-300px',
            'opacity': '0'
        }, 2000, 'easeOutCirc');
        
        $('#user_circle').append($circle);
    
        setTimeout(function __remove() {
            $circle.remove();
        }, 2000);
    }

    AddCircle();
    setInterval(AddCircle, 1200);

    function LoadUsers(position) {
        var lon = position.coords.longitude;
        var lat = position.coords.latitude;

        var user_url = base_url +'users/DiscoverLoad';
        var data = 'auth='+ auth +'&lon='+ lon +'&lat='+ lat +'&index=0&type=new';

        // Load the results
        $('#users_load').load(user_url, data, function() {
            // Fade the radiating circle out
            $('#user_circle').fadeOut();

            // Make each users pics clickable
            $('ul#sub_pics li').click(function() {
                $('#main_img').attr('src', $(this).attr('name'));
            });

            // Upon like or pass of a user
            $('#like_user, #pass_user').click(function() {
                // Get the element's ID
                var _id = $(this).attr('id');
                var tinder_id = $('#user_tinder_id').text();

                if(_id == 'like_user') {
                    var text = 'Like';
                    var _class = 'like';
                    var direction = 'left';
                } else {
                    var text = 'Pass';
                    var _class = 'dislike';
                    var direction = 'right';
                }

                // Swipe animations
                $('.jumbotron').addClass('rotate-'+ direction).delay(700).fadeOut(1);
                $('.buddy').find('.status').remove();
                $('.jumbotron').append('<div class="status '+ _class +'">'+ text +'</div>'); 

                // If it's the 11th like, then load a fresh batch
                var index = $('#user_at_num').text();
                var new_index = parseInt(index)+parseInt(1);
                var mod = parseInt(new_index)%parseInt(11);
                $('#user_at_num').text(new_index);

                if(mod == 0) {
                    $('#users_load').load(user_url, data);
                    console.log('loaded again');
                }

                // Determine the API endpoint
                var el_id = $(this).attr('id');

                if(el_id == 'pass_user') {
                    var endpoint = 'PassUser';
                } else {
                    var endpoint = 'LikeUser';
                }

                $.ajax({
                    url: base_url +'users/'+ endpoint,
                    data: {
                        id: tinder_id
                    },
                    success: function(data) {
                        console.log(data);

                        if(data != 'false') {
                            var match_id = data;

                            // Change the match count number
                            var match_count = $('#match_count_num').text();
                            var new_match_count = parseInt(match_count)+parseInt(1);
                            $('#match_count_num').text(new_match_count);

                            $.ajax({
                                url: base_url +'users/GetMatchInfo',
                                data: {
                                    match_id: match_id
                                },
                                success: function(data) {
                                    var obj = JSON.parse(data);
                                    var name = obj.name;
                                    var id = obj.id;
                                    var pic = obj.pic;

                                    // Change the match's pic on the modal
                                    $('#match_pic').attr('src', pic);

                                    // Change the link to their profile
                                    $('#match_name').attr('href', base_url +'users/'+ id);
                                    $('#match_name').text(name);

                                    // Change the text of the button
                                    $('#msg_match').text('Send '+ name +' a Message');
                                    
                                    // Show the modal
                                    $('#match_modal').modal('show');

                                    // Make the button clickable
                                    $('#msg_match').click(function() {
                                        window.location.href = 'matches/'+ match_id; 
                                    });  
                                }
                            });
                        } 

                        // Update the user's stats in the upper right hand corner
                        if(el_id == 'pass_user') {
                            $('#users_load').effect('toggle');
                        } else {
                            var like_count = $('#like_count_num').text();
                            $('#like_count_num').text(parseInt(like_count)+parseInt(1));
                        }

                        // Load the next user in the batch
                        $('#users_load').load(user_url, 'index='+ new_index +'&type=old', function(data) {
                            $('#like_or_pass').fadeIn('slow');

                            $('#sub_pics li').click(function() {
                                $('#main_user_pic').attr('src', $(this).attr('name'));
                            });
                        });
                    }
                });
            });
        });
    }

    function ShowError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                x.innerHTML = "User denied the request for Geolocation.";
                break;

            case error.POSITION_UNAVAILABLE:
                x.innerHTML = "Location information is unavailable.";
                break;

            case error.TIMEOUT:
                x.innerHTML = "The request to get user location timed out.";
                break;

            case error.UNKNOWN_ERROR:
                x.innerHTML = "An unknown error occurred.";
                break;
        }
    }

    // Get the longitude and latitude coordinates
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(LoadUsers, ShowError);
    } else {
        alert('Geolocation is not supported by this browser');
    }
});
