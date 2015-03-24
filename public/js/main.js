$(document).ready(function() {
    var base_url = $('#base_url').text().trim();  
    var auth = $('#auth').text();
    // console.log(auth);

    // Query to check for any updates and/or matches/messages every 10 seconds
    if(auth != '') {
        window.setInterval(function() {
             $.ajax({
                url: base_url +'users/GetUpdates',
                success: function(data) {
                    var obj = JSON.parse(data);
                    console.log(data);
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