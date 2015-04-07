$(document).ready(function() {
    var base_url = $('#base_url').text(); 
    var tinder_id = $('#user_tinder_id').text();
    var my_tinder_id = $('#my_tinder_id').text();
    var twitter_id = $('#twitter_id').text();
    var twitter = $('#twitter').text();
    var first_name = $('#first_name').text();
    var gender = $('#gender').text();
    var active_tab = $('#active_tab').text();
    var can_edit = $('#can_edit').text();
    var can_like = $('#like').text();
    var styles = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0099dd"}]}];

    // Google Maps
    function Initialize(lat, lon) {
        var distance = $('#radius').text();
        var tenth = distance*0.05;
        var zoom = parseInt(10)-parseInt(tenth);

        if(zoom < 0) {
            var zoom = 4;
        }

        // Set the position
        var LatLon = new google.maps.LatLng(lat, lon);
        var options = {
            center: LatLon,
            zoom: zoom,
            mapTypeControlOptions: {
                mapTypeIds: ['map_style']
            }
        };

        var map = new google.maps.Map(document.getElementById('ping_map'), options);
        map.mapTypes.set('map_style', new google.maps.StyledMapType(styles, {name: 'Twinder Radar'}));
        map.setMapTypeId('map_style');

        // Set the infobox options
        var infowindow = new google.maps.InfoWindow({
            content: $('#infobox').html()
        });

        // Set the marker
        var marker = new google.maps.Marker({
            map: map,
            position: LatLon,
            draggable: true,
            animation: google.maps.Animation.DROP,
        });

        // Zoom in and center the marker upon click of the marker
        google.maps.event.addListener(marker, 'click', function() {
            map.setZoom(16);
            map.setCenter(marker.getPosition());
            infowindow.open(map, marker);
        });
    
        // Convert the miles to meters and draw the radius
        var radius = {
            strokeColor: '#ad5',
            strokeOpacity: 0.8,
            strokeWeight: 1,
            fillColor: '#ad5',
            fillOpacity: 0.35,
            map: map,
            center: LatLon,
            radius: Math.round(parseInt(distance*1609.344))
        };

        circle = new google.maps.Circle(radius);
        circle.bindTo('center', marker, 'position');

        // Resize the map accordingly
        google.maps.event.trigger(map, 'resize');

        // Adjust the height of the map
        $('#google_maps').css('height', '250px');
    }

    function ChangeURL(name, tab) {
        var title = name +' - Twinder';
        var link = location.pathname.match(/\/users\/(.*)/)[1];
        var real = link.split('/');
        var url = base_url + 'users/'+ real[0] +'/'+ tab;
        window.history.replaceState('', title, url);
    }

    // Edit the profile
    if(can_edit == 1) {
        // Edit the user's bio
        $('button#click_to_edit, button#resize_click_to_edit').click(function(e) {
            e.preventDefault();
            
            // If the form is being opened to be edited
            if($(this).attr('id') == 'click_to_edit') {
                $(this).attr('class', 'btn btn-success pull-right');
                $(this).attr('type', 'submit');
                $(this).attr('id', 'editing');
                $(this).text('Done');
            } else {
                // Submit the form
                $.ajax({
                    url: base_url +'users/UpdateProfile',
                    type: 'POST',
                    data: {
                        bio: $('#bio_text').val(),
                        submit: 'submit'
                    },
                    success: function(data) {
                        // console.log(data);
                        $('#about_quote').text($('#bio_text').val());
                        $('#about_quote').fadeIn();
                        $('#bio_text').hide();
                    }
                });

                $(this).attr('class', 'btn btn-primary pull-right');
                $(this).attr('type', 'button');
                $(this).attr('id', 'click_to_edit');
                $(this).text('Edit');
            }
        });
    }

    // Report the user
    $('#report_modal ul li').click(function() {
        var reason = $(this).attr('name');

        if(reason == 0) {
            $('#other_box').slideDown();
            
            $('#report_text').click(function() {
                var text = $('#other_comment').val().trim();

                if(text != '') {
                    $.ajax({
                        url: base_url +'users/ReportUser',
                        data: {
                            id: tinder_id,
                            reason: reason,
                            text: text
                        },
                        success: function(data) {
                            console.log(data);
                            var obj = JSON.parse(data);

                            if(obj.status == 200) {
                                $('#report_modal').modal('hide');
                                $('#report_user').fadeOut('slow');
                            }
                        }
                    });
                } else {
                    $('#other_comment').css('border', 'solid 1px red');
                }
            });
        } else {
            $.ajax({
                url: base_url +'users/ReportUser',
                data: {
                    id: tinder_id,
                    reason: reason
                },
                success: function(data) {
                    console.log(data);
                    var obj = JSON.parse(data);

                    if(obj.status == 200) {
                        $('#report_modal').modal('hide');
                        $('#report_user').fadeOut('slow');
                        location.reload();
                    }
                }
            });
        }
    });

    // Search thru connections upon keyup of the input field
    $('#search_connections').keyup(function(e) {
        var type = $('#active').attr('name');
        var data = 'type='+ type + '&page=0&id='+ tinder_id +'&q='+ $(this).val() +'&twitter_id='+ twitter_id;
        $('#con_load_box').load(base_url +'users/GetConnections', data);
    });

    switch(can_like) {
        // The users have already been matched
        case'matched':

            var match_id = $('#match_id').text();

            $('#unmatch_user, #resize_unmatch_user').hover(function() {
                $(this).removeClass('btn-warning');
                $(this).addClass('btn-danger');
                $(this).text('Unmatch');
            });

            $('#unmatch_user, #resize_unmatch_user').mouseout(function() {
                $(this).removeClass('btn-danger');
                $(this).addClass('btn-warning');
                $(this).text('Matched');
            });

            $('#unmatch_user, #resize_unmatch_user').click(function() {
                $.ajax({
                    url: base_url +'users/UnmatchUser',
                    data: {
                        id: match_id
                    },
                    success: function(data) {
                        console.log(data);
                        $('#unmatch_user, #resize_unmatch_user').fadeOut(2000);
                    }
                });
            });
            break;

        case'can_like':

            $('#like_user, #resize_like_user').click(function() {
                $.ajax({
                    url: base_url +'users/LikeUser',
                    data: {
                        id: tinder_id
                    },
                    success: function(data) {
                        if(data != 'false') {
                            // Change the match count number
                            var count = $('#match_count_num').text();
                            $('#match_count_num').text(parseInt(count)+parseInt(1));

                            // Show the modal
                            $('#match_modal').modal('show');

                            // Make the button clickable
                            $('#msg_match').click(function() {
                                window.location.href = base_url +'matches/'+ data; 
                            });

                            var new_class = 'warning';
                            var new_text = 'Matched';
                        } else {
                            var new_class = 'success';
                            var new_text = 'Liked!';
                        }

                        // Change the button
                        $('#like_user, #resize_like_user').removeClass('btn-default');
                        $('#like_user, #resize_like_user').addClass('btn-'+ new_class);
                        $('#like_user, #resize_like_user').html(new_text);
                        console.log(data);
                    }
                });
            });
            break;
    }

    // Load the connections upon hover
    $('.timer_box').click(function() {
        // Set this element's ID to 'active'
        $('.timer_box').attr('id', '');
        $(this).attr('id', 'active');

        var type = $(this).attr('name');
        $('#type_name').text(type);
        $('#search_connections').attr('placeholder', 'Search '+ type);

        // Change the URL
        ChangeURL(first_name, type);

        // Change the font-awesome icon
        switch(type) {
            case'likes':

                var fa = 'thumbs-up';
                var labels = {
                    'likes': 'Likes',
                    'liked_by': 'Liked by'
                };

                if(my_tinder_id != tinder_id && my_tinder_id != '') {
                    labels['mutual_likes'] = 'Mutual likes';
                }
                break;

            case'matches':

                var fa = 'heart';
                var labels = {
                    'matches': 'Matches'
                };

                if(my_tinder_id != tinder_id && my_tinder_id != '') {
                    labels['mutual_matches'] = 'Mutual matches';
                }
                break;

            case'passes':

                var fa = 'thumbs-down';
                var labels = {
                    'passes': 'Passes',
                    'passed_by': 'Passed by'
                };

                if(my_tinder_id != tinder_id && my_tinder_id != '') {
                    labels['mutual_passes'] = 'Mutual passes';
                }
                break;

            case'tweets':

                var fa = 'twitter';
                var labels = {
                    'tweets': 'Tweets',
                    'tweets_and_replies': 'Tweets and replies',
                    'photos_and_videos': 'Photos and videos'
                };
                break;
        }

        if(type == 'tweets' && twitter == 'false') {
            $('#con_wrapper .input-group').hide();
        } else {
            $('#fa_type').attr('class', 'fa fa-'+ fa +' fa-2x');
            $('#con_wrapper .input-group').fadeIn();
        }

        // Define the query string
        var data = 'type='+ type + '&page=0&id='+ tinder_id;
        if(type == 'tweets' || type == 'tweets_and_replies') {
            data += '&twitter='+ twitter +'&name='+ first_name +'&gender='+ gender +'&twitter_id='+ twitter_id;
        }

        // Load the new results
        $('#con_load_box').load(base_url +'users/GetConnections', data, function() {
            $('.panel-heading ul li').fadeOut();
            $('.panel-heading ul li').remove();

            for(var key in labels) {
                $('.panel-heading ul').append("<li name='"+ key +"'>"+ labels[key] +"</li>");
                $('.panel-heading ul li').fadeIn();
            }

            $('.panel-heading ul li:first-of-type').attr('id', 'active');

            $('.panel-heading ul li').click(function() {
                $(this).attr('id', 'active');
                $(this).siblings().attr('id', '');
                var type = $(this).attr('name');

                // Change the URL
                ChangeURL(first_name, $(this).attr('name'));

                var data = 'type='+ type + '&page=0&id='+ tinder_id;
                if(type == 'tweets' || type == 'tweets_and_replies' || type == 'photos_and_videos') {
                    data += '&twitter='+ twitter +'&name='+ first_name +'&gender='+ gender;
                }

                $('#con_load_box').load(base_url +'users/GetConnections', data);
            });
        });
    });

    // Load the modal upon click
    $('ul#sub_pics li').click(function(e) {
        e.preventDefault();
        $('#gallery_img').attr('src', $(this).attr('name'));
    });

    // Load the map
    Initialize($('#lat').text(), $('#lon').text());

    // Load the connections
    var active_tab = $('.panel-heading ul li#active').attr('name');
    var data = 'type='+ active_tab +'&page=0&id='+ tinder_id +'&twitter='+ twitter +'&twitter_id='+ twitter_id +'&name='+ first_name;

    $('#con_load_box').load(base_url +'users/GetConnections', data, function() {
        $('.panel-heading ul li').click(function() {
            $(this).attr('id', 'active');
            $(this).siblings().attr('id', '');
            var type = $(this).attr('name');

            // Change the URL
            ChangeURL(first_name, $(this).attr('name'));

            var data = 'type='+ type + '&page=0&id='+ tinder_id;
            if(type == 'tweets' || type == 'tweets_and_replies' || type == 'photos_and_videos') {
                data += '&twitter='+ twitter +'&name='+ first_name +'&gender='+ gender +'&twitter_id='+ twitter_id;
            }

            $('#con_load_box').load(base_url +'users/GetConnections', data);
        });
    });
});