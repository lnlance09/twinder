<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
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

                <div class="hidden" id="match_type"><?php echo $type; ?></div>
            </div>
        </div>