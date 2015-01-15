$(document).ready(function() {
    var base_url = $('#base_url').text();
    var user_id = $('#user_id').text();
    var auth = $('#auth').text();
    var method = $('#method').text();

    // Get the latitude and longitude coordinates
    var lon = $('#lon').text();
    var lat = $('#lat').text();
    var distance = $('#distance').text();
    var meters = Math.round(parseInt(distance)*1609.344);
    // console.log(meters);

    // Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    // Age slider
    var min = $('#min').text();
    var max = $('#max').text();
    var distance = $('#distance').text();
    // console.log(distance);

    $("#age_slider").noUiSlider({
        connect: true,
        behaviour: 'tap',
        start: [min, max],
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

    // Distance slider
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

    
    $('.settings_box .dropdown li a').click(function() {
        var value = $(this).attr('title');
        var key = $(this).text().trim();
        $('#interested_in_button').text(key);
    });


    // Google Maps
    function initialize(lat, lon, meters) {
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

    initialize(lat, lon, meters);

    $('#distance_slider').change(function() {
        var miles = $('#distance-value').text().trim();
        var meters = Math.round(parseInt(miles)*1609.344);
        initialize(lat, lon, meters);
    });

    $('#username').keyup(function() {
       $.ajax({
            url : base_url +'settings/CheckUsername',
            data : {
                username: username
            },
            success: function(data) {
                if(data == 0) {
                    $(this).css('border')
                } else {

                }
            }
        }); 
    });

    $('#settings_form').submit(function(e) {
        e.preventDefault();
        var distance = $('#distance-value').text();
        var username = $('#username').val();
        var max = $('#upper-value').text();
        var min = $('#lower-value').text();
        var interested = $('#interested_in_button').attr('name');
        var gender = $('#gender_button').attr('name');

        $.ajax({
            url : base_url +'settings/UpdateSettings',
            type: 'POST',
            data : {
                auth: auth,
                username: username,
                interested_in: interested,
                gender: gender,
                distance: distance,
                max: max,
                min: min
            },
            success: function(data) {
                window.location = base_url +'settings';
            }
        });
    });
});