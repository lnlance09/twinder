<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
        <div class="hidden" id="distance"><?php echo $distance; ?></div>
        <div class="hidden" id="meters"><?php echo $meters; ?></div>
        <div class="hidden" id="drag_lon"></div>
        <div class="hidden" id="drag_lat"></div>
        <div class="hidden" id="page"><?php echo $page; ?></div>

		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>

                <div class="slide_wrapper">
                    <form method="GET" action="<?php echo $base_url; ?>matches" id="search_hot">
                        <div class="col-lg-12 settings_box" id="maps_box">
                            <div id="google_maps">

                            </div>
                        </div>

                        <div class="col-lg-12 text-center settings_box" id="distance_bar">
                            <h2>
                                <span class="example-val" id="distance-value"></span> miles
                            </h2>

                            <div id="distance_slider" class="slider"></div><br>

                            <span id="address_components"></span>
                        </div>

                        <div class="col-lg-12 text-center settings_box" id="age_box">
                            <h2>age</h2>

                            <div id="age_slider" class="slider"></div>

                            <h2 id="age_settings">
                                <span class="example-val" id="lower-value">18</span> - <span class="example-val" id="upper-value">50+</span>
                            </h2>
                        </div>

                        <div class="col-lg-12" id="sort_by">
                            <div class="dropdown pull-right">
                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">Show <span class="caret"></span></button>

                                <ul class="dropdown-menu" id="sex_select">
                                    <li><a tabindex="-1" href="#" title="0">Men</a></li>
                                    <li><a tabindex="-1" href="#" title="1">Women</a></li>
                                    <li><a tabindex="-1" href="#" title="-1">Both</a></li>
                                </ul>
                            </div>

                            <div class="clearfix"></div>
                        </div>

                        <div class="clearfix"></div>
                    </form>
                </div>

                <div id="hot_load">
                    <div class="ajax-loader">
                        <i class="fa fa-refresh fa-2x fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>