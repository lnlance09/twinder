<?php
    // Define the base URL
	$base_url = $this->config->base_url();
	
    if($count > 0) {
?>
    <div id="media_container">
<?php
        // Loop thru each user
        for($i=0;$i<$end;$i++) {
            $img = $connections[$i]['pic'];
            $tweet = $connections[$i]['tweet'];	
            $my_username = $connections[$i]['username'];
            $my_name = $connections[$i]['name'];
            $rt = $connections[$i]['retweet'];
            $media = $connections[$i]['media'];
            $rt_count = $connections[$i]['rt_count'];
            $fav_count = $connections[$i]['fav_count'];

            if($rt) {
                $tweet = $rt['tweet'];
                $name = $rt['name'];
                $username = $rt['username'];
            } else {
                $name = $my_name;
                $username = $my_username;
            }

            $link = 'https://twitter.com/'.$username;
?>
        <div class="media tweet">
<?php
            if($rt) {
?>
            <p class="retweet text-left">
                <i class="fa fa-retweet"></i> <span><?php echo $my_name; ?> retweeted</span>
            </p>
<?php
            }
?>
            <div class="media-left media-top">
                <a href="<?php echo $link; ?>">
                    <img src="<?php echo $img; ?>" class="media-object thumbnail" alt="<?php echo $username; ?>">
                </a>
            </div>
            
            <div class="media-body text-left">
                <h4 class="media-heading">
                    <a href="<?php echo $link; ?>" title="<?php echo $username; ?>"><?php echo $username; ?></a>
                    <?php echo $name; ?>
                </h4>

                <p>
                    <?php echo FormattedTweet($tweet); ?> 
                </p>
<?php
            if($media) {
?>
                <p>
                    <img src="<?php echo $media['url']; ?>" class="thumbnail media_pic" alt="twitter pic">
                </p>
<?php
            }
?>
                <p class="tweet_stats">
                    <!-- Favorite count -->
                    <span class="favorite_count"><i class="fa fa-star"></i> <?php echo number_format($fav_count); ?></span>

                    <!-- Retweet count -->
                    <span class="retweet_count"><i class="fa fa-retweet"></i> <?php echo number_format($rt_count); ?></span>
                </p>
            </div>
        </div>
<?php
		}
?>
    </div>
<?php
        if($new_page != $pages) {
?>
    <div class="text-center">
        <button type="button" class="btn btn-primary" id="see_more">See more (<?php echo number_format($left_over); ?>)</button>
    </div>
<?php
        }
?>
    <!-- JS -->
     <script>
        var base_url = '<?php echo $base_url; ?>';
        var type = '<?php echo $type; ?>';
        var tinder_id = '<?php echo $id; ?>';
        var twitter_id = '<?php echo $twitter_id; ?>';

        $('button#see_more').click(function(e) {
            $('#con_load_box .text-center').prepend('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');

            e.preventDefault();
            var new_page = '<?php echo $new_page; ?>';
            var data = 'type='+ type +'&page='+ new_page +'&id='+ tinder_id +'&twitter_id='+ twitter_id;
    
            $('#con_load_box').load(base_url +'users/GetConnections', data, function() {
                $('.ajax-loader').fadeOut();
            });
        });
    </script>
<?php
	} else {
?>
	<div class="main_none">
		There are no results
	</div>
<?php
	}
?>