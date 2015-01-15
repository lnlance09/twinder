$(document).ready(function() { 
    var base_url = $('#base_url').text();
    var distance = $('#distance').text().trim();
    var meters = parseInt($('#meters').text().trim());
    var page = $('#page').text().trim();
    // console.log(meters);

    // Get the longitude and latitude coordinates
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(ShowPosition);
    } else {
        console.log('Geolocation is not supported by this browser');
    }

    // Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    // Age slider
    $("#age_slider").noUiSlider({
        connect: true,
        behaviour: 'tap',
        start: [18, 50],
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

    
    $('ul#sex_select li').click(function() {
        var gender = $(this).find('a').attr('title');

        // Load the hottest
        $('#hot_load').load(base_url +'hot/GetHottest', 'page='+ page +'&gender='+ gender, function() {
            $('#hot_load .ajax-loader').fadeOut();
        });
    });

    // Load the hottest
    $('#hot_load').load(base_url +'hot/GetHottest', 'page='+ page, function() {
    	$('#hot_load .ajax-loader').fadeOut();
    });

    function ShowPosition(position) {
        function initialize(meters, lat, lon) {
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
                position: latlng,
                draggable: true,
            });

            marker.setAnimation(google.maps.Animation.BOUNCE);

            google.maps.event.addListener(marker, 'dragend', function(marker) { 
                var drag_lat = marker.latLng.lat();
                var drag_lon = marker.latLng.lng();
                $('#drag_lat').text(drag_lat);
                $('#drag_lon').text(drag_lon);

                $.ajax({
                    url : base_url +'home/LocationNameFromCoords',
                    data : {
                        lon: drag_lon,
                        lat: drag_lat
                    },
                    success: function(data) {
                        $('#address_components').text(data);
                    }
                });
            });

            var sunCircle = {
                strokeColor: '#c3fc49',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#c3fc49',
                fillOpacity: 0.25,
                map: map,
                center: latlng,
                radius: meters
            };

            cityCircle = new google.maps.Circle(sunCircle)
            cityCircle.bindTo('center', marker, 'position');
        }

        // Get the user's current longitude and latitude coordinates
        var lon = position.coords.longitude;
        var lat = position.coords.latitude;
        initialize(meters, lat, lon);

        $('#drag_lat').text(lat);
        $('#drag_lon').text(lon);

        $.ajax({
            url : base_url +'home/LocationNameFromCoords',
            data : {
                lon: lon,
                lat: lat
            },
            success: function(data) {
                $('#address_components').text(data);
            }
        });

        $('#distance_slider').change(function() {
            var miles = $('#distance-value').text().trim();
            var meters = Math.round(parseInt(miles)*1609.344);
            // console.log(miles);

            var lat = $('#drag_lat').text();
            var lon = $('#drag_lon').text();
            // console.log(lat +' '+ lon);

            initialize(meters, lat, lng);
        });
    }
});