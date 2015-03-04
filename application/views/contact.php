<?php
    $base_url = $this->config->base_url();
?>
		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>

                <form method="POST" id="contact_form" action="<?php echo $base_url; ?>contact/send">
                    <textarea class="form-control" rows="5" id="contact_us" name="contact_us" placeholder="Tell us what you think" autocomplete="off"></textarea><br>
                    <button class="btn btn-success pull-right" type="submit" name="submit" value="submit">Send</button>
                    <div class="clearfix"></div>
                </form>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="contact_modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                            <h3 class="modal-title">
                                <i class="fa fa-check fa-lg"></i> Your message has been sent
                            </h3>
                        </div>

                        <div class="modal-body">
                            <p>
                                Your message has been sent. You will get a response back from WeTinder within the next 48 hours.
                            </p>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-primary" type="button" data-dismiss="modal" id="wipe_text">Got it</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>