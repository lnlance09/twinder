$(document).ready(function() {
    var base_url = '/wetinder/'; 
    var tinder_id = $('#user_tinder_id').text();
    var can_edit = $('#can_edit').text().trim();
    var twitter = $('#twitter_access').text().trim();
    var styles = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0099dd"}]}];
    // console.log(can_edit);

    // Google Maps
    function Initialize(lat, lon) {
        // Set the position
        var LatLon = new google.maps.LatLng(lat, lon);
        var options = {
            center: LatLon,
            zoom: 10,
            mapTypeControlOptions: {
                mapTypeIds: ['map_style']
            }
        };

        var map = new google.maps.Map(document.getElementById('ping_map'), options);
        map.mapTypes.set('map_style', new google.maps.StyledMapType(styles, {name: 'Last seen by '}));
        map.setMapTypeId('map_style');

        // Set the infobox options
        infoBubble = new InfoBubble({
            map: map,
            content: $('#infobox').html(),
            position: LatLon,
            shadowStyle: 1,
            padding: '6px',
            backgroundColor: '#fff',
            borderRadius: 0,
            arrowSize: 5,
            borderWidth: 1,
            borderColor: '#000',
            disableAutoPan: false,
            hideCloseButton: true,
            arrowPosition: 30,
            backgroundClassName: 'transparent',
            arrowStyle: 2,
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
            
            infoBubble.open();
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
            radius: Math.round(parseInt($('#radius').text())*1609.344)
        };

        circle = new google.maps.Circle(radius);
        circle.bindTo('center', marker, 'position');

        // Resize the map accordingly
        google.maps.event.trigger(map, 'resize');

        // Adjust the height of the map
        $('#google_maps').css('height', '250px');
    }

    if(can_edit == 1) {
        // Edit the user's bio
        $('h1.static button').click(function(e) {
            e.preventDefault();
            
            // If the form is being opened to be edited
            if($(this).attr('id') == 'click_to_edit') {
                $('#about_quote').hide();
                $('#bio_text').fadeIn();

                $(this).attr('class', 'btn btn-success pull-right');
                $(this).attr('type', 'submit');
                $(this).attr('id', 'editing');
                $(this).text('Done');
            } else {
                // If the form is being submitted
                $('ul#sub_pics li').each(function(index) {
                    // var link = $(this).attr('');
                });

                // Submit the form
                $.ajax({
                    url: base_url +'users/UpdateProfile',
                    type: 'POST',
                    data: {
                        bio: $('#about_quote span').text(),
                        pics: '',
                        submit: 'submit'
                    },
                    success: function(data) {
                        console.log(data);
                        $('#about_quote').fadeIn();
                        $('#bio_text').hide();
                    }
                });

                $(this).attr('class', 'btn btn-primary pull-right');
                $(this).attr('type', 'button');
                $(this).attr('id', 'click_to_edit');
                $(this).text('Edit');
            }

            $('#about_quote span').attr('contenteditable', 'true');
            $('#about_quote span').css('font-style', 'italic');
        });

        // Make the pics sortable
        // $('ul#sub_pics').sortable();
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
                            // console.log(data);
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
                url : base_url +'users/ReportUser',
                data : {
                    id: tinder_id,
                    reason: reason
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
        }
    });

    // Search thru connections upon keyup of the input field
    $('#search_connections').keyup(function(e) {
        var q = $(this).val();
        var type = $('#active').attr('name');
        var data = 'type='+ type + '&page=0&id='+ tinder_id +'&q='+ q;
        $('#con_load_box').load(base_url +'users/GetConnections', data);
    });

    // Load the connections upon hover
    $('.timer_box').click(function() {
        $('.timer_box').attr('id', '');
        $(this).attr('id', 'active');

        var type = $(this).attr('name');
        $('#type_name').text(type);
        $('#search_connections').attr('placeholder', 'Search '+ type);

        // Change the font-awesome icon
        switch(type) {
            case'likes':

                var fa = 'thumbs-up';
                break;

            case'matches':

                var fa = 'heart';
                break;

            case'passes':

                var fa = 'thumbs-down';
                break;

            case'tweets':

                var fa = 'twitter';
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

        if(type == 'tweets') {
            data += '&twitter='+ twitter;
        }

        // Load the new results
        $('#con_load_box').load(base_url +'users/GetConnections', data, function() {
           $('#con_load_box .ajax-loader').fadeOut();
        });
    });

    // Change the pic upon click
    $('ul#sub_pics li').click(function(e) {
        e.preventDefault();
        var pic = $(this).attr('name');
        $('#gallery_img').attr('src', pic);
        $('#gallery_modal').modal('show');
    });

    // Load the map
    var lat = $('#lat').text().trim();
    var lon = $('#lon').text().trim();
    Initialize(lat, lon);

    // Load the connections
    $('#con_load_box').load(base_url +'users/GetConnections', 'type=likes&page=0&id='+ tinder_id, function() {

    });
});
