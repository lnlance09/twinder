$(document).ready(function() {
    // Define the base URL
    var base_url = $('#base_url').text();

    // Check to see if the user's browser support GeoLocation
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(ShowPosition);
    } else {
        alert('Geolocation is not supported by this browser');
    }

    // Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    function ChangeTitleURL() {
        var title = 'The hottest '+ key +' - WeTinder';
        var new_url = $('#base_url').text() +'hot/'+ GetFullURL();
        
        // Change the URL and the document's title
        window.history.replaceState('', title, new_url);
        document.title = title;
    }

    // Get the city, state and abbreviation of a place from it's lat & lon coordinates
    function GetLocationName(base_url, lon, lat) {
        $.ajax({
            url: base_url +'home/LocationFromCoords',
            data: {
                lon: lon,
                lat: lat
            },
            success: function(data) {
                // Parse the JSON
                var obj = JSON.parse(data);
                var city = obj.city;
                var abbrev = obj.state;
                var state = obj.full_name;

                // Update the city and state
                $('#state').val(state);
                $('#state_ref').text(state);
                $('span.stateface').attr('class', 'stateface stateface-'+ abbrev.toLowerCase());
                $('#city').val(city);
            }
        }); 
    }

    // Get the lon & lat coordinates from either a city or a state
    function CoordsFromLocation(base_url, city, state) {
        $.ajax({
            url: base_url +'home/LocationFromCity',
            data: {
                city: city,
                state: state
            },
            success: function(data) {
                // Parse the JSON
                var obj = JSON.parse(data);
                var lon = obj.lon;
                var lat = obj.lat;

                // Update the lat & lon coordinates
                $('#drag_lon').text(lon);
                $('#drag_lat').text(lat);

                // Reload the map
                Initialize($('#distance-value').text(), lat, lon);

                // Define the query string
                var params = GetParams();
                console.log('State or City change: '+ params);

                // Load the results
                $('#hot_load').load(base_url +'hot/GetHottest', params, function() {
                    ChangeTitleURL();
                });
            }
        });
    }

    function Initialize(miles, lat, lon) {
        // Adjust the height of the map
        $('#google_maps').css('height', '250px');

        // Convert the miles to meters
        var meters = Math.ceil(miles/0.000621371);
        // console.log(meters);

        // Set the styling
        var styles = [{"stylers":[{"saturation":-100}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0099dd"}]},{"elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{}];

        // Set the position via latitude and longitude
        var latlng = new google.maps.LatLng(lat, lon);

        var mapOptions = {
            mapTypeControlOptions: {  
                mapTypeIds: ['Styled']  
            },  
            mapTypeId: 'Styled',
            center: latlng,
            zoom: 6,
        };

        // Select the google maps ID
        var el = document.getElementById('google_maps');
        var map = new google.maps.Map(el, mapOptions);
        var styledMapType = new google.maps.StyledMapType(styles, {name: 'Styled'});  
        map.mapTypes.set('Styled', styledMapType); 

        // Define the marker properties
        var marker = new google.maps.Marker({
            map: map,
            position: latlng,
            draggable: true,
        });

        // Bounce the marker
        marker.setAnimation(google.maps.Animation.BOUNCE);

        // Make the marker draggable 
        google.maps.event.addListener(marker, 'dragend', function(marker) { 
            lat = marker.latLng.lat();
            lon = marker.latLng.lng();

            // Update the new coordinates on the map
            $('#drag_lat').text(lat);
            $('#drag_lon').text(lon);

            // Get the name of the new location and mark it on the page
            GetLocationName(base_url, lon, lat);
        });

        // Draw the radius circle
        var radius = {
            strokeColor: '#fd923a',
            strokeOpacity: 0.8,
            strokeWeight: 1,
            fillColor: '#fd923a',
            fillOpacity: 0.25,
            map: map,
            center: latlng,
            radius: meters
        };

        circle = new google.maps.Circle(radius);
        circle.bindTo('center', marker, 'position');
    }

    // Grab all of the parameters to update the search results
    function GetParams() {
        var str;
        var params = {
                    gender: $('[name="gender"]').attr('title'), 
                    distance: $('#distance-value'),
                    lon: $('#drag_lon'), 
                    lat: $('#drag_lat'),  
                    min: $('#lower-value'), 
                    max: $('#upper-value'), 
                    page: $('#page'),
                    q: $('#users_autocomplete')
                };

        for(var index in params) {
            if(index == 'gender') {
                var value = params[index];
            } else if(index == 'q') {
                var value = params[index].val();
            } else {
                var value = params[index].text().trim();
            }

            str += index +'='+ value +'&';
        }

        return str.substr(9, str.length-10);
    }

    function GetFullURL() {
        var str;
        var params = {
                    gender: $('[name="gender"]'), 
                    city: $('#city'), 
                    state: $('#state_ref'), 
                    distance: $('#distance-value'), 
                    min: $('#lower-value'), 
                    max: $('#upper-value'), 
                    page: $('#page')
                };

        for(var index in params) {
            if(index == 'city') {
                var value = params[index].val();
            } else if(index == 'gender') {
                var value = params[index].text().trim().toLowerCase();
            } else {
                var value = params[index].text().trim();
            }

            str += index +'/'+ value +'/';
        }

        var q = $('#users_autocomplete').val();
        return str.substr(9, str.length-10) +'?q='+ q;
    }

    // Get the user's current longitude and latitude coordinates
    function ShowPosition(position) {
        // Get the lat & lon coordinates
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();

        // If the location parameters aren't set, then get the user's current location
        if(lon == 0 || lat == 0) {
            var lon = position.coords.longitude;
            var lat = position.coords.latitude;
        } 

        // Update the new lon & lat coordinates 
        $('#drag_lon').text(lon);
        $('#drag_lat').text(lat);

        // Define the query string
        var data = GetParams();
        console.log('Initital load: '+ data);

        // Load the results
        $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
            $('#hot_load .ajax-loader').fadeOut();

            // Get the distance
            var distance = $('#distance-value').text();

            // Redefine the map
            Initialize(distance, lat, lon);

            // Update the new location name
            GetLocationName(base_url, lon, lat);

            /* Autocomplete for States and Cities */
            // State
            $('#state').keyup(function(e) {
                if(e.which != 27) {
                    var value = $(this).val(); 
                    var data = 'state='+ value;
                    
                    // Load the results
                    $('#state_autocomplete').load(base_url +'home/GetStates', data, function() {
                        $('#state_autocomplete').slideDown();

                        // Upon click of one of the items from the autocomplete panel
                        $('#state_autocomplete ul li').click(function() {
                            // Get the state's name and abbreviation
                            var abbrev = $(this).attr('name');
                            var state = $(this).text().trim();

                            // Slide the autocomplete panel up
                            $('#state_autocomplete').slideUp();

                            // Update the text field's value with the state's anme
                            $('#state').val(state);

                            // Update the hidden div for the JS
                            $('#state_ref').text(state);

                            // Update the stateface typefont
                            $('span.stateface').attr('class', 'stateface stateface-'+ abbrev);

                            // Set the city's value to nothing
                            $('#city').val('');

                            // Close the city's autocomplete panel up too incase it was open
                            $('#city_autocomplete').slideUp();

                            // Update the latitude and longitude coordinates
                            CoordsFromLocation(base_url, null, abbrev);
                        });
                    });
                } else {
                     $('#state_autocomplete').slideUp();
                }
            });

            // City
            $('#city').keyup(function(e) {
                if(e.which != 27) {
                    // Get the value of the city and the state
                    var value = $(this).val(); 
                    var state = $('#state_ref').text().trim();

                    // Define the query string
                    var data = 'state='+ state +'&city='+ value;
                    
                    // Load the results
                    $('#city_autocomplete').load(base_url +'home/GetCities', data, function() {
                        // Slide the autocomplete panel down
                        $('#city_autocomplete').slideDown();

                        // Upon click of one of the items from the autocomplete panel
                        $('#city_autocomplete ul li').click(function() {
                            // Get the city's name
                            var city = $(this).text().trim();

                            // Slide up the autocomplete panel
                            $('#city_autocomplete').slideUp();

                            // Set the text field's value to the city's name
                            $('#city').val(city);

                            // Update the latitude and longitude coordinates
                            CoordsFromLocation(base_url, city, state);
                        });
                    });
                } else {
                     $('#city_autocomplete').slideUp();
                }
            });


            /* Q FILTER */
            $('#users_autocomplete').keyup(function(e) {
                if(e.which != 27) {
                    var data = GetParams();
                    console.log('Q change: '+ data);
                    
                    $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                        $('#hot_load .ajax-loader').fadeOut();
                        ChangeTitleURL();
                    });
                } 
            });


            /* GENDER FILTER */
            $('.gender_filter').click(function() {
                $(this).siblings().removeClass('active');
                $(this).addClass('active');

                $(this).siblings().attr('name', '');
                $(this).attr('name', 'gender');

                // Define the query string
                var data = GetParams();
                            
                // Load the hottest
                $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                    $('#hot_load .ajax-loader').fadeOut();
                    ChangeTitleURL();
                });
            });

            /* AGE FILTER */
            $("#age_slider").noUiSlider({
                connect: true,
                behaviour: 'tap',
                start: [$('#lower-value').text(), $('#upper-value').text()],
                step: 1,
                format: wNumb({
                    decimals: 0
                }),
                range: {
                    'min': [18],
                    'max': [50]
                }
            });

            // Update the new values on the page accordingly
            $("#age_slider").Link('lower').to($('#lower-value'));
            $("#age_slider").Link('upper').to($('#upper-value'));

            // Update the results upon change of the distance
            $('#age_slider').click(function() {
                // Define the query string
                var data = GetParams();
                console.log('Age change: '+ data);

                $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                    $('#hot_load .ajax-loader').fadeOut();
                    ChangeTitleURL();
                });
            });

            /* DISTANCE FILTER */
            $('#distance_slider').noUiSlider({
                start: $('#distance-value').text(),
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

            // Update the new distance value on the page
            $('#distance_slider').Link('lower').to($('#distance-value'));

            // Upon change of the distance value
            $('#distance_slider').change(function() {
                // Define the query string
                var data = GetParams();
                console.log('Distance change: '+ data);

                // Load the new results
                $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                    $('#hot_load .ajax-loader').fadeOut();

                    // Get the lon & lat coordinates plus the distance value
                    var lon = $('#drag_lon').text();
                    var lat = $('#drag_lat').text();
                    var distance = $('#distance-value').text();
                    // console.log(lon +', '+ lat);

                    // Reload the map again with the new radius circle 
                    Initialize(distance, lat, lon);
                    ChangeTitleURL();
                });
            });
        });
    }
});