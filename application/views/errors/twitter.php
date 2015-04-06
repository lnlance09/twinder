<?php
    $base_url = $this->config->base_url();
    $img_url = $base_url.'public/img/';
?>
	<div class="container">
		<img class="svg" id="twitter_svg" src="<?php echo $img_url; ?>svg/twitter.svg" alt="twitter">
<?php
	if($can_link) {
?>
		<br><br>
		<button class="btn btn-default" type="button" id="twitter_sign_in" onclick="location.href='<?php echo $base_url; ?>home/TwitterRedirect'"><i class="fa fa-twitter fa-lg"></i> Sign in with Twitter</button><br>
<?php
	}
?>	
		<p id="twit_error">
<?php
	if($can_link) {
		echo 'Hi, '.$name.'. <br><br>';
?>
			It looks like you haven't signed up for Twinder on Twitter. 
			To sync your Twitter account, just sign in. <br><br>

			Cheers,<br><br>

			The Twinder Team
<?php
	} else {
		echo $name." hasn't linked ".$gender." Twitter yet...";
	}
?>
		</p>
	</div>

	<script>
        jQuery('img.svg').each(function() {
            var $img = jQuery(this);
            var imgID = $img.attr('id');
            var imgClass = $img.attr('class');
            var imgURL = $img.attr('src');

            jQuery.get(imgURL, function(data) {
                // Get the SVG tag, ignore the rest
                var $svg = jQuery(data).find('svg');

                // Add replaced image's ID to the new SVG
                if(typeof imgID !== 'undefined') {
                    $svg = $svg.attr('id', imgID);
                }
                // Add replaced image's classes to the new SVG
                if(typeof imgClass !== 'undefined') {
                    $svg = $svg.attr('class', imgClass +' replaced-svg');
                }

                // Remove any invalid XML tags as per http://validator.w3.org
                $svg = $svg.removeAttr('xmlns:a');

                // Replace image with new SVG
                $img.replaceWith($svg);
            }, 'xml');
        });
	</script>