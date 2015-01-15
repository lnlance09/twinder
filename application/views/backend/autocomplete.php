<?php
	$base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/users/';

    // Set the number of messages as a variable
    $count = $users['count'];

    if($count > 0) {
    	if($count >= 3) {
    		$end = 3;
    	} else {
    		$end = $count;
    	}
?>
	<ul class="list-group">
<?php
		for($i=0;$i<$end;$i++) {
			$name = $users['users'][$i]['first_name'];
			$id = $users['users'][$i]['tinder_id'];
			$link = $users['users'][$i]['link'];
			$age = $users['users'][$i]['age'];
			$img = $users['users'][$i]['pics'][0]['tiny'];
?>
		<li class="list-group-item" onclick="location.href='<?php echo $base_url.$link; ?>'">
			<img src="<?php echo 'http://images.gotinder.com/'.$id.'/'.$img; ?>" width="55" height="55" class="img-circle" alt="<?php echo $name; ?>" />

			<div> 
				<span>
					<?php echo trim($name).', '.$age; ?>
				</span> 
			</div>
		</li>
<?php
		}
?>
	</ul>

		<button type="submit" class="btn btn-default" id="autocomplete_submit">see <?php echo number_format($count); ?> results for '<?php echo $q; ?>'</button>

	<div class="clearfix"></div>
<?php
	} else {
?>
	<div class="none">
		there are no results
	</div>
<?php
	}
?>