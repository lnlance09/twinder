<?php
	$base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/users/';

    $count = $users['count'];
    $per_page = 20;
    $pages = ceil($count/$per_page);
?>
	<div class="hidden" id="search_results_num"><?php echo $count; ?></div>
<?php
    if($pages > 0) {
    	$start = $page*$per_page;

        if($page == ($pages-1)) {
            $mod = $count%$per_page;
            $end = $start+$mod;
        } else {
            $end = $start+$per_page;
        }
?>
	<ul class="list-group">
<?php
		for($i=0;$i<$end;$i++) {
			$name = $users['users'][$i]['first_name'];
			$id = $users['users'][$i]['tinder_id'];
			$age = $users['users'][$i]['age'];
			$img = $users['users'][$i]['profile_pic'];
			$link = $users['users'][$i]['link'];
?>
		<li class="list-group-item" onclick="location.href='<?php echo $link; ?>'">
			<img src="http://images.gotinder.com/<?php echo $id.'/'.$img; ?>" width="110" height="110" class="img-circle" alt="<?php echo $name; ?>" />

			<div> 
				<span>
					<a href="<?php echo $link; ?>" title="<?php echo $name; ?>"><?php echo trim($name); ?></a>, <?php echo $age; ?>
				</span> 
			</div>
		</li>
<?php
		}
?>
	</ul>
<?php
		if($page < ($pages-1)) {
?>
	<button type="button" class="btn btn-default" id="search_see_more">see more</button>	
<?php
		}
	} else {
?>
	<div class="main_none">
		There are no results
	</div>
<?php
	}
?>

	<script>
		$('#search_load button').click(function() {
			var q = '<?php echo $q; ?>';
			var page = '<?php echo $page; ?>';
    		var new_page = parseInt(page) + parseInt(1);
    		var gender = $('#interested_in').val();
        	var min = $('#lower-value').text();
        	var max = $('#upper-value').text();
    		var data = 'q='+ q +'&page='+ new_page +'&gender='+ gender +'&min='+ min +'&max='+ max;

    		$('#search_load ul').append('<div class="ajax-loader"><i class="fa fa-refresh fa-2x fa-spin"></i></div>');
    		
    		$('#search_load').load('search/Backend', data, function() {
    			$('#search_load .ajax-loader').fadeOut();
    		});
    	});
	</script>