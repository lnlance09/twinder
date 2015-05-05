$(document).ready(function() {
    var base_url = $('#base_url').text(); 
    var styles = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0993c7"}]}];
    var _city = 'San Francisco';
    var _state = 'California';
    var _lon = '-122.4206';
    var _lat = '37.7750';

    console.log($('#drag_lat').text());
    // Load the map and the results
    FinalizeMap($('#distance-value').text(), $('#drag_lat').text(), $('#drag_lon').text(), 10);
    RefreshResults(false);

    $('#city_addon').click(function(e) {
        $('#google_maps').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');
        
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(ShowPosition, ShowError);
        } else {
            alert('Geolocation is not supported by this browser');
        }
    });

    /**
     * Load Google Maps and load the results based upon the given criteria
     * @param {int} [miles] The number of miles. The distance filter
     * @param {decimal} [lat] The latitude coordinate
     * @param {decimal} [lon] The longitude coordinate
     * @param {int} [zoom] The zoom value
     */
    function FinalizeMap(miles, lat, lon, zoom) {
        if(zoom == null) {
            var tenth = miles*0.05;
            var zoom = parseInt(10)-parseInt(tenth);
        } 

        // Create a new position based upon lat & lon
        var LatLon = new google.maps.LatLng(lat, lon);
        var options = {
            center: LatLon,
            zoom: zoom,
            mapTypeControlOptions: {
                mapTypeIds: ['map_style']
            }
        };

        var map = new google.maps.Map(document.getElementById('google_maps'), options);
        map.mapTypes.set('map_style', new google.maps.StyledMapType(styles, {name: 'Twinder Radar'}));
        map.setMapTypeId('map_style');

        var marker = new google.maps.Marker({
                map: map,
                position: LatLon,
                draggable: true,
                animation: google.maps.Animation.DROP,
            });

        // Make the marker draggable
        google.maps.event.addListener(marker, 'dragend', function(marker) { 
            lat = marker.latLng.lat();
            lon = marker.latLng.lng();

            // Update the new coordinates on the map
            $('#drag_lat').text(lat);
            $('#drag_lon').text(lon);
            var loc = GetLocationName(lon, lat, true);
            map.setCenter(new google.maps.LatLng(lat, lon));
        });

        // Zoom in and center the marker upon click of the marker
        google.maps.event.addListener(marker, 'click', function() {
            map.setZoom(15);
            map.setCenter(marker.getPosition());
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
            radius: Math.ceil(miles/0.000621371)
        };

        circle = new google.maps.Circle(radius);
        circle.bindTo('center', marker, 'position');

        // Resize the map accordingly
        google.maps.event.trigger(map, 'resize');
        $('#google_maps').css('height', '250px');
    }

    /**
     * Get the state and city names of a place from its lat & lon coordinates
     * @param {decimal} [lon] The longitude coordinate
     * @param {decimal} [lat] The latitude coordinate
     */
    function GetLocationName(lon, lat, reset) {
        $.ajax({
            url: base_url +'home/LocationFromCoords',
            async: true,
            data: {
                lon: lon,
                lat: lat
            },
            success: function(data) {
                var obj = JSON.parse(data);
                var country = obj.country;
                var city = obj.city;
                var abbrev = obj.state;
                var state = obj.full_name;
                
                // Update the city and state
                $('#state_ref').text(state);
                $('#city').text(city);

                if(country == 'US') {
                    $('#location').val(city +', '+ state);
                } else {
                    $('#location').val(city +', '+ country);
                }
                
                // Load the new results
                RefreshResults(reset);
            }
        }); 
    }

    /**
     * Get the longitude and latitude coordinates of a place from its city and state
     * @param {string} [city] The name of the city
     * @param {string} [state] The full name of the state
     */
    function CoordsFromLocation(city, state, reset) {
        $.ajax({
            url: base_url +'home/LocationFromCity',
            async: true,
            data: {
                city: city,
                state: state
            },
            success: function(data) {
                var obj = JSON.parse(data);
                var lon = obj.lng;
                var lat = obj.lat;

                // Update the lat & lon coordinates
                $('#drag_lon').text(lon);
                $('#drag_lat').text(lat);

                // Update the city and state
                $('#state_ref').text(state);
                $('#city').text(city);
                $('#page').text(0);

                if(city == null) {
                    $('#location').val('');
                    var zoom = 6;
                } else {
                    $('#location').val(city +', '+ state);
                    var zoom = 14;
                }

                // Reload the map
                FinalizeMap($('#distance-value').text(), lat, lon, zoom);
                RefreshResults(reset);
            }
        });
    }

    /**
     * Change the title and URL of a document without reloading the page
     */
    function ChangeTitleURL() {
        var title = DefineTitle() +' - Twinder';
        var url = GetFullURL();
        var new_url = base_url +'hot/'+ url;
        window.history.replaceState('', title, new_url);
        document.title = title;
    }

    /**
     * Form the URL based upon all of the search parameters
     */
    function GetFullURL() {
        var str;
        var params = {
                    gender: $('[name="gender"]'), 
                    city: $('#city').text(), 
                    state: $('#state_ref'), 
                    distance: $('#distance-value'), 
                    min: $('#lower-value'), 
                    max: $('#upper-value'), 
                    page: $('#page')
                };

        for(var index in params) {
            switch(index) {
                case'city':

                    var val = params[index];
                    if(val == '') {
                        var val = _city;
                    }
                    break;

                case'state':

                    var val = params[index].text();
                    if(val == '') {
                        var val = _state;
                    }
                    break;

                case'gender':

                    var val = params[index].text().trim().toLowerCase();
                    if(val === undefined || val == '') {
                        var val = 'both';
                    }
                    break;

                case'page':

                    var val = parseInt(params[index].text().trim()) + parseInt(1);
                    break;

                default:
                    var val = params[index].text().trim();
                    break;
            }

            str += index +'/'+ val +'/';
        }

        var q = $('#users_autocomplete').val();
        return str.substr(9, str.length-10) +'?q='+ q;
    }

    /**
     * Grab all of the parameters to update the search results
     */
    function GetParams(reset) {
        var str;
        var params = {
                    gender: $('[name="gender"]').attr('title'), 
                    distance: $('#distance-value'),
                    lon: $('#drag_lon'), 
                    lat: $('#drag_lat'),  
                    min: $('#lower-value'), 
                    max: $('#upper-value'), 
                    page: $('#page')
                };

        if(reset === false) {
            params['q'] = $('#users_autocomplete');
        } 

        for(var index in params) {
            switch(index) {
                case'gender':

                    var val = params[index];
                    break;

                case'q':

                    var val = params[index].val();
                    break;

                case'page':

                    var val = parseInt(params[index].text().trim());
                    break;

                default:
                    var val = params[index].text();
            }

            str += index +'='+ val +'&';
        }

        return str.substr(9, str.length-10);
    }

    /**
     * Format the title of the document based upon the search parameters
     */
    function DefineTitle() {
        var title = 'Browse ';
        var gender = $('[name="gender"]').attr('title');
        var distance = $('#distance-value').text();
        var city = $('#city').text();
        var state = $('#state_ref').text();
        var min = $('#lower-value').text();
        var max = $('#upper-value').text();
        var page = $('#page').text();
        var q = $('#users_autocomplete').val();

        // Format the gender
        if(gender == 0) {
            title += 'men '
        } else if(gender == 1) {
            title += 'women ';
        }

        // Format the age 
        if(parseInt(min) > 18 || parseInt(max) < 50) {
            title += 'ages '+ min +' to '+ max +' ';
        }

        title += 'within '+ distance +' miles of ';

        // Format the city
        if(city != '' && city != 'null') {
            title +=  city +', ';
        }

        // Format the state
        if(state != '') {
            title += state;
        }

        return title;
    }

    /**
     * Load the new results with the updated parameters in the #hot_load div
     */
    function RefreshResults(reset) {
        $('#hot_load').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');
        if(reset === true) {
            $('#users_autocomplete').val('');
        }

        $('#hot_load').load(base_url +'hot/GetHottest', GetParams(reset), function() {
            $('#hot_load .ajax-loader').fadeOut();
            ChangeTitleURL();
        });
    }

    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    /*
     * In the event of a GeoLocation error, reference the error 
     */
    function ShowError(error) {
        // Get the lat & lon coordinates
        var set = $('#set_location').text();
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();
        // console.log('Lon: '+ lon +', Lat: '+ lat);

        // If the location parameters aren't set, then get the user's current location
        if(set == 'false') {
            $('#drag_lon').text(_lon);
            $('#drag_lat').text(_lat);
            GetLocationName(_lon, _lat, false);
        } else {
            // Load the new results
            RefreshResults(false);
        }

        // Load the initial results
        FinalizeMap($('#distance-value').text().trim(), lat, lon, null);

        switch(error.code) {
            case error.PERMISSION_DENIED:
                console.log("User denied the request for Geolocation");
                break;

            case error.POSITION_UNAVAILABLE:
                console.log("Location information is unavailable");
                break;

            case error.TIMEOUT:
                console.log("The request to get user location timed out");
                break;

            case error.UNKNOWN_ERROR:
                console.log("An unknown error occurred");
        }
    }

    /**
     * Determine the client's longitude and latitude coordinates based upon their position and load the maps and results based upon the search parameters
     * @param {object} The cliet's position
     */
    function ShowPosition(position) {
        // Get the lat & lon coordinates
        var set = $('#set_location').text();
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();
        // console.log('Lon: '+ lon +', Lat: '+ lat);

        // If the location parameters aren't set, then get the user's current location
        var lon = position.coords.longitude;
        var lat = position.coords.latitude;

        // Update the new lon & lat coordinates 
        $('#drag_lon').text(lon);
        $('#drag_lat').text(lat);

        GetLocationName(lon, lat, false);
        FinalizeMap($('#distance-value').text().trim(), lat, lon, null);
    }



    /**
     * 2 Location Autocomplete
     */
    $('#location').keyup(function(e) {
        var length = $(this).val().length;

        if(e.which != 27 && parseInt(length) > 3) {
            // Load the results
            $('#location_autocomplete').load(base_url +'home/GetLocations', 'q='+ $(this).val(), function() {
                $(this).slideDown();

                // Upon click of one of the items from the autocomplete panel
                $('#location_autocomplete ul li').click(function() {
                    $('#location_autocomplete').slideUp();
                    var city = $(this).attr('city');
                    var state = $(this).attr('state');

                    // Update the latitude and longitude coordinates
                    CoordsFromLocation(city, state, true);
                });
            });
        } else {
             $('#location_autocomplete').slideUp();
        }
    });



    /**
     * 3 Q Filter
     */
    $('#navbar_form').submit(function(e) {
        e.preventDefault();
        var redirect = GetFullURL();
        // console.log(base_url + redirect);
        window.location.href = base_url +'hot/'+ redirect;
    });



    /**
     * 4 Gender Filter
     */
    $('.gender_filter').click(function() {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $(this).siblings().attr('name', '');
        $(this).attr('name', 'gender');

        // Load the new results
        RefreshResults(false);
    });



    /**
     * 5 Age Slider
     */
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

    $("#age_slider").Link('lower').to($('#lower-value'));
    $("#age_slider").Link('upper').to($('#upper-value'));

    /**
     * Age Trigger
     */
    $('#age_slider').click(function() {
        RefreshResults(false);
    });



    /**
     * 6 Distance Filter Slider
     */
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

    $('#distance_slider').Link('lower').to($('#distance-value'));

    /**
     * Distance Filter Trigger
     */
    $('#distance_slider').change(function() {
        // Load the map again
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();
        var distance = $('#distance-value').text();
        FinalizeMap(distance, lat, lon, null);
        
        // Load the new results
        RefreshResults(false);
    }); 
});