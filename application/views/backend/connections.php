<?php
	$base_url = $this->config->base_url();

    $per_page = 5;
    $pages = ceil($count/$per_page);

    if($pages > 0) {
?>
	<div class="hidden" id="matches_results_num"><?php echo $count; ?></div>

	<ul class="list-group">
<?php
		for($i=0;$i<count($connections);$i++) {
			$user = $connections[$i]['user_info'];

			if(is_array($user)) {
				$name = $user['first_name'];
				$tinder_id = $user['tinder_id'];
				$age = $user['age'];
				$pic = $user['profile_pic'];
				$link = $user['link'];
			} else {
				$name = '';
			}
?>
		<li class="list-group-item" onclick="location.href='<?php echo $base_url.$link; ?>'">
			<img src="http://images.gotinder.com/<?php echo $tinder_id.'/84x84_'.$pic; ?>" width="50" height="50" class="img-circle" alt="<?php echo $name; ?>" />

			<a href="#" title=""><?php echo $name; ?></a>, <?php echo $age; ?>

			<span class="pull-right">
				<?php echo date('D, M j', strtotime($connections[$i]['datetime'])); ?>
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

		$('.pagination li a').click(function() {
            var this_page = $(this).text().trim();

            if(this_page == 'Prev') {
                var new_page = parseInt(<?php echo $page; ?>)-parseInt(1);
            } else if(this_page == 'Next') {
                var new_page = parseInt(<?php echo $page; ?>)+parseInt(1);
            } else {
                var new_page = parseInt(this_page)-parseInt(1);
            }

            $('#con_load_box').load(base_url +'users/GetConnections', 'type='+ type +'&page='+ new_page +'&id='+ tinder_id, function() {

            });

            event.preventDefault();
        });
	</script>	