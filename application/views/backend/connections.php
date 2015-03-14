<?php
    // Define the base URL
	$base_url = $this->config->base_url();
	
    if($count > 0) {
?>
    <ul class="list-group">
<?php
        // Loop thru each user
        for($i=0;$i<$end;$i++) {
			$user = $connections[$i]['user_info'];
			$name = trim($user['first_name']);
			$id = $user['tinder_id'];
			$age = $user['age'];
			$link = $user['link'];

            // Format the link to the user's picture
            $img = 'http://images.gotinder.com/'.$id.'/84x84_'.$user['profile_pic'];
?>
		<li class="list-group-item" onclick="location.href='<?php echo $base_url.$link; ?>'">
            <h4 class="list-group-item-heading">
                <!-- Print the user's pic -->
                <img src="<?php echo $img; ?>" class="img-circle" alt="<?php echo $name; ?>">

                <!-- Print their name and age -->
                <a href="<?php echo $base_url.$link; ?>" title="<?php echo $name; ?>"><?php echo $name; ?></a>, <?php echo $age; ?>
            </h4>
    
            <p class="list-group-item-text"></p>
        </li>
<?php
		}
?>
    </ul>
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

        $('button#see_more').click(function(e) {
            e.preventDefault();
            var new_page = '<?php echo $new_page; ?>';
            var data = 'type='+ type +'&page='+ new_page +'&id='+ tinder_id;
            $('#con_load_box').load(base_url +'users/GetConnections', data, function() {

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