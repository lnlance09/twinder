$(document).ready(function() {
    var base_url = $('#base_url').text();
    var user_id = $('#user_id').text();
    var auth = $('#auth').text();
    var method = $('#method').text();

    if(method == 'Discover') {
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(ShowPosition);

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
            setInterval(AddCircle, 1500);
        } else {
            console.log('Geolocation is not supported by this browser');
        }

        function ShowPosition(position) {
            // Get the user's current longitude and latitude coordinates
            var lon = position.coords.longitude;
            var lat = position.coords.latitude;

            var user_url = base_url +'users/DiscoverLoad';
            var data = 'auth='+ auth +'&lon='+ lon +'&lat='+ lat +'&index=0&type=new';

            $('#users_load').load(user_url, data, function() {
                $('#user_circle').fadeOut();

                $('#sub_pics li').click(function() {
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
                            console.log(data);
                            var match_id = data;

                            if(data != '') {
                                // Show the modal
                                $('#match_modal').modal('show');

                                // Change the match count number
                                var match_count = $('#match_count_num').text();
                                var new_match_count = parseInt(match_count)+parseInt(1);
                                $('#match_count_num').text(new_match_count);

                                $.ajax({
                                    url: base_url +'users/GetMatchInfo',
                                    data: {
                                        match_id: data
                                    },
                                    success: function(data) {
                                        var obj = jQuery.parseJSON(data);
                                        var name = obj.name;
                                        var pic = obj.pic;
                                        var id = obj.id;
                                        // console.log(obj);

                                        // Change the match's pic on the modal
                                        $('#match_pic').attr('src', 'http://images.gotinder.com/'+ id +'/'+ pic);

                                        // Change the link to their profile
                                        $('#match_name').attr('href', base_url +'users/'+ id);
                                        $('#match_name').text(name);

                                        // Change the text of the button
                                        $('#msg_match').text('Send '+ name +' a Message');
                                        
                                        // Make the button clickable
                                        $('#msg_match').click(function() {
                                            window.location.href = base_url +'matches/'+ match_id; 
                                        });
                                    }   
                                });
                            } 

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
                                    $('#fb_pic_link').attr('href', '');
                                });
                            });
                        }
                    });
                });
            });
        }
    } else if(method == 'index') {
        var tinder_id = $('#user_tinder_id').text();
        var likes = $('#like_count_num').text();
        var matches = $('#match_count_num').text();
        var can_edit = $('#can_edit').text().trim();
        var passes = $('#pass_count').text();
        
        $('#main_img').click(function() {
            var href = $(this).attr('src');
            console.log(href);

            $('.fancybox').fancybox({
                href: href,
            });
        });

        // Define the query string
        var user_data = 'edit=false&id='+ tinder_id +'&edit='+ can_edit;

        $('#single_users_load').load(base_url +'users/EditProfile', user_data, function() {
            var can_edit = $('#can_edit').text().trim();

            $('#other_trigger').click(function() {
                $('#other_box').slideDown();
            });

            if(can_edit == 'true') {
                // Edit the user's bio
                $('h1.static button').click(function(e) {
                    e.preventDefault();
                    var button_id = $(this).attr('id');

                    if(button_id == 'click_to_edit') {
                        $(this).attr('class', 'btn btn-success pull-right');
                        $(this).attr('type', 'submit');
                        $(this).attr('id', 'editing');
                        $(this).text('Done');
                    } else {
                        var bio = $('#about_quote span').text();
                        // console.log(bio);

                        $.ajax({
                            url : base_url +'users/UpdateProfile',
                            type: 'POST',
                            data : {
                                bio: bio,
                                submit: 'submit'
                            },
                            success: function(data) {
                                console.log(data);
                            }
                        });

                        $(this).attr('class', 'btn btn-default pull-right');
                        $(this).attr('type', 'button');
                        $(this).attr('id', 'click_to_edit');
                        $(this).text('Edit');
                    }

                    $('#about_quote span').attr('contenteditable', 'true');
                    $('#about_quote span').css('font-style', 'italic');
                });
            }

            // Load the connections
            $('#con_load_box').load(base_url +'users/GetConnections', 'type=matches&page=0&id='+ tinder_id);

            // Search thru connections upon keyup of the input field
            $('#search_connections').keyup(function(e) {
                var q = $(this).val();
                var type = $('#connection_active').attr('name');

                $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type + '&page=0&id='+ tinder_id +'&q='+ q);
            });

            // Load the connections upon hover
            $('.timer_box').click(function() {
                $('.timer_box').attr('id', '');
                $(this).attr('id', 'connection_active');
                
                var type = $(this).attr('name');
                $('#type_name').text(type);
                $('#search_connections').attr('placeholder', 'Search '+ type);

                // Change the font-awesome icon
                if(type == 'likes') {
                    var font_awesome = 'thumbs-up';
                } else if(type == 'passes') {
                    var font_awesome = 'thumbs-down';   
                } else {
                    var font_awesome = 'heart';
                }

                $('i#fa_type').attr('class', 'fa fa-'+ font_awesome +' fa-2x');

                $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type + '&page=0&id='+ tinder_id);
            });

            // Make the pics sortable
            $('ul#sub_pics').sortable();

            // Change the pic upon click
            $('ul#sub_pics li').click(function(d) {
                d.preventDefault();
                var pic = $(this).attr('name');
                $('#main_img').attr('src', pic);
            });
        });

        
        // The user's last location
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(LastSeen);
        } else {
            alert("Geolocation isn't supported");
        }

        function LastSeen(position) {
            // Get the user's current longitude and latitude coordinates
            var lon = position.coords.longitude;
            var lat = position.coords.latitude;

            function initialize(lat, lon) {
                var latlng = new google.maps.LatLng(lat, lon);

                var mapOptions = {
                    center: latlng,
                    zoom: 6,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                var el = document.getElementById('ping_map');
                var map = new google.maps.Map(el, mapOptions);

                var marker = new google.maps.Marker({
                    map: map,
                    position: latlng
                });

                marker.setAnimation(google.maps.Animation.BOUNCE);
            }

            initialize(lat, lon);
        }
    } 
});