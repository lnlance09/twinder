<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
	<div id="header-section">
        <div id="signin">
            <div id="match_box">
                <h1 class="page-header">
                    <a href="<?php echo $base_url.$user_one['link']; ?>"><?php echo $user_one['name']; ?></a>
                    and
                    <a href="<?php echo $base_url.$user_two['link']; ?>"><?php echo $user_two['name']; ?></a>

                    <button class="btn btn-default pull-right" type="button">422 views</button>
                    
                    <a class="twitter-share-button pull-right" 
                        href="<?php echo $base_url.'matches/'.$match_id; ?>"
                        data-related="twitterdev"
                        data-size="large"
                        data-count="none">
                        Tweet
                    </a>

                    <span class="clearfix"></span>
                </h1>

                <div id="match_load">
                    <div class="ajax-loader">
                        <i class="fa fa-circle-o-notch fa-4x fa-spin"></i>
                    </div>
                </div>
<?php
    // Print out the form to send a message if necessary
    if($can_send) {
?>
                <form method="POST" id="send_msg" action="<?php echo $base_url; ?>users/SendMessage">
                    <div class="send_area">
                        <textarea class="form-control" placeholder="Send a message" name="msg"></textarea>
                        <button class="btn btn-primary" type="submit" value="submit" name="submit">Send</button>

                        <div class="clearfix"></div>
                    </div>
                </form>
<?php
    }
?>
                <div class="fb-comments" data-href="<?php echo $base_url; ?>" data-numposts="10" data-colorscheme="light"></div>
            </div>
        </div>
    </div>

    <!-- Write all of the variables for the JS to work -->
    <div class="hidden" id="match_id"><?php echo $match_id; ?></div>
    <div class="hidden" id="match_type"><?php echo $type; ?></div>
        