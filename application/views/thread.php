<?php
    $base_url = $this->config->base_url();
?>
	<div id="header-section">
        <div id="signin">
            <h1 class="page-header text-center">
                <?php echo $header; ?>
            </h1>

            <!-- Where all of the previous messages will be loaded -->
            <div id="thread_load">
                <div class="ajax-loader">
                    <i class="fa fa-cog fa-2x fa-spin"></i>
                </div>
            </div>

            <!-- The form to send a message -->
            <div class="col-lg-12" id="send_msg_box">
                <form method="POST" action="<?php echo $base_url; ?>matches/SendMessage">
                    <div class="form-group">
                        <label for="comment"></label>
                        <textarea class="form-control" rows="5" id="msg_to_match" name="msg" placeholder="Send <?php echo $name; ?> a message..."></textarea>
                    </div>

                    <button class="btn btn-success pull-right" type="submit" name="submit">Send</button>

                    <div class="clearfix"></div>
                </form>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>

    <!-- Write all of the variables for the JS to work -->
    <div class="hidden" id="match_id"><?php echo $match_id; ?></div>
    <div class="hidden" id="match_type"><?php echo $type; ?></div>