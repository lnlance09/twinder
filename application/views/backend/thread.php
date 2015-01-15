<?php
	$base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/users/';

    echo '<pre>';
    print_r($messages);
    echo '</pre>';
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