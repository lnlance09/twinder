<?php
	$base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/users/';

    $count = $hot['count'];
    $per_page = 10;
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
	<div class="hidden" id="search_results_num"><?php echo $count; ?></div>

	<ul class="list-group">
<?php
		for($i=$start;$i<$end;$i++) {
            $id = $hot['users'][$i]['tinder_id'];
			$name = $hot['users'][$i]['name'];
			$age = $hot['users'][$i]['age'];
            $username = $hot['users'][$i]['username'];
			$img = $id.'/'.$hot['users'][$i]['pic'];
            $likes = $hot['users'][$i]['like_count'];

            // Determine which link to display
            if($username != '') {
                $link = $base_url.'users/'.$username;
            } else {
                $link = $base_url.'users/'.$id;
            }

            // Find out the ranks
            if($likes == $hot['users'][$i+1]['like_count']) {
                if(!isset($point)) {
                    $point = $i;
                    $rank = $i;
                } else {
                    $rank = $point;
                }
            } else {
                $rank = $i;
                unset($point);
            }

            $rank = $rank+1;
?>
		<li class="list-group-item" onclick="location.href='<?php echo $link; ?>'">
			<img src="http://images.gotinder.com/<?php echo $img; ?>" width="84" height="84" class="img-circle" alt="<?php echo $name; ?>" />

			<div> 
				<span>
					<a href="<?php echo $link; ?>" title="<?php echo $name; ?>"><?php echo trim($name); ?></a>, <?php echo $age; ?>
				</span> <br>

                <span>
                    <?php echo $rank; ?>
                </span>
			</div>
		</li>
<?php
		}
?>
	</ul>

    <div class="text-center">
        <ul class="pagination">
<?php
        Pagination($page, $pages);
?>
        </ul>
        <br><br>
    </div>
<?php
	} else {
?>
	<div class="main_none">
		There are no results
	</div>
<?php
	}
?>

	<script>
        var base_url = '<?php echo $base_url; ?>';

		$('.pagination li a').click(function(e) {
            e.preventDefault();
			var page = $(this).text().trim();
    		
    		//$('#hot_load').append('<div class="ajax-loader"><i class="fa fa-refresh fa-2x fa-spin"></i></div>');
    		
            if(page == 'Prev') {
                var new_page = parseInt(<?php echo $page; ?>)-parseInt(1);
            } else if(page == 'Next') {
                var new_page = parseInt(<?php echo $page; ?>)+parseInt(1);
            } else {
                var new_page = parseInt(page-1);
            }

            var data = 'page='+ new_page;

    		$('#hot_load').load(base_url +'hot/GetHottest', data, function() {
    			$('#search_load .ajax-loader').fadeOut();
    		});
    	});
	</script>
