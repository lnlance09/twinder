<?php
    if($count > 0) {
?>
	<ul class="list-group">
<?php
		for($i=0;$i<$count;$i++) {
?>
		<li class="list-group-item" name="<?php echo $states[$i]['abbrev']; ?>">
			<?php echo $states[$i]['name']; ?>
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