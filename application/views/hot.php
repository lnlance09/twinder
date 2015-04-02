<?php
    $base_url = $this->config->base_url();
?>
	<div id="header-section">
        <div id="signin">
            <h1 class="page-header">
                <?php echo $header; ?>

                <!-- Print out the number of users -->
                <span id="hot_result_num" class="pull-right"></span>

                <span class="clearfix"></span>
            </h1>

            <div class="slide_wrapper">
                <form method="GET" action="<?php echo $base_url; ?>" id="search_hot">
                    <div id="filter_box">
                        <div class="col-lg-12 settings_box" id="location_select_box">
                            <!-- State Autocomplete -->
                            <div class="col-lg-6 pull-left">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><span id="top_stateface" class="stateface stateface-<?php echo strtolower($abbrev); ?>"></span></div>
                                        <input type="text" class="form-control" id="state" placeholder="State" value="<?php echo $state; ?>" />
                                    </div>
                                </div>

                                <div id="state_autocomplete"></div>
                            </div>

                            <!-- City Autocomplete -->
                            <div class="col-lg-6 pull-left">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon" id="city_addon"><i class="fa fa-building-o"></i></div>
                                        <input type="text" class="form-control" id="city" placeholder="City" value="<?php echo $city; ?>" />
                                    </div>
                                </div>

                                <div id="city_autocomplete"></div>
                            </div>

                            <div class="clearfix"></div>
                        </div>

                        <!-- Google Maps -->
                        <div class="col-lg-12 settings_box" id="maps_box">
                            <div id="google_maps">
                                <div class="ajax-loader">
                                    <i class="fa fa-cog fa-4x fa-spin"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Distance Slider -->
                        <div class="col-lg-6 text-center settings_box">
                            <h2>
                                <span class="example-val" id="distance-value"><?php echo $distance; ?></span> miles
                            </h2>

                            <div id="distance_slider" class="slider"></div><br>
                        </div>

                        <!-- Age Slider -->
                        <div class="col-lg-6 text-center settings_box" id="age_box">
                            <h2>
                                <span class="example-val" id="lower-value"><?php echo $min; ?></span> - <span class="example-val" id="upper-value"><?php echo $max; ?></span> years
                            </h2>

                            <div id="age_slider" class="slider"></div><br>
                        </div>

                        <div class="clearfix"></div>
                    </div>

                    <!-- Gender Button -->
<?php
    for($i=0;$i<count($genders);$i++) {
        if($gender == $genders[$i]['name']) {
            $class = ' active';
            $name = 'gender';
        } else {
            $class = '';
            $name = '';
        }
?>
                    <div class="col-lg-4 text-center gender_filter<?php echo $class; ?>" title="<?php echo $genders[$i]['num']; ?>" name="<?php echo $name; ?>">
                        <?php echo ucwords($genders[$i]['name']); ?>
                    </div>
<?php
    }
?>
                    <div class="clearfix"></div>
                </form>
            </div>

            <!-- Where all of the users will be loaded -->
            <div id="hot_load">
                <div class="ajax-loader">
                    <i class="fa fa-circle-o-notch fa-4x fa-spin"></i>
                </div>
            </div>

            <!-- Charts -->
            <div id="chart_load">
                <div class="ajax-loader">
                    <i class="fa fa-circle-o-notch fa-4x fa-spin"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- The out-of-bounds modal -->
    <div class="modal fade" id="bounds_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">
                        Twinder only works in 'Murica

                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    </h3>
                </div>

                <div class="modal-body text-center">
                    <img class="svg" id="error" src="<?php echo $base_url; ?>public/img/svg/404.svg" width="200" height="200" alt="error"/>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Write all of the URL parameters for the JS -->
    <div class="hidden" id="drag_lon"><?php echo $lon; ?></div>
    <div class="hidden" id="drag_lat"><?php echo $lat; ?></div>
    <div class="hidden" id="state_ref"><?php echo $state; ?></div>
    <div class="hidden" id="abbrev"><?php echo $abbrev; ?></div>
    <div class="hidden" id="set_location"><?php echo $set; ?></div>
    <div class="hidden" id="page"><?php echo $page; ?></div>