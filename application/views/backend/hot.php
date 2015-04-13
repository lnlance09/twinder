<?php
	$base_url = $this->config->base_url();
    $js_url = $base_url.'public/js/';
?>
    <!--Write the page number for the JS to work -->
    <div class="hidden" id="load_page"><?php echo $page; ?></div>

    <div class="sub_load">
<?php
    if($count > 0) {
?>
        <div id="media_container">
<?php
    	for($i=0;$i<$end;$i++) {
            // Define the style
            $style = ($i == ($end-1) ? 'style="border-bottom: solid 1px #ccc;"' : NULL);
            $id = $hot['users'][$i]['tinder_id'];
    		$name = $hot['users'][$i]['name'];
    		$age = $hot['users'][$i]['age'];
            $bio = $hot['users'][$i]['bio'];
    		$img = $hot['users'][$i]['profile_pic'];
            $link = $hot['users'][$i]['link'];
?>
            <div class="media" onclick="location.href='<?php echo $base_url.$link; ?>'" <?php echo $style; ?>>
                <div class="media-left media-top">
                    <a href="<?php echo $base_url.$link; ?>">
                        <img src="<?php echo $img; ?>" class="media-object img-circle" alt="<?php echo $name; ?>">
                    </a>
                </div>
                
                <div class="media-body text-left">
                    <h4 class="media-heading">
                        <a href="<?php echo $base_url.$link; ?>" title="<?php echo $name; ?>"><?php echo $name; ?></a>, <?php echo $age; ?>
                    </h4>

                    <p>
                        <?php echo BioLinks($bio); ?>
                    </p>
                </div>
            </div>
<?php
        }
    }
?>
            <div id="append"></div>
        </div>
    </div>
<?php
    if($count == 0) {
?>
    <div class="main_none">
        There are no results
    </div>
<?php
    } else {
        if($new_page != $pages) {
?>
    <div class="text-center">
        <button type="button" class="btn btn-default" id="see_more">See more (<?php echo number_format($left_over); ?>)</button>
    </div>
<?php
        }
    }
?>
    <script>
        var base_url = '<?php echo $base_url; ?>';
        $('#hot_result_num').text(FormatNumber(<?php echo $count; ?>));

        function FormatNumber(num) {
            if(num > 1000) {
                var floor = Math.floor(num/1000);
                var decimal = Math.ceil(num/100)-(floor*10); 
                return floor +'.'+ decimal +'K';
            } else {
                return num;
            }
        }

        function GetFullURL() {
            var str;
            var params = {
                        gender: $('[name="gender"]'), 
                        city: $('#city'), 
                        state: $('#state_ref'), 
                        distance: $('#distance-value'), 
                        min: $('#lower-value'), 
                        max: $('#upper-value'), 
                        page: $('#load_page')
                    };

            for(var index in params) {
                switch(index) {
                    case'city':

                        var val = params[index].val();

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

                    case'gender':

                        var val = params[index].text().trim().toLowerCase();

                        if(val === undefined || val == '') {
                            var val = 'both';
                        }
                        break;

                    case'page':

                        var val = parseInt(params[index].text().trim()) + parseInt(1);
                        break;

                    default:
                        var val = params[index].text().trim();
                        break;
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
            var new_page = '<?php echo $new_page; ?>';
            var data = '<?php echo $q_string; ?>&page='+ new_page;
            // console.log(data);

            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
                ChangeTitleURL();
            });
        });
    </script>
