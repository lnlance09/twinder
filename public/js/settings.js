$(document).ready(function() {
    var base_url = $('#base_url').text();
    var user_id = $('#user_id').text();
    var auth = $('#auth').text();
    var method = $('#method').text();

    // Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    // Google Maps
    function Initialize(lat, lon, miles) {
        // Convert the miles to meters
        var meters = Math.ceil(miles/0.000621371);

        // Set the longitude and latitude
        var latlng = new google.maps.LatLng(lat, lon);

        var mapOptions = {
            center: latlng,
            zoom: 7,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var el = document.getElementById('google_maps');
        var map = new google.maps.Map(el, mapOptions);

        var marker = new google.maps.Marker({
            map: map,
            position: latlng
        });

        marker.setAnimation(google.maps.Animation.BOUNCE);

        var sunCircle = {
            strokeColor: "#c3fc49",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#c3fc49",
            fillOpacity: 0.25,
            map: map,
            center: latlng,
            radius: meters
        };

        cityCircle = new google.maps.Circle(sunCircle)
        cityCircle.bindTo('center', marker, 'position');
    }

    // Get the latitude and longitude coordinates
    var lon = $('#lon').text();
    var lat = $('#lat').text();
    var distance = $('#distance').text();

    // Load the Google Maps
    Initialize(lat, lon, meters);


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
     * Insterested In
     * 
     */
    $('#interested_in li a').click(function() {
        var value = $(this).attr('title');
        var key = $(this).text().trim();
        $('#interested_in_button').text(key);
        $('#interested_in_button').val(value);
    });


    /**
     * Gender Filter
     * 
     */
    $('#gender li a').click(function() {
        var value = $(this).attr('title');
        var key = $(this).text().trim();
        $('#gender_button').text(key);
        $('#gender_button').val(value);
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
                        $('#username_div .form-control').css('border', 'solid 1px green');
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
        var interested = $('#interested_in_button').val();
        var gender = $('#gender_button').val();
        // console.log('Distance: '+ distance +', Username: '+ username +', Max: '+ max +', Min: '+ min +', Interested In: '+ interested +', Gender: '+ gender);
        
        $.ajax({
            url: base_url +'settings/UpdateSettings',
            type: 'POST',
            data: {
                auth: auth,
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