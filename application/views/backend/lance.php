<?php
    // Define the base URL
	$base_url = $this->config->base_url();
?>
    <div id="media_container">
<?php
    // Loop thru each user
    for($i=0;$i<count($users);$i++) {
        $_id = $users[$i]['tinder_id'];
		$name = $users[$i]['name'];
		$age = $users[$i]['age'];
        $bio = $users[$i]['bio'];
        $img = $users[$i]['profile_pic'];
        $distance = $users[$i]['distance'];
        $link = $base_url.'users/'.$_id;
?>
        <div class="media">
            <div class="media-left media-top">
                <a href="<?php echo $link; ?>">
                    <img src="<?php echo $img; ?>" width="100" height="100" class="media-object img-circle" alt="<?php echo $name; ?>">
                </a>
            </div>
            
            <div class="media-body text-left">
                <h4 class="media-heading">
                    <a href="<?php echo $link; ?>" title="<?php echo $name; ?>" target="_blank"><?php echo $name; ?></a>, <?php echo $age; ?>
                
                </h4>

                <p>
                    <?php echo $bio; ?>
                </p>

                <p>
                    <?php echo $distance; ?> miles away
                </p>
            </div>
        </div>
<?php
	}
?>
    </div>