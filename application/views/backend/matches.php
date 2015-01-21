<?php
	$base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/users/';

	$count = $matches['count'];
    $per_page = 20;
    $pages = ceil($count/$per_page);

    if($pages > 0) {
    	$start = $page*$per_page;

        if($page == ($pages-1)) {
            $mod = $count%$per_page;
            $end = $start+$mod;
        } else {
            $end = $start+$per_page;
        }
?>
	<div class="hidden" id="matches_results_num"><?php echo $count; ?></div>

	<ul class="list-group">
<?php
		for($i=0;$i<$end;$i++) {
			$user = $matches['likes'][$i]['user_info'];

			if(is_array($user)) {
				$name = $user['first_name'];
				$tinder_id = $user['tinder_id'];
				$age = $user['age'];
				$pic = $user['profile_pic'];
			} else {
				$name = '';
			}

			$message = $matches['likes'][$i]['msg'];

			//echo '<pre>';
			//print_r($message);
			//echo '</pre>';

			if(count($message['msg']) > 0) {
				$msg = $message['message']['msg'];
			} else {
				$msg = '';
			}
?>
			<li class="list-group-item" onclick="location.href='<?php echo $base_url.'matches/'.$matches['likes'][$i]['match_id']; ?>'">
				<img src="http://images.gotinder.com/<?php echo $tinder_id.'/84x84_'.$pic; ?>" width="84" height="84" class="img-circle" alt="<?php echo $name; ?>" />

				<div> 
    				<span>
    					<a href="<?php echo $base_url.'matches/'.$matches['likes'][$i]['match_id']; ?>" title=""><?php echo $name; ?></a>, <?php echo $age; ?>
    				</span> <br>
    					
    				<?php echo $msg; ?>
				</div>
			</li>
<?php
		}
?>
	</ul>
<?php
		if($page < ($pages-1)) {
?>
	<button type="button" class="btn btn-default" id="matches_see_more">see more</button>	
<?php
		}
	} else {
?>
	<div class="main_none">
		You don't have any matches yet
	</div>
<?php
	}
?>

	<script>
		$('#matches_load button').click(function() {
			var page = '<?php echo $page; ?>';
    		var new_page = parseInt(page) + parseInt(1);
    		var data = 'page='+ new_page;

    		$('#matches_load').append('<div class="ajax-loader"><i class="icon-spinner icon-spin icon-3x"></i></div>');
    		
    		$('#matches_load').load('matches/MatchesBackend', data, function() {
    			$('.ajax-loader').fadeOut();
    		});
    	});
	</script>
	