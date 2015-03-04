$(document).ready(function() {
    var base_url = '/wetinder/'; 
    var styles = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0099dd"}]}];

    // Write the CSS 'left' value to a span
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    // Google Maps
    function Initialize(lat, lon, miles) {
        var tenth = miles*0.05;
        var zoom = parseInt(10)-parseInt(tenth);

        // Set the longitude and latitude
        var latlng = new google.maps.LatLng(lat, lon);
        var options = {
            center: latlng,
            zoom: zoom,
            mapTypeControlOptions: {
                mapTypeIds: ['map_style']
            }
        };

        var map = new google.maps.Map(document.getElementById('google_maps'), options);
        map.mapTypes.set('map_style', new google.maps.StyledMapType(styles, {name: 'Distance Filter'}));
        map.setMapTypeId('map_style');

        var marker = new google.maps.Marker({
            map: map,
            position: latlng,
            animation: google.maps.Animation.DROP,
        });

        // Zoom in and center the marker upon click of the marker
        google.maps.event.addListener(marker, 'click', function() {
            map.setZoom(15);
            map.setCenter(marker.getPosition());
        });

        var radius = {
            strokeColor: '#ad5',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#ad5',
            fillOpacity: 0.35,
            map: map,
            center: latlng,
            radius: Math.ceil(miles/0.000621371),
        };

        circle = new google.maps.Circle(radius);
        circle.bindTo('center', marker, 'position');
    }

    var lon = $('#lon').text().trim();
    var lat = $('#lat').text().trim();
    var distance = $('#distance').text();

    // Load the Google Maps
    Initialize(lat, lon, distance);

    /**
     * Age Slider
     * 
     */
    $("#age_slider").noUiSlider({
        connect: true,
        behaviour: 'tap',
        start: [$('#min').text(), $('#max').text()],
        step: 1,
        format: wNumb({
            decimals: 0
        }),
        range: {
            'min': [18],
            'max': [50]
        }
    });

    $("#age_slider").Link('lower').to($('#lower-value'));
    $("#age_slider").Link('upper').to($('#upper-value'));

    /**
     * Distance Slider
     * 
     */
    $("#distance_slider").noUiSlider({
        start: distance,
        connect: 'lower',
        step: 1,
        format: wNumb({
            decimals: 0
        }),
        range: {
          'min': 1,
          'max': 100
        }
    });

    $("#distance_slider").Link('lower').to($('#distance-value'));

    // Change the radius on the map each time its changed
    $('#distance_slider').change(function() {
        var miles = $('#distance-value').text().trim();
        Initialize(lat, lon, miles);
    });
    
    /**
     * Insterested In & Gender
     * 
     */
    $('#interested_in div.selector, #gender div.selector').click(function() {
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');
    });

    /**
     * Check to see if a username is available upon keyup of the input field
     */
    $('#username').keyup(function() {
        var username = $(this).val().trim();
        
        $.ajax({
            url: base_url +'settings/CheckUsername',
            data: {
                username: username
            },
            success: function(data) {
                console.log(data);

                if(username != '') {
                    if(data == 0) {
                        $('#username_div .form-control').css('border', 'solid 1px #ad5');
                    } else {
                        $('#username_div .form-control').css('border', 'solid 1px red');
                    }
                } else {
                    $('#username_div .form-control').css('border', 'solid 1px #ccc');
                }
            }
        }); 
    });

    /**
     * Submit the form with AJAX
     */
    $('#settings_form').submit(function(e) {
        e.preventDefault();
        var distance = $('#distance-value').text().trim();
        var username = $('#username').val().trim();
        var max = $('#upper-value').text().trim();
        var min = $('#lower-value').text().trim();
        var interested = $('#interested_in').find('.active').attr('title');
        var gender = $('#gender').find('.active').attr('title');
        // console.log('Distance: '+ distance +', Username: '+ username +', Max: '+ max +', Min: '+ min +', Interested In: '+ interested +', Gender: '+ gender);
        
        $.ajax({
            url: base_url +'settings/UpdateSettings',
            type: 'POST',
            data: {
                username: username,
                interested_in: interested,
                gender: gender,
                distance: distance,
                max: max,
                min: min
            },
            success: function(data) {
                // console.log(data);
                window.location = base_url +'settings';
            }
        });
    });
});
