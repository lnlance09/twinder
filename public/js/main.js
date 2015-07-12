$(document).ready(function() {
    var base_url = $('#base_url').text();  
    var auth = $('#auth').text();
    var controller = $('#controller').text();
    // console.log(auth);

    //var url = 'https://m.facebook.com/login.php?skip_api_login=1&api_key=464891386855067&signed_next=1&next=https%3A%2F%2Fm.facebook.com%2Fv2.0%2Fdialog%2Foauth%3Fredirect_uri%3Dhttps%253A%252F%252Fwww.facebook.com%252Fconnect%252Flogin_success.html%26scope%3Dbasic_info%252Cemail%252Cpublic_profile%252Cuser_about_me%252Cuser_activities%252Cuser_birthday%252Cuser_education_history%252Cuser_friends%252Cuser_interests%252Cuser_likes%252Cuser_location%252Cuser_photos%252Cuser_relationship_details%26response_type%3Dtoken%26client_id%3D464891386855067%26ret%3Dlogin&cancel_uri=https%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html%3Ferror%3Daccess_denied%26error_code%3D200%26error_description%3DPermissions%2Berror%26error_reason%3Duser_denied%23_%3D_&display=touch&_rdr';
    //window.open(url, 'Login to Facebook');

    // Query to check for any updates and/or matches/messages every 10 seconds
    if(auth != '') {
        window.setInterval(function() {
             $.ajax({
                url: base_url +'users/GetUpdates',
                success: function(data) {
                    if(controller == 'matches') {
                        var match_id = $('#match_id').text();

                        $('#match_load').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');

                        setTimeout(function() {
                            // Load the results
                            var string = 'id='+ match_id +'&page=0';
                            $('#match_load').load(base_url +'matches/Thread', string, function() {
                                $('#match_load .ajax-loader').fadeOut();

                                $('[data-toggle="tooltip"]').tooltip({
                                    placement: 'top',
                                    html: true,
                                });
                            });
                        }, 0);
                    }
                    // var obj = JSON.parse(data);
                    // console.log(data);
                }
            });
        }, 10000);
    }

    // SVG script
    jQuery('img.svg').each(function() {
        var $img = jQuery(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        jQuery.get(imgURL, function(data) {
            // Get the SVG tag, ignore the rest
            var $svg = jQuery(data).find('svg');

            // Add replaced image's ID to the new SVG
            if(typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if(typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass +' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Replace image with new SVG
            $img.replaceWith($svg);
        }, 'xml');
    });
});