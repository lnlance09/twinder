$(document).ready(function() {
    var base_url = $('#base_url').text();
    var q = $('input[name=q]').val();
    var gender = $('#interested_in').val();
    var min = $('#lower-value').text();
    var max = $('#upper-value').text();
    var data = 'q='+ q +'&page=0&gender=both&min='+ min +'&max='+ max;

    $('#search_load').load(base_url +'search/Backend', data, function() {
        $('.ajax-loader').fadeOut();
    });

	// Write the CSS 'left' value to a span.
    function leftValue(value, handle, slider) {
        $(this).text(handle.parent()[0].style.left);
    }

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
    $('#age_slider').change(function() {
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