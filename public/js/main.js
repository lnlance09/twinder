$(document).ready(function() {
    var base_url = $('#base_url').text();
    var auth = $('#auth').text();
    // console.log(auth);

    // Query to check for any updates and/or matches/messages every 10 seconds
    if(auth != '') {
        console.log('hey');
        window.setInterval(function() {
             $.ajax({
                url: base_url +'users/GetUpdates',
                success: function(data) {
                    var obj = jQuery.parseJSON(data);
                    // console.log(obj);
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

    /*
    // Search autocomplete
    $('#users_autocomplete').keyup(function(e) {
        var val = $(this).val().trim();
        
        if(e.which == 27
        || val == '') {
            $('#autocomplete').slideUp('fast');
        } else {
            $('#autocomplete').slideDown('fast');
            var data = 'q='+ val +'&gender=both';

            $('#autocomplete').load(base_url +'search/Autocomplete', data, function() {
                $('#autocomplete_submit').click(function() {
                    window.location = base_url +'search?='+ val;
                });
            }); 
        }
    });

    // Close autocomplete tab on click outside of tab
    $('body').bind('click', function(e) {
        var that = $('#autocomplete');

        if($(e.target).attr('id') != that.attr('id')
        && $(e.target).attr('id') != 'autocomplete_submit') {
            $('#autocomplete').slideUp('fast');
        }
    });
    */
});