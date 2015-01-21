$(document).ready(function() {
    var base_url = $('#base_url').text();
    var user_id = $('#user_id').text();
    var auth = $('#auth').text();
    
    var tinder_id = $('#user_tinder_id').text();
    var likes = $('#like_count_num').text();
    var matches = $('#match_count_num').text();

    function AddCircle() {
        var $circle = $('<div class="circle"></div>');
        
        $circle.animate({
            'width': '600px',
            'height': '600px',
            'margin-top': '-350px',
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

    // Get the longitude and latitude coordinates
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(LoadUsers);
    } else {
        alert('Geolocation is not supported by this browser');
    }

    function LoadUsers(position) {
        var lon = position.coords.longitude;
        var lat = position.coords.latitude;

        var user_url = base_url +'users/DiscoverLoad';
        var data = 'auth='+ auth +'&lon='+ lon +'&lat='+ lat +'&index=0&type=new';

        $('#users_load').load(user_url, data, function() {
            $('#user_circle').fadeOut();

            $('ul#sub_pics li').click(function() {
                var pic = $(this).attr('name');
                $('#main_img').attr('src', pic);
            });

            $('#like_user, #pass_user').click(function() {
                var button = $(this).attr('id');

                if(button == 'pass_user') {
                    var endpoint = 'PassUser';
                } else {
                    var endpoint = 'LikeUser';
                }

                var index = $('#user_at_num').text();
                var tinder_id = $('#user_tinder_id').text();
                var new_index = parseInt(index)+parseInt(1);
                var mod = parseInt(new_index)%parseInt(11);
                $('#user_at_num').text(new_index);

                if(mod == 0) {
                    $('#users_load').load(user_url, data);
                    console.log('loaded again');
                }

                $.ajax({
                    url: base_url +'users/'+ endpoint,
                    data: {
                        id: tinder_id
                    },
                    success: function(data) {
                        if(data !== null) {
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
                                    $('#match_pic').attr('src', 'http://images.gotinder.com/'+ id +'/'+ pic);

                                    // Change the link to their profile
                                    $('#match_name').attr('href', base_url +'users/'+ id);
                                    $('#match_name').text(name);

                                    // Change the text of the button
                                    $('#msg_match').text('Send '+ name +' a Message');
                                    
                                    // Show the modal
                                    $('#match_modal').modal('show');

                                    // Make the button clickable
                                    $('#msg_match').click(function() {
                                        window.location.href = base_url +'matches/'+ match_id; 
                                    });  
                                }
                            });
                        } 

                        // Update the user's stats in the upper right hand corner
                        if(button == 'pass_user') {
                            $('#users_load').effect('toggle');
                        } else {
                            var like_count = $('#like_count_num').text();
                            var new_like_count = parseInt(like_count)+parseInt(1);
                            $('#like_count_num').text(new_like_count);
                        }

                        $('#users_load').load(user_url, 'index='+ new_index +'&type=old', function(data) {
                            $('#like_or_pass').fadeIn('slow');

                            $('#sub_pics li').click(function() {
                                var pic = $(this).attr('name');
                                $('#main_user_pic').attr('src', pic);
                            });
                        });
                    }
                });
            });
        });
    }
});