<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
                    <img src="<?php echo $profile_pic; ?>" width="84" height="84" class="img-circle" alt="<?php echo $name; ?>" id="" />

                    <?php echo $header; ?>
                </h1>
                    
                <div class="col-lg-12">
                
                </div>

                <!--
                <form method="GET" action="<?php echo $base_url; ?>matches" id="search_messages">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Filter" name="search_messages">
                    </div>
                </form>
                -->

                <div id="matches_load">
                    <div class="ajax-loader">
                        <i class="icon-spinner icon-spin icon-3x"></i>
                    </div>
                </div>

                <div class="col-lg-12" id="send_msg_box">
                    <form method="POST" action="<?php echo $base_url; ?>matches/SendMessage" id="send_message">
                        <div class="form-group">
                            <label for="comment"></label>
                            <textarea class="form-control" rows="5" id="msg_to_match" name="msg" placeholder="Send <?php echo $name; ?> a message..."></textarea>
                        </div>

                        <button class="btn btn-success pull-right" type="submit" name="submit">Send</button>

                        <div class="hidden" id="match_id"><?php echo $match_id; ?></div>
                        <div class="clearfix"></div>
                    </form>
                </div>

                <div class="hidden" id="match_type"><?php echo $type; ?></div>
                <div class="clearfix"></div>
            </div>
        </div>