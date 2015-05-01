<?php
	$base_url = $this->config->base_url();
?>
    <!-- Write the page number for the JS to work -->
    <div class="hidden" id="load_page"><?php echo $page; ?></div>

    <div class="sub_load">
<?php
    if($count > 0) {
?>
        <div id="media_container">
<?php
    	for($i=0;$i<$end;$i++) {
            $user = $hot['users'][$i];

            // Show the highligthed text
            $high = (!empty($q) ? str_replace($q, '<span class="highlight">'.$q.'</span>', $user['bio']) : $user['bio']);
?>
            <div class="media" onclick="location.href='<?php echo $base_url.$user['link']; ?>'">
                <div class="media-left media-top">
                    <a href="<?php echo $base_url.$user['link']; ?>">
                        <img src="<?php echo $user['profile_pic']; ?>" class="media-object img-circle" alt="<?php echo $user['name']; ?>">
                    </a>
                </div>
                
                <div class="media-body text-left">
                    <h4 class="media-heading">
                        <a href="<?php echo $base_url.$user['link']; ?>" title="<?php echo $user['name']; ?>"><?php echo $user['name']; ?></a>, <?php echo $user['age']; ?>
                    </h4>

                    <p>
                        <?php echo BioLinks($high); ?>
                    </p>
                </div>
            </div>
<?php
        }
?>
        </div>

        <div id="append"></div>
<?php
    }
?>
    </div>
<?php
    if($count == 0) {
?>
    <div class="main_none">
        There are no results
    </div>
<?php
    } else {
        if(($page+1) < $pages) {
?>
    <div class="text-center">
        <button type="button" class="btn btn-default" id="see_more">see more (<?php echo number_format($left_over); ?>)</button>
    </div>
<?php
        }
    }
?>
    <script>
        var base_url = '<?php echo $base_url; ?>';

        function GetFullURL() {
            var str;
            var params = {
                        gender: $('[name="gender"]').text().trim(),
                        city: $('#city'), 
                        state: $('#state_ref'),
                        distance: $('#distance-value'), 
                        min: $('#lower-value'), 
                        max: $('#upper-value'), 
                        page: $('#load_page'),
                    };

            for(var index in params) {
                switch(index) {
                    case'gender':

                        var val = params[index].toLowerCase();
                        break;

                    case'city':

                        var val = params[index].text();

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

                    case'page':

                        var val = parseInt(params[index].text().trim()) + parseInt(1);
                        break;

                    default:
                        var val = params[index].text().trim();
                }

                str += index +'/'+ val +'/';
            }

            var q = $('#users_autocomplete').val();
            return str.substr(9, str.length-10) +'?q='+ q;
        }

        function ChangeTitleURL() {
            var new_url = base_url +'hot/'+ GetFullURL();
            window.history.replaceState('', '', new_url);
        }

        $('button#see_more').click(function(e) {
            $('#append').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');
            var data = '<?php echo $query; ?>&page=<?php echo $new_page; ?>';
            // console.log(data);

            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
                ChangeTitleURL();
            });
        });

        $('#hot_count_num').text('<?php echo FormatNumber($count); ?>');
    </script>