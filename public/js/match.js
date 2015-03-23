$(document).ready(function() {
    var base_url = $('#base_url').text().trim(); 
    var tinder_id = $('#user_tinder_id').text();
    var match_id = $('#match_id').text();

    // Load the results
    var data = 'id='+ match_id +'&page=0';
    $('#match_load').load(base_url +'matches/Thread', data, function() {
        $('#match_load .ajax-loader').fadeOut();
    });
});