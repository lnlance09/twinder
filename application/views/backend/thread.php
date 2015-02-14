<?php
	$base_url = $this->config->base_url();

    FormatArray($messages);
?>
	<div class="messages-wrapper">
<?php
    $count = count($messages);

    if($count > 0) {
    	for($i=0;$i<$count;$i++) {
    		if($i == 0) {
?>
		<div class="message to">
			
		</div>
<?php
			} else {
?>
		<div class="message from">
			
		</div>
<?php
			}
		}
	}
?>
	</div>