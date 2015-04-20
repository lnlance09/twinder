    var base_url = $('#base_url').text(); 
    var styles = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#aadd55"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#0993c7"}]}];

    // Check to see if the user's browser supports GeoLocation
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(ShowPosition, ShowError);
    } else {
        alert('Geolocation is not supported by this browser');
    }

    // 2 State Autocomplete
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

                    // Slide up the autocomplete panelx
                    $('#state_autocomplete').slideUp();
                    $('#city_autocomplete').slideUp();

                    // Update the latitude and longitude coordinates
                    CoordsFromLocation(null, state, abbrev);

                    $.ajax({
                        url: base_url +'hot/HottestUser',
                        data: {
                            gender: 1,
                            state: abbrev
                        },
                        success: function(data) {
            
                        }
                    });
                });
            });
        } else {
             $('#state_autocomplete').slideUp();
        }
    });

    // 3 City Autocomplete
    $('#city').keyup(function(e) {
        if(e.which != 27) {
            // Get the value of the city and the state
            var state = $('#state_ref').text().trim();
            var abbrev = $('#abbrev').text().trim();
            var data = 'state='+ state +'&city='+ $(this).val();
            
            // Load the results
            $('#city_autocomplete').load(base_url +'home/GetCities', data, function() {
                // Slide the autocomplete panel down
                $('#city_autocomplete').slideDown();

                // Upon click of one of the items from the autocomplete panel
                $('#city_autocomplete ul li').click(function() {
                    // Slide up the autocomplete panel
                    $('#city_autocomplete').slideUp();

                    // Update the latitude and longitude coordinates
                    CoordsFromLocation($(this).text().trim(), state, abbrev);
                });
            });
        } else {
             $('#city_autocomplete').slideUp();
        }
    });

    // 4 Gender Filter
    $('.gender_filter').click(function() {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $(this).siblings().attr('name', '');
        $(this).attr('name', 'gender');

        // Load the new results
        RefreshResults();
    });

    // 5 Age Slider
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

    // Age Trigger
    $('#age_slider').click(function() {
        RefreshResults();
    });

    // 6 Distance Filter Slider
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

    // Distance Filter Trigger
    $('#distance_slider').change(function() {
        // Load the map again
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();
        var distance = $('#distance-value').text();
        FinalizeMap(distance, lat, lon, null);
        RefreshResults();
    });

    // Reflect the changes from the sliders on the document
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    // Determine the client's longitude and latitude coordinates based upon their position and load the maps and results based upon the search parameters
    function ShowPosition(position) {
        // Get the lat & lon coordinates
        var set = $('#set_location').text().trim();
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();

        // If the location parameters aren't set, then get the user's current location
        if(set == 'false') {
            var lon = position.coords.longitude;
            var lat = position.coords.latitude;

            // Update the new lon & lat coordinates 
            $('#drag_lon').text(lon);
            $('#drag_lat').text(lat);
            GetLocationName(lon, lat);
        } else {
            RefreshResults();
            LoadChart($('#abbrev').text());
        }

        // Load the initial results
        FinalizeMap($('#distance-value').text(), lat, lon, null);
    }

    // In the event of a GeoLocation error, reference the error 
    function ShowError(error) {
        // Get the lat & lon coordinates
        var set = $('#set_location').text().trim();
        var lon = $('#drag_lon').text();
        var lat = $('#drag_lat').text();

        // If the location parameters aren't set, then get the user's current location
        if(set == 'false') {
            // Update the new lon & lat coordinates with the default to NYC
            var lon = -122.4206;
            var lat = 37.7750;
            $('#drag_lon').text(lon);
            $('#drag_lat').text(lat);
            GetLocationName(lon, lat);
        } else {
            // Load the new results
            RefreshResults();
            LoadChart($('#abbrev').text());
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
                break;
        }
    }

    // Load the new results with the updated parameters in the #hot_load div
    function RefreshResults() {
        $('#hot_load').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');
        // console.log(GetParams());

        $('#hot_load').load('http://twinder.io/hot/GetHottest', GetParams(), function() {
            $('#hot_load .ajax-loader').fadeOut();
            ChangeTitleURL();
        });
    }

    // Load Google Maps and load the results based upon the given criteria
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
            var loc = GetLocationName(lon, lat);
            // console.log('Log: '+ loc);

            // If the location is in the US, then center the marker
            if(loc == 'true') {
                map.setCenter(new google.maps.LatLng(lat, lon));
            } else {
                // Show a modal saying that WeTinder is limited to the US
                $('#bounds_modal').modal('show');
            }
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

    // Get the state and city names of a place from its lat & lon coordinates
    function GetLocationName(lon, lat) {
        var result = '';

        $.ajax({
            url: base_url +'home/LocationFromCoords',
            async: false, 
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

                if(country == 'US') {
                    // Update the city and state
                    $('#state').val(state);
                    $('#state_ref').text(state);
                    $('#abbrev').text(abbrev);
                    $('#top_stateface').attr('class', 'stateface stateface-'+ abbrev.toLowerCase());
                    $('h2 .stataface').text(state);
                    $('#city').val(city);

                    // Set the result to true
                    result = 'true';

                    // Load the pie chart
                    LoadChart(abbrev);
                    RefreshResults();
                } else {
                    result = 'false';
                }
            }
        }); 

        return result;
    }

    // Get the longitude and latitude coordinates of a place from its city and state
    function CoordsFromLocation(city, state, abbrev) {
        $.ajax({
            url: base_url +'home/LocationFromCity',
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
                $('#state').val(state);
                $('#state_ref').text(state);
                $('#abbrev').text(abbrev);
                $('#top_stateface').attr('class', 'stateface stateface-'+ abbrev.toLowerCase());
                $('h2 .stateface').text(state);
                $('#page').text(0);

                if(city == null) {
                    $('#city').val('');
                    var zoom = 6;
                } else {
                    $('#city').val(city);
                    var zoom = 14;
                }

                // Reload the map
                FinalizeMap($('#distance-value').text(), lat, lon, zoom);
                LoadChart(abbrev);
                RefreshResults();
            }
        });
    }

    function LoadChart(state) {
        $('#chart_load').load(base_url +'home/DrawPieChart', 'state='+ state, function() {
            $('#chart_load .ajax-loader').fadeOut();

            $('[data-toggle="tooltip"]').tooltip({
                placement: 'top',
                html: true,
            });
        });
    }

    // Change the title and URL of a document without reloading the page
    function ChangeTitleURL() {
        var title = DefineTitle() +' - Twinder';
        var url = GetFullURL();
        var new_url = base_url +'hot/'+ url;
        
        // Change the URL
        window.history.replaceState('', title, new_url);
        document.title = title;
    }

    // Form the URL based upon all of the search parameters
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
            switch(index) {
                case'city':

                    var val = params[index].val();

                    // Set the default value of the city to 'null'
                    if(val == '') {
                        var val = 'null';
                    }
                    break;

                case'state':

                    var val = params[index].text();

                    // Set the default value of the state to 'new york'
                    if(val == '') {
                        var val = 'new york';
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

    // Grab all of the parameters to update the search results
    function GetParams() {
        var str;
        var params = {
                    gender: $('[name="gender"]').attr('title'), 
                    distance: $('#distance-value'),
                    lon: $('#drag_lon'), 
                    lat: $('#drag_lat'), 
                    state: $('#state'),
                    abbrev: $('#abbrev'), 
                    min: $('#lower-value'), 
                    max: $('#upper-value'), 
                    page: $('#page'),
                    q: $('#users_autocomplete'),
                    count: $('#hot_count')
                };

        for(var index in params) {
            switch(index) {
                case'gender':

                    var val = params[index];
                    break;

                case'q':
                case'state':

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

    // Format the title of the document based upon the search parameters
    function DefineTitle() {
        var title = 'The hottest ';
        var gender = $('[name="gender"]').attr('title');
        var distance = $('#distance-value').text();
        var city = $('#city').val();
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