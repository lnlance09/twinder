$(document).ready(function() { 
    var base_url = $('#base_url').text();

    // Get all of the parameters from the URL
    var gender = $('#gender').text().trim();
    var city = $('#city').text().trim();
    var state = $('#state').text().trim();
    var distance = $('#distance').text().trim();
    var meters = parseInt($('#meters').text().trim());
    var min = $('#min').text().trim();
    var max = $('#max').text().trim();
    var q = $('#q').text().trim();
    var lon = $('#lon').text().trim();
    var lat = $('#lat').text().trim();
    var page = $('#page').text().trim();
    console.log(distance);

    // Get the longitude and latitude coordinates
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(ShowPosition);
    } else {
        alert('Geolocation is not supported by this browser');
    }

    // Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    // Age slider
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

    // Update the results upon change of the distance
    $('#age_slider').click(function() {
        var min = $('#lower-value').text();
        var max = $('#upper-value').text();
        var gender = $('#interested_in').val();
        var data = 'gender='+ gender +'&city='+ city +'&state='+ state +'&distance='+ distance +'&min='+ min +'&max='+ max +'&q='+ q +'&page='+ page;

        $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
            $('#hot_load .ajax-loader').fadeOut();

            // var count = $('#search_results_num').text();
            // $('#count_num').text(count);
        });
    });


    $('ul#sex_select li').click(function() {
        var gender = $(this).find('a').attr('title');
        var city = '';
        var state = '';
        var distance = $('#distance-value').text().trim();
        var min = $('#lower-value').text();
        var max = $('#upper-value').text();
        var page = parseInt(0);
        
        var data = 'gender='+ gender +
                    '&city='+ city +
                    '&state='+ state +
                    '&distance='+ distance +
                    '&min='+ min +
                    '&max='+ max +
                    '&page='+ page;
                    
        // Load the hottest
        $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
            $('#hot_load .ajax-loader').fadeOut();
        });
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

        // Load the hottest
        var data = 'gender='+ gender +
                    '&city='+ city +
                    '&state='+ state +
                    '&distance='+ distance +
                    '&min='+ min +
                    '&max='+ max +
                    '&q='+ q +
                    '&lon=' + lon +
                    '&lat='+ lat +
                    '&page='+ page;
        $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
            $('#hot_load .ajax-loader').fadeOut();
        });

        $('#drag_lat').text(lat);
        $('#drag_lon').text(lon);
        initialize(meters, lat, lon);

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

        $('#distance_slider').change(function() {
            var miles = $('#distance-value').text().trim();
            var meters = Math.round(parseInt(miles)*1609.344);
            // console.log(miles);

            var lat = $('#drag_lat').text();
            var lon = $('#drag_lon').text();
            // console.log(lat +' '+ lon);

            initialize(meters, lat, lon);
        });
    }
});