$(document).ready(function() {
    var base_url = $('#base_url').text(); 

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
                    var state = $(this).text().trim();

                    // Slide up the autocomplete panelx
                    $('#state_autocomplete').slideUp();
                    $('#city_autocomplete').slideUp();

                    // Update the city and state
                    $('#state').val(state);
                    $('#state_ref').text(state);
                    $('#city').val('');
                });
            });
        } else {
             $('#state_autocomplete').slideUp();
        }
    });

    $('#city').keyup(function(e) {
        if(e.which != 27) {
            // Get the value of the city and the state
            var state = $('#state_ref').text().trim();
            var data = 'state='+ state +'&city='+ $(this).val();
            
            // Load the results
            $('#city_autocomplete').load(base_url +'home/GetCities', data, function() {
                // Slide the autocomplete panel down
                $('#city_autocomplete').slideDown();

                // Upon click of one of the items from the autocomplete panel
                $('#city_autocomplete ul li').click(function() {
                    // Slide up the autocomplete panel
                    $('#city_autocomplete').slideUp();
                    $('#city').val($(this).text().trim());

                    // Get the latitude and longitude cards
                    var lon = $(this).attr('lon');
                    var lat = $(this).attr('lat');
                    var data = 'lon='+ lon +'&lat='+ lat;
                    // console.log(data);
                    
                    $('#lance_load').load(base_url +'home/LanceBackend', data, function() {

                    });
                });
            });
        } else {
             $('#city_autocomplete').slideUp();
        }
    });
});