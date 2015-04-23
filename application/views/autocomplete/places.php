<?php
    if($count > 0) {
?>
	<ul class="list-group">
<?php
		for($i=0;$i<$count;$i++) {
?>
		<li class="list-group-item" city="<?php echo $places[$i]['city']; ?>" state="<?php echo $places[$i]['state']; ?>">
			<?php echo $places[$i]['city'].', '.$places[$i]['state']; ?>
			<span class="badge badge-danger pull-right"><?php echo number_format($places[$i]['count']); ?></span>
			<span class="clearfix"></span>
		</li>
<?php
		}
?>
	</ul>
<?php
	} else {
?>
	<div class="none">
		no results
	</div>
<?php
	}
?>