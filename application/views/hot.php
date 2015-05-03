<?php
    $base_url = $this->config->base_url();
?>
	<div id="header-section" itemscope itemtype="http://schema.org/SearchResultsPage">
        <div id="signin">
            <h1 class="page-header">
                <span itemprop="headline"><?php echo $header; ?></span>
                <span class="pull-right" id="hot_count_num"></span>
                <span class="clearfix"></span>
            </h1>

            <div class="slide_wrapper">
                <form method="GET" action="<?php echo $base_url; ?>" id="search_hot">
                    <div id="filter_box">
                        <div class="col-lg-12 settings_box" id="location_select_box">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon" id="city_addon"><i class="fa fa-building-o"></i></div>
                                    <input type="text" class="form-control" id="location" placeholder="Pick a place" value="">
                                </div>
                            </div>

                            <div id="location_autocomplete"></div>
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
        $class = ($gender == $genders[$i]['name'] ? ' active' : '');
        $name = ($gender == $genders[$i]['name'] ? 'gender' : '');
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
        </div>
    </div>

    <!-- Write all of the URL parameters for the JS -->
    <div class="hidden" id="drag_lon"><?php echo $lon; ?></div>
    <div class="hidden" id="drag_lat"><?php echo $lat; ?></div>
    <div class="hidden" id="state_ref"><?php echo $state; ?></div>
    <div class="hidden" id="city"><?php echo $city; ?></div>
    <div class="hidden" id="abbrev"><?php echo $abbrev; ?></div>
    <div class="hidden" id="set_location"><?php echo $set; ?></div>
    <div class="hidden" id="page"><?php echo $page; ?></div>