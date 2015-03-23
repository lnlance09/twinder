<?php
	$base_url = $this->config->base_url();
?>
	<div class="messages-wrapper">
<?php
    if($count > 0) {
    	for($i=0;$i<$count;$i++) {
    		if($messages[$i]['from'] == $user_one) {
?>
		<div class="message to">
			<?php echo nl2br($messages[$i]['message']); ?>
		</div>
<?php
			} else {
?>
		<div class="message from">
			
		</div>
<?php
			}
		}
	} else {
?>
		<div class="none">
			There are no results
		</div>
<?php
	}
?>
	</div>