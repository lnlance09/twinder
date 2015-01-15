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

                <div class="slide_wrapper">
                    <form method="GET" action="<?php echo $base_url; ?>matches" id="search_hot">
                        <div class="col-lg-5 text-center pull-left settings_box">
                            <h2>age</h2>

                            <div id="age_slider" class="slider"></div>

                            <div class="settings_display" id="age_settings">
                                <span class="example-val" id="lower-value">18</span>
                                -
                                <span class="example-val" id="upper-value">50+</span>
                            </div>
                        </div>

                        <div class="col-lg-5 text-center pull-right settings_box" id="interested_in_box">
                            <h2>sex</h2>

                            <select class="multiselect" id="interested_in">
                                <option value="both" selected="selected">Men and Women</option>
                                <option value="0">Men</option>
                                <option value="1">Women</option>
                            </select>
                        </div>

                        <div class="clearfix"></div>
                    </form>
                </div>

                <div id="search_load">
                    <div class="ajax-loader">
                        <i class="fa fa-refresh fa-2x fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>