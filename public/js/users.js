$(document).ready(function() {
    var base_url = $('#base_url').text();
    var tinder_id = $('#user_tinder_id').text();
    var can_edit = $('#can_edit').text().trim();
    
    var lat = $('#lat').text().trim();
    var lon = $('#lon').text().trim();
    var radius = Math.round(parseInt($('#radius').text())*1609.344);
    // console.log(radius);

    $('#other_trigger').click(function() {
        $('#other_box').slideDown();
    });

    //console.log(can_edit);
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
                    url : base_url +'users/UpdateProfile',
                    type: 'POST',
                    data : {
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

    // Load the connections
    $('#con_load_box').load(base_url +'users/GetConnections', 'type=matches&page=0&id='+ tinder_id);

    // Search thru connections upon keyup of the input field
    $('#search_connections').keyup(function(e) {
        var q = $(this).val();
        var type = $('#connection_active').attr('name');
        $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type + '&page=0&id='+ tinder_id +'&q='+ q);
    });

    // Load the connections upon hover
    $('.timer_box').click(function() {
        $('.timer_box').attr('id', '');
        $(this).attr('id', 'connection_active');
        
        var type = $(this).attr('name');
        $('#type_name').text(type);
        $('#search_connections').attr('placeholder', 'Search '+ type);

        // Change the font-awesome icon
        if(type == 'likes') {
            var font_awesome = 'thumbs-up';
        } else if(type == 'passes') {
            var font_awesome = 'thumbs-down';   
        } else {
            var font_awesome = 'heart';
        }

        $('#fa_type').attr('class', 'fa fa-'+ font_awesome +' fa-2x');

        $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type + '&page=0&id='+ tinder_id, function() {
           
        });
    });

    // Change the pic upon click
    $('ul#sub_pics li').click(function(d) {
        d.preventDefault();
        var pic = $(this).attr('name');
        $('#main_img').attr('src', pic);
    });

    function initialize(lat, lon) {
        var latlng = new google.maps.LatLng(lat, lon);

        var mapOptions = {
            center: latlng,
            zoom: 6,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            position: latlng
        };

        var el = document.getElementById('ping_map');
        var map = new google.maps.Map(el, mapOptions);

        // Set the marker
        var marker = new google.maps.Marker({
            map: map,
            position: latlng
        });

        // Bounce the marker
        marker.setAnimation(google.maps.Animation.DROP);

        var sunCircle = {
            strokeColor: '#c3fc49',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#c3fc49',
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
            closeBoxMargin: "12px 4px 2px 2px",
            closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
            infoBoxClearance: new google.maps.Size(1, 1)
        });
        

        google.maps.event.addListener(marker, 'click', function() {
            infobox.open(map, this);
        });
    }

    initialize(lat, lon);
});



