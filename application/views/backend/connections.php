<?php
	$base_url = $this->config->base_url();

	$count = $connections['count'];
    $per_page = 5;
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
		for($i=$start;$i<$end;$i++) {
			$user = $connections['likes'][$i]['user_info'];

			if(is_array($user)) {
				$name = $user['first_name'];
				$tinder_id = $user['tinder_id'];
				$age = $user['age'];
				$pic = $tinder_id.'/'.$user['pics'][0]['tiny'];
				$link = $user['link'];
			} else {
				$name = '';
			}
?>
		<li class="list-group-item" onclick="location.href='<?php echo $link; ?>'">
			<img src="http://images.gotinder.com/<?php echo $pic; ?>" width="50" height="50" class="img-circle" alt="<?php echo $name; ?>" />

			<a href="<?php echo $base_url.'matches/'; ?>" title=""><?php echo $name; ?></a>, <?php echo $age; ?>

			<span class="pull-right">
				<?php echo date('D, M j', strtotime($connections['likes'][$i]['datetime'])); ?>
			</span>

			<span class="clearfix"></span> 
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
    </div>
<?php
	} else {
?>
	<div class="main_none">
		no <?php echo $type; ?> yet
	</div>
<?php
	}
?>

	<script>
		var base_url = '<?php echo $base_url; ?>';
		var type = '<?php echo $type; ?>';
		var tinder_id = '<?php echo $id; ?>';

		$('.pagination li a').click(function(e) {
            e.preventDefault();
            var this_page = $(this).text().trim();

            if(this_page == 'Prev') {
                var new_page = parseInt(<?php echo $page; ?>)-parseInt(1);
            } else if(this_page == 'Next') {
                var new_page = parseInt(<?php echo $page; ?>)+parseInt(1);
            } else {
                var new_page = parseInt(this_page)-parseInt(1);
            }

            $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type +'&page='+ new_page +'&id='+ tinder_id);
        });
	</script>	