<?php
	$base_url = $this->config->base_url();
?>
	<ol class="discussion">
<?php
    if($count > 0) {
    	for($i=0;$i<$count;$i++) {
    		if($messages[$i]['from'] == $user_one['id']) {
?>
		<li class="other">
			<div class="avatar">
        		<img src="<?php echo $user_two['pic']; ?>" alt="<?php echo $user_two['name']; ?>" onclick="location.href='<?php echo $base_url.$user_two['link']; ?>'">
      		</div>

			<div class="messages">
        		<p><?php echo nl2br($messages[$i]['message']); ?></p>
        		<time datetime="2009-11-13T20:00"><?php echo $user_two['name']; ?> • <?php echo $messages[$i]['datetime']; ?></time>
      		</div>
		</li>
<?php
			} else {
?>
		<li class="self">
			<div class="avatar">
        		<img src="<?php echo $user_one['pic']; ?>" alt="<?php echo $user_one['name']; ?>" onclick="location.href='<?php echo $base_url.$user_one['link']; ?>'">
      		</div>

			<div class="messages">
        		<p><?php echo nl2br($messages[$i]['message']); ?></p>
        		<time datetime="2009-11-13T20:00"><?php echo $user_one['name']; ?> • <?php echo $messages[$i]['datetime']; ?></time>
      		</div>
		</li>
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
	</ol>