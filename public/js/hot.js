$(document).ready(function() {
    // Define the base URL
    var base_url = $('#base_url').text();

    var styles;
    // Check to see if the user's browser supports GeoLocation
    if(navigator.geolocation) {
         // Set the styling of Google Maps
        $.ajax({
            url: '/wetinder/public/js/maps.json',
            dataType: 'json',
            success: function(data) {
                styles = data;
                navigator.geolocation.getCurrentPosition(ShowPosition);
            }
        });
    } else {
        alert('Geolocation is not supported by this browser');
    }

    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    /**
     * Get the state and city names of a place from its lat & lon coordinates
     * @param {string} [base_url] The base URL of WeTinder
     * @param {decimal} [lon] The longitude coordinate
     * @param {decimal} [lat] The latitude coordinate
     */
    function GetLocationName(base_url, lon, lat) {
        $.ajax({
            url: '/wetinder/home/LocationFromCoords',
            data: {
                lon: lon,
                lat: lat
            },
            success: function(data) {
                var obj = JSON.parse(data);
                var city = obj.city;
                var abbrev = obj.state;
                var state = obj.full_name;

                // Update the city and state
                $('#state').val(state);
                $('#state_ref').text(state);
                $('#abbrev').text(abbrev);
                $('span.stateface').attr('class', 'stateface stateface-'+ abbrev.toLowerCase());
                $('#city').val(city);

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

    /**
     * Get the longitude and latitude coordinates of a place from its city and state
     * @param {string} [base_url] The base URL of WeTinder
     * @param {string} [city] The name of the city
     * @param {string} [state] The full name of the state
     */
    function CoordsFromLocation(base_url, city, state, abbrev) {
        $.ajax({
            url: '/wetinder/home/LocationFromCity',
            data: {
                city: city,
                state: abbrev
            },
            success: function(data) {
                var obj = JSON.parse(data);
                var lon = obj.lon;
                var lat = obj.lat;

                // Update the lat & lon coordinates
                $('#drag_lon').text(lon);
                $('#drag_lat').text(lat);

                // Update the city and state
                $('#state').val(state);
                $('#state_ref').text(state);
                $('#abbrev').text(abbrev);
                $('#top_stateface').attr('class', 'stateface stateface-'+ abbrev.toLowerCase());
                
                if(city === null) {
                    $('#city').val('');
                }

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

    /**
     * Change the title and URL of a document without reloading the page
     */
    function ChangeTitleURL() {
        // var title = 'The hottest '+ key +' - WeTinder';
        var title = DefineTitle();
        var new_url = base_url +'hot/'+ GetFullURL();
        
        // Change the URL
        window.history.replaceState('', title, new_url);

        // Change the document's title
        document.title = title;
    }

    /**
     * Form the URL based upon all of the search parameters
     */
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

                if(value == '') {
                    var value = 'null';
                }
            } else if(index == 'state') {
                var value = params[index].val();

                if(value == '') {
                    var value = 'new york';
                }
            } else if(index == 'gender') {
                var value = params[index].text().trim().toLowerCase();

                if(value === undefined) {
                    var value = 'both';
                }
            } else {
                var value = params[index].text().trim();
            }

            str += index +'/'+ value +'/';
        }

        var q = $('#users_autocomplete').val();
        return str.substr(9, str.length-10) +'?q='+ q;
    }

    /**
     * Grab all of the parameters to update the search results
     */
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

    /**
     * Format the title of the document based upon the search parameters
     */
    function DefineTitle() {
        var title = 'The hottest ';
        var gender = $('[name="gender"]').attr('title');
        var distance = $('#distance-value').text().trim();
        var city = $('#city').val();
        var state = $('#state_ref').text().trim();
        var min = $('#lower-value').text().trim();
        var max = $('#upper-value').text().trim();
        var page = $('#page').text().trim();
        var q = $('#users_autocomplete').val();

        // Format the gender
        if(gender == 0) {
            title += 'men '
        } else if(gender == 1) {
            title += 'women ';
        }

        // Format the age filter
        title += 'ages '+ min +' to '+ max +' within '+ distance +' miles of '+ city +', '+ state;
        
        if(page > 1) {
            title += ' page '+ page;
        }

        return title;
    }

    /**
     * Load Google Maps and load the results based upon the given criteria
     * @param {int} [miles] The number of miles. The distance filter
     * @param {decimal} [lat] The latitude coordinate
     * @param {decimal} [lon] The longitude coordinate
     */
    function Initialize(miles, lat, lon) {
        // Adjust the height of the map
        $('#google_maps').css('height', '250px');

        // Convert the miles to meters
        var meters = Math.ceil(miles/0.000621371);
        console.log(meters);

        // Set the position via latitude and longitude
        var latlng = new google.maps.LatLng(lat, lon);

        var mapOptions = {
            mapTypeControlOptions: {  
                mapTypeIds: ['Styled']  
            },  
            mapTypeId: 'Styled',
            center: latlng,
            zoom: 9,
        };

        // Select the Google Maps ID
        var el = document.getElementById('google_maps');
        var map = new google.maps.Map(el, mapOptions);

        // Style the map
        var styledMapType = new google.maps.StyledMapType(styles, {name: 'Styled'});  
        map.mapTypes.set('Styled', styledMapType); 

        // Define the marker properties
        var marker = new google.maps.Marker({
            map: map,
            position: latlng,
            draggable: true,
            title: 'Lance'
        });

        // Bounce the marker
        marker.setAnimation(google.maps.Animation.toggleBounce);

        /**
         * Make the marker draggable
         * 1
         */
        google.maps.event.addListener(marker, 'dragend', function(marker) { 
            console.log(marker);
            lat = marker.latLng.lat();
            lon = marker.latLng.lng();

            var new_pos = new google.maps.LatLng(lat, lon);
            //map.setCenter(new_pos);
            //marker.setAnimation(google.maps.Animation.toggleBounce);

            // Update the new coordinates on the map
            $('#drag_lat').text(lat);
            $('#drag_lon').text(lon);

            // Get the name of the new location and mark it on the page
            GetLocationName(base_url, lon, lat);
        });

        // Zoom in upon click of the marker
        google.maps.event.addListener(marker, 'click', function() {
            map.setZoom(17);
            map.setCenter(marker.getPosition());
        });

        // Get the zoom level upon zoom in/out
        google.maps.event.addListener(map, 'zoom_changed', function() { 
            var zoom = map.getZoom();
            console.log('zoom: '+ zoom);
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

        // Resize the map accordingly
        google.maps.event.trigger(map, 'resize');
    }



    /**
     * Determine the client's longitude and latitude coordinates based upon their position and load the maps and results based upon the search parameters
     * @param {object} The cliet's position
     */
    function ShowPosition(position) {
        // Get the lat & lon coordinates
        var set_location = $('#set_location').text().trim();
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();
        console.log('Lon: '+ lon +', Lat: '+ lat);

        // If the location parameters aren't set, then get the user's current location
        if(set_location == 'false') {
            var lon = position.coords.longitude;
            var lat = position.coords.latitude;

            // Update the new lon & lat coordinates 
            $('#drag_lon').text(lon);
            $('#drag_lat').text(lat);
        } 

        // Load the initial results
        GetLocationName(base_url, lon, lat);
        Initialize($('#distance-value').text().trim(), lat, lon, styles);

        /**
         * State Autocomplete
         * 2
         */
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

                        // Slide the state's autocomplete panel up
                        $('#state_autocomplete').slideUp();

                        // Close the city's autocomplete panel up too incase it was open
                        $('#city_autocomplete').slideUp();

                        // Update the latitude and longitude coordinates
                        CoordsFromLocation(base_url, null, state, abbrev);
                    });
                });
            } else {
                 $('#state_autocomplete').slideUp();
            }
        });



        /**
         * City Autocomplete
         * 3
         */
        $('#city').keyup(function(e) {
            if(e.which != 27) {
                // Get the value of the city and the state
                var state = $('#state_ref').text().trim();
                var abbrev = $('#abbrev').text().trim();

                // Define the query string
                var data = 'state='+ state +'&city='+ $(this).val();
                
                // Load the results
                $('#city_autocomplete').load(base_url +'home/GetCities', data, function() {
                    // Slide the autocomplete panel down
                    $('#city_autocomplete').slideDown();

                    // Upon click of one of the items from the autocomplete panel
                    $('#city_autocomplete ul li').click(function() {
                        // Get the city's name
                        var city = $(this).text().trim();

                        // Set the text field's value to the city's name
                        $('#city').val(city);

                        // Slide up the autocomplete panel
                        $('#city_autocomplete').slideUp();

                        // Update the latitude and longitude coordinates
                        CoordsFromLocation(base_url, city, state, abbrev);
                    });
                });
            } else {
                 $('#city_autocomplete').slideUp();
            }
        });



        /**
         * Q Filter
         * 4
         */
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



        /**
         * Gender Filter
         * 5
         */
        $('.gender_filter').click(function() {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');

            $(this).siblings().attr('name', '');
            $(this).attr('name', 'gender');

            var data = GetParams();
                        
            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
                ChangeTitleURL();
            });
        });



        /**
         * Age Slider
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
         * 6
         */
        $('#age_slider').click(function() {
            var data = GetParams();
            console.log('Age change: '+ data);

            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
                ChangeTitleURL();
            });
        });



        /**
         * Distance Filter Slider
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
         *  7
         */
        $('#distance_slider').change(function() {
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
    }
});