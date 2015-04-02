<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';

    // Adjust the plurality
    $grammar = ($views != 1 ? 'views' : 'view');
    $other = ($user_one['id'] == $my_tinder_id ? $user_two : $user_one);
?>
	<div id="header-section">
        <div id="signin">
            <div id="match_box">
                <h1 class="page-header">
                    <?php echo $user_one['name'].' and '.$user_two['name']; ?>
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
                        <textarea class="form-control" placeholder="Send a <?php echo $other['name']; ?> message" name="msg"></textarea>
                        <button class="btn btn-primary" type="submit" value="submit" name="submit">Send</button>
                        <div class="clearfix"></div>
                    </div>
                </form>
<?php
    }
?>

                <div class="panel panel-default" id="comment_box">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-comments fa-lg"></i> Comments

                            <span class="pull-right"><?php echo $views.' '.$grammar; ?></span>

                            <a class="twitter-share-button pull-right" 
                                href="<?php echo $base_url.'matches/'.$match_id; ?>"
                                data-related="twitterdev"
                                data-size="medium"
                                data-count="horizontal">
                                Tweet
                            </a>

                            <span class="clearfix"></span>
                        </h3>
                    </div>

                    <div class="panel-body">
                        <div id="disqus_thread"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Write all of the variables for the JS to work -->
    <div class="hidden" id="match_id"><?php echo $match_id; ?></div>
    <div class="hidden" id="match_type"><?php echo $type; ?></div>