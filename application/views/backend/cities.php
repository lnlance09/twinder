<?php
    if($count > 0) {
?>
	<ul class="list-group">
<?php
		for($i=0;$i<$count;$i++) {
?>
		<li class="list-group-item">
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