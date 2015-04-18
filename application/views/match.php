<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';

    // Adjust the plurality
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
            </div>
        </div>
    </div>

    <!-- Write all of the variables for the JS to work -->
    <div class="hidden" id="match_id"><?php echo $match_id; ?></div>
    <div class="hidden" id="match_type"><?php echo $type; ?></div>