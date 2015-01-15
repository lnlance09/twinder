$(document).ready(function() {
    var base_url = $('#base_url').text();
    var url = base_url +'search/Autocomplete';

    // Search autocomplete
    $('#users_autocomplete').keyup(function(e) {
        var val = $(this).val().trim();
        
        if(e.which == 27
        || val == '') {
            $('#autocomplete').slideUp('fast');
        } else {
            //$('#autocomplete').prepend('<div class="ajax-loader"><i class="fa fa-refresh fa-2x fa-spin"></i></div>');
            $('#autocomplete').slideDown('fast');
            var data = 'q='+ val +'&gender=both';

            $('#autocomplete').load(url, data, function() {
            //    $('#autocomplete .ajax-loader').fadeOut();
            }); 
        }
    });

    // Close autocomplete tab on click outside of tab
    $('body').bind('click', function(e) {
        var that = $('#autocomplete');

        if($(e.target).attr("id") != that.attr("id")) {
            $('#autocomplete').slideUp('fast');
        }
    });

    // Query to check for any updates and/or matches/messages
    /*
    window.setInterval(function() {
         $.ajax({
            url: base_url +'users/GetUpdates',
            success: function(data) {
                var obj = jQuery.parseJSON(data);
                console.log(obj);
            }
        });
    }, 10000);
    */

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