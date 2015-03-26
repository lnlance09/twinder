<?php
	$base_url = $this->config->base_url();
    $js_url = $base_url.'public/js/';
    // echo 'Num: '.$count.', '.$num_rows.'<br>';
?>
    <!--Write the page number for the JS to work -->
    <div class="hidden" id="page"><?php echo $page; ?></div>

    <div class="sub_load">
<?php
	for($i=0;$i<$num_rows;$i++) {
?>
        <div class="container-fluid">
            <div class="row">
<?php
        $start = $i*$per_row;
        $end = ($i == ($num_rows-1) ? $end_col : $start+$per_row);
        // echo 'Start: '.$start.', End: '.$end.'<br>';

        for($x=$start;$x<$end;$x++) {
            $id = $hot['users'][$x]['tinder_id'];
			$name = $hot['users'][$x]['name'];
			$age = $hot['users'][$x]['age'];
			$img = $hot['users'][$x]['profile_pic'];
            $likes = $hot['users'][$x]['like_count'];
            $link = $hot['users'][$x]['link'];

            $form = 'match';

            if($likes == 0 || $likes > 1) {
                $form .= 'es';
            } 

            // Define the tooltip HTML
            $tooltip = "<span class='tip'>".$name.", ".$age."</span><span class='sub_tip'> ".$likes." ".$form."</span>";
?>
                <div class="col-lg-2 thumbnail" onclick="location.href='<?php echo $base_url.$link; ?>'" data-toggle="tooltip" data-original-title="<?php echo $tooltip; ?>">
                    <img class="img-responsive" src="<?php echo $img; ?>" alt="<?php echo $name; ?>" />
                </div>
<?php
        }
?>
                <div class="clearfix"></div>
            </div>
        </div>
<?php
    }
?>
        <div id="append"></div>
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
        <button type="button" class="btn btn-success" id="see_more">See more (<?php echo number_format($left_over); ?>)</button>
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
                        page: $('#page')
                    };

            for(var index in params) {
                if(index == 'city') {
                    var val = params[index].val();

                    // Set the default value of the city to 'null'
                    if(val == '') {
                        var val = 'null';
                    }
                } else if(index == 'state') {
                    var val = params[index].text().trim();
            
                    // Set the default value of the state to 'new york'
                    if(val == '') {
                        var val = 'new york';
                    }
                } else if(index == 'gender') {
                    var val = params[index].text().trim().toLowerCase();

                    if(val === undefined) {
                        var val = 'both';
                    }
                } else {
                    var val = params[index].text().trim();
                }

                str += index +'/'+ val +'/';
            }

            var q = $('#users_autocomplete').val();
            return str.substr(9, str.length-10) +'?q='+ q;
        }

        /**
         * Format the title of the document based upon the search parameters
         */
        function DefineTitle() {
            var title = 'The hottest ';
            var gender = $('[name="gender"]').attr('title');
            var distance = $('#distance-value').text().trim();
            var city = $('#city').val();
            var state = $('#state_ref').text().trim();
            var min = $('#lower-value').text().trim();
            var max = $('#upper-value').text().trim();
            var page = $('#page').text().trim();
            var q = $('#users_autocomplete').val();

            // Format the gender
            if(gender == 0) {
                title += 'men '
            } else if(gender == 1) {
                title += 'women ';
            }

            // Format the age filter
            title += 'ages '+ min +' to '+ max +' within '+ distance +' miles of '+ city +', '+ state;
            
            if(page > 1) {
                title += ' page '+ page;
            }

            return title;
        }

        function ChangeTitleURL() {
            // var title = 'The hottest '+ key +' - WeTinder';
            var title = DefineTitle();
            var new_url = base_url +'hot/'+ GetFullURL();
            
            // Change the URL
            window.history.replaceState('', title, new_url);

            // Change the document's title
            document.title = title;
        }

        $('button#see_more').click(function(e) {
            $('#append').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');

            $('#page').text(<?php echo $new_page; ?>);
            var new_page = '<?php echo $new_page; ?>';
            var data = '<?php echo $q_string; ?>&page='+ new_page;
            // console.log(data);

            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
                ChangeTitleURL();

                $('[data-toggle="tooltip"]').tooltip({
                    placement: 'top',
                    html: true,
                });
            });
        });
    </script>
