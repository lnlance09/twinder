<?php
    if($count > 0) {
?>
	<ul class="list-group">
<?php
		for($i=0;$i<$count;$i++) {
?>
		<li class="list-group-item" lon="<?php echo $cities[$i]['lon']; ?>" lat="<?php echo $cities[$i]['lat']; ?>">
			<?php echo $cities[$i]['name']; ?>
		</li>
<?php
		}
?>
	</ul>
<?php
	} else {
?>
	<div class="none">
		there are no results
	</div>
<?php
	}
?>