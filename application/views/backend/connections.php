<?php
    // Define the base URL
	$base_url = $this->config->base_url();
	
    if($count > 0) {
?>
    <div id="media_container">
<?php
        // Loop thru each user
        for($i=0;$i<$end;$i++) {
			$user = $connections[$i]['user_info'];
            $_id = $user['tinder_id'];
			$name = $user['first_name'];
			$age = $user['age'];
            $bio = $user['bio'];
            $img = $user['profile_pic'];

            // Set the link
            $link = $base_url.$user['link'];
?>
        <div class="media" onclick="location.href='<?php echo $link; ?>'">
            <div class="media-left media-top">
                <a href="<?php echo $link; ?>">
                    <img src="<?php echo $img; ?>" class="media-object img-circle" alt="<?php echo $name; ?>">
                </a>
            </div>
            
            <div class="media-body text-left">
                <h4 class="media-heading">
                    <a href="<?php echo $link; ?>" title="<?php echo $name; ?>"><?php echo $name; ?></a>, <?php echo $age; ?>
                </h4>

                <p>
                    <?php echo $bio; ?>
                </p>
            </div>
        </div>
<?php
		}
?>
    </div>
<?php
        if($new_page != $pages) {
?>
    <div class="text-center">
        <button type="button" class="btn btn-primary" id="see_more">See more (<?php echo number_format($left_over); ?>)</button>
    </div>
<?php
        }
?>
    <!-- JS -->
     <script>
        var base_url = '<?php echo $base_url; ?>';
        var type = '<?php echo $type; ?>';
        var tinder_id = '<?php echo $id; ?>';

        $('button#see_more').click(function(e) {
            $('#con_load_box .text-center').prepend('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');

            e.preventDefault();
            var new_page = '<?php echo $new_page; ?>';
            var data = 'type='+ type +'&page='+ new_page +'&id='+ tinder_id;
    
            $('#con_load_box').load(base_url +'users/GetConnections', data, function() {
                $('.ajax-loader').fadeOut();
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