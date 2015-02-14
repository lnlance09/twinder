$(document).ready(function() {
    var base_url = $('#base_url').text();
    var tinder_id = $('#user_tinder_id').text();
    var can_edit = $('#can_edit').text().trim();
    
    var lat = $('#lat').text().trim();
    var lon = $('#lon').text().trim();
    var radius = Math.round(parseInt($('#radius').text())*1609.344);
    // console.log(radius);
    // console.log(can_edit);

    if(can_edit == 1) {
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

                $('ul#sub_pics li').each(function(index) {
                    // var link = $(this).attr('');
                });

                $.ajax({
                    url: base_url +'users/UpdateProfile',
                    type: 'POST',
                    data: {
                        bio: bio,
                        pics: '',
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

        // Make the pics sortable
        $('ul#sub_pics').sortable();
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
                            var obj = jQuery.parseJSON(data);

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
                    var obj = jQuery.parseJSON(data);

                    if(obj.status == 200) {
                        $('#report_modal').modal('hide');
                        $('#report_user').fadeOut('slow');
                    }
                }
            });
        }
    });

    // Load the connections
    $('#con_load_box').load(base_url +'users/GetConnections', 'type=matches&page=0&id='+ tinder_id);
    


    // Search thru connections upon keyup of the input field
    $('#search_connections').keyup(function(e) {
        var q = $(this).val();
        var type = $('#active').attr('name');
        $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type + '&page=0&id='+ tinder_id +'&q='+ q);
    });

    // Load the connections upon hover
    $('.timer_box').click(function() {
        $('.timer_box').attr('id', '');
        $(this).attr('id', 'active');

        var type = $(this).attr('name');
        $('#type_name').text(type);
        $('#search_connections').attr('placeholder', 'Search '+ type);

        // Change the font-awesome icon
        if(type == 'likes') {
            var fa = 'thumbs-up';
        } else if(type == 'passes') {
            var fa = 'thumbs-down';   
        } else {
            var fa = 'heart';
        }

        $('#fa_type').attr('class', 'fa fa-'+ fa +' fa-2x');

        // Define the query string
        var data = 'type='+ type + '&page=0&id='+ tinder_id;
        $('#con_load_box').load(base_url +'users/GetConnections', data, function() {
           
        });
    });

    // Change the pic upon click
    $('ul#sub_pics li').click(function(e) {
        e.preventDefault();
        var pic = $(this).attr('name');
        $('#main_img').attr('src', pic);
    });

    // Google Maps
    function Initialize(lat, lon) {
        // Set the styling for the map
        var styles = [{"stylers":[{"saturation":-100}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0099dd"}]},{"elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{}];
        var latlng = new google.maps.LatLng(lat, lon);

        var mapOptions = {
            mapTypeControlOptions: {  
                mapTypeIds: ['Styled']  
            },  
            mapTypeId: 'Styled',
            center: latlng,
            zoom: 16,
            position: latlng
        };

        var el = document.getElementById('ping_map');
        var map = new google.maps.Map(el, mapOptions);
        var styledMapType = new google.maps.StyledMapType(styles, {name: 'Styled'});  
        map.mapTypes.set('Styled', styledMapType);  

        // Set the marker
        var marker = new google.maps.Marker({
            map: map,
            position: latlng
        });

        // Bounce the marker
        marker.setAnimation(google.maps.Animation.DROP);

        var sunCircle = {
            strokeColor: '#fd923a',
            strokeOpacity: 0.8,
            strokeWeight: 1,
            fillColor: '#fd923a',
            fillOpacity: 0.25,
            map: map,
            center: latlng,
            radius: radius
        };

        cityCircle = new google.maps.Circle(sunCircle)
        cityCircle.bindTo('center', marker, 'position');

        infobox = new InfoBox({
             content: document.getElementById('infobox'),
             disableAutoPan: true,
             pixelOffset: new google.maps.Size(-140, 0),
             zIndex: null,
             boxStyle: {
                width: '100%'
            },
            closeBoxMargin: '12px 4px 2px 2px',
            closeBoxURL: 'http://www.google.com/intl/en_us/mapfiles/close.gif',
            infoBoxClearance: new google.maps.Size(1, 1)
        });
        
        google.maps.event.addListener(marker, 'click', function() {
            infobox.open(map, this);
        });
    }

    // Load the map
    Initialize(lat, lon);
});