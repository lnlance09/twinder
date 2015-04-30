<?php
	$base_url = $this->config->base_url();
?>
	<ol class="discussion">
<?php
    if($count > 0) {
    	for($i=0;$i<$count;$i++) {
    		// if(!empty($messages[$i]['message'])) {
    			if($messages[$i]['from'] == $user_one['id']) {
?>
		<li class="other">
			<div class="avatar" onclick="location.href='<?php echo $base_url.$user_two['link']; ?>'">
        		<img src="<?php echo $user_two['pic']; ?>" alt="<?php echo $user_two['name']; ?>" data-toggle="tooltip" data-original-title="<?php echo $user_two['name'].', '.$user_two['age']; ?>">
      		</div>

			<div class="messages">
        		<p><?php echo $messages[$i]['message']; ?></p>
        		<time datetime="2009-11-13T20:00"><?php echo $user_two['name']; ?> • <?php echo $messages[$i]['datetime']; ?></time>
      		</div>
		</li>
<?php
				} else {
?>
		<li class="self">
			<div class="avatar" onclick="location.href='<?php echo $base_url.$user_one['link']; ?>'" >
        		<img src="<?php echo $user_one['pic']; ?>" alt="<?php echo $user_one['name']; ?>" data-toggle="tooltip" data-original-title="<?php echo $user_one['name'].', '.$user_one['age']; ?>">
      		</div>

			<div class="messages">
        		<p><?php echo $messages[$i]['message']; ?></p>
        		<time datetime="2009-11-13T20:00"><?php echo $user_one['name']; ?> • <?php echo $messages[$i]['datetime']; ?></time>
      		</div>
		</li>
<?php
				}
			}
		// }
	} else {
?>
		<div class="none">
			<p>
				You matched with <a href="<?php echo $his_link; ?>"><?php echo $his_name; ?></a><br> 
				<?php echo $datetime; ?>
			</p>

			<img src="<?php echo $his_img; ?>" width="172" class="img-circle" alt="<?php echo $his_name; ?>"><br><br>

			<?php echo Openers(); ?>
		</div>
<?php
	}
?>
	</ol>