$(document).ready(function() {
	// Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

    var rad = function(x) {
        return x * Math.PI/180;
    };

    var GetDistance = function(p1, p2) {
        var R = 6378137; // Earth’s mean radius in meter
        var dLat = rad(p2.lat() - p1.lat());
        var dLong = rad(p2.lng() - p1.lng());
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(rad(p1.lat())) * Math.cos(rad(p2.lat())) *
                Math.sin(dLong / 2) * Math.sin(dLong / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d = R * c;
        return d; 
    };

    $("#age_slider").noUiSlider({
        connect: true,
        behaviour: 'tap',
        start: [18, 50],
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

    $('.multiselect').multiselect({
        disableIfEmpty: false
    });


    var base_url = $('#base_url').text();
    var q = $('input[name=q]').val();
    var gender = $('#interested_in').val();
    var min = $('#lower-value').text();
    var max = $('#upper-value').text();
    var data = 'q='+ q +'&page=0&gender=both&min='+ min +'&max='+ max;

    $('#search_load').load(base_url +'search/Backend', data, function() {
    	$('.ajax-loader').fadeOut();
    });

    // Update the results upon change of the gender
    $('#interested_in').change(function() {
        var gender = $(this).val();
        var min = $('#lower-value').text();
        var max = $('#upper-value').text();
        var data = 'q='+ q +'&page=0&gender='+ gender +'&min='+ min +'&max='+ max;

        $('#search_load').load(base_url +'search/Backend', data, function() {
            $('.ajax-loader').fadeOut();

            var count = $('#search_results_num').text();
            console.log(count);
            $('#count_num').text(count);
        });
    });

    // Update the results upon change of the distance
    $('#age_slider').click(function() {
        var min = $('#lower-value').text();
        var max = $('#upper-value').text();
        var gender = $('#interested_in').val();
        var data = 'q='+ q +'&page=0&gender='+ gender +'&min='+ min +'&max='+ max;

        $('#search_load').load(base_url +'search/Backend', data, function() {
            $('.ajax-loader').fadeOut();

            var count = $('#search_results_num').text();
            $('#count_num').text(count);
        });
    });
});