<?php
	$base_url = $this->config->base_url();
    // echo 'Num: '.$count.', '.$num_rows.'<br>';

    if($count > 0) {
	   for($i=0;$i<$num_rows;$i++) {
?>
    <div class="container-fluid">
        <div class="row">
<?php
            $start = $i*$per_row;

            if($i == ($num_rows-1)) {
                $end = $end_col;
            } else {
                $end = $start+$per_row;
            }

            for($x=$start;$x<$end;$x++) {
                $id = $hot['users'][$x]['tinder_id'];
    			$name = $hot['users'][$x]['name'];
    			$age = $hot['users'][$x]['age'];
    			$img = $hot['users'][$x]['profile_pic'];
                $likes = $hot['users'][$x]['like_count'];
                $link = $hot['users'][$x]['link'];

                // Get the status code for each user's profile pic
                $img_url = 'http://images.gotinder.com/'.$id.'/172x172_'.$img;
?>
            <div class="col-lg-2" onclick="location.href='<?php echo $base_url.$link; ?>'">
                <div class="pic_wrap"> 
                    <object data="<?php echo $img_url; ?>" type="image/jpeg">
                        <img class="svg" src="<?php echo $base_url; ?>public/img/svg/kanye.svg" width="120" height="120" alt="<?php echo $name; ?>" />
                    </object>

                    <p>
                        <a href="<?php echo $base_url.$link; ?>" title="<?php echo $name; ?>"><?php echo trim($name); ?></a>, <?php echo $age; ?>
                        <!--<span class="sub_info"><?php echo $likes; ?></span>-->
                    </p>
                </div>
            </div>
<?php
            }
?>
            <div class="clearfix"></div>
        </div>
    </div>
<?php
	   }

       if($new_page != $pages) {
?>
    <hr>

    <div class="text-center">
        <button type="button" class="btn btn-success" id="see_more">See more (<?php echo $left_over; ?>)</button>
    </div>

<?php
        }
?>
    <script>
        var base_url = '<?php echo $base_url; ?>';
        // alert('<?php echo $count; ?>');
        $('#hot_result_num').text(<?php echo $count; ?>);

        $('button#see_more').click(function(e) {
            e.preventDefault();
            var new_page = '<?php echo $new_page; ?>';
            var data = '<?php echo $q_string; ?>&page='+ new_page;
            // console.log(data);

            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
            });
        });
    </script>

<?php
	} else {
?>
	<div class="main_none">
		There are no results
	</div>
<?php
	}
?>  
    <hr>

    <div class="container-fluid">
        <!-- Mr. State -->
        <div class="col-lg-4 text-center mrs_state">
            <h1 class="mrs">
                Male
            </h1>
        </div>

        <div class="col-lg-4 text-center mrs_state">
            <h1 class="mrs">
                <span class="stateface stateface-ny"></span>
            </h1>
        </div>

        <!-- Mrs. State -->
        <div class="col-lg-4 text-center mrs_state">
            <h1 class="mrs">
                Female
            </h1>
        </div>

        <div class="clearfix"></div>
    </div>

    <!-- Datamaps chart -->
    <div id="datamaps"></div>
    
    <hr><br>

    <!-- Draw the pie chart -->
    <canvas id="my_chart" width="100" height="100"></canvas>