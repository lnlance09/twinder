<?php
    $base_url = $this->config->base_url();
?>
		<div id="header-section">
            <div class="container" id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>

                <form method="POST" action="" id="settings_form">
                    <div class="col-lg-12 settings_box">
                        <!-- Interested In -->
                        <span class="settings_field">interested in</span>
    
                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" type="button" id="interested_in_button" data-toggle="dropdown" aria-expanded="true" value="<?php echo $gender_filter; ?>"><?php echo FormatInterestedIn($gender_filter); ?> <span class="caret"></span></button>

                            <ul class="dropdown-menu" id="interested_in">
<?php
    for($i=0;$i<count($filters);$i++) {
?>
                                <li><a href="#" title="<?php echo $filters[$i]['num']; ?>"><?php echo $filters[$i]['name']; ?></a></li>
<?php
    }
?>
                            </ul>
                        </div>

                        <div class="clearfix"></div>
                    </div>

                    <div class="col-lg-12 settings_box">
                        <!-- Gender -->
                        <span class="settings_field">gender</span>

                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" type="button" id="gender_button" data-toggle="dropdown" aria-expanded="true" value="<?php echo $gender; ?>"><?php echo FormatGender($gender); ?> <span class="caret"></span></button>

                            <ul class="dropdown-menu" id="gender">
<?php
    for($i=0;$i<count($genders);$i++) {
?>
                                <li><a href="#" title="<?php echo $genders[$i]['num']; ?>"><?php echo $genders[$i]['name']; ?></a></li>
<?php
    }
?>
                            </ul>
                        </div>

                        <div class="clearfix"></div>
                    </div> 

                    <div class="col-lg-12 settings_box">
                        <!-- Username -->
                        <span class="settings_field">username</span>

                        <div class="pull-right" id="username_div">
                            <div class="input-group pull-right">
                                <input type="text" class="form-control" name="username" id="username" placeholder="Username" value="<?php echo $username; ?>" autocomplete="off">
                                <span class="input-group-addon">@</span>
                            </div>
                        </div>
    
                        <div class="clearfix"></div>
                    </div>

                    <!-- Age -->
                    <div class="col-lg-12 text-center settings_box">
                        <div id="age_slider" class="slider"></div>

                        <h2 id="age_settings">
                            <span class="example-val" id="lower-value"></span> - <span class="example-val" id="upper-value"></span>
                        </h2>
                    </div>

                    <!-- Google Maps -->
                    <div class="col-lg-12" id="maps_bar">
                        <div id="google_maps"></div>
                    </div>

                    <!-- Distance -->
                    <div class="col-lg-12 text-center" id="distance_bar">
                        <span id="address_components"></span>
                        
                        <div id="distance_slider" class="slider"></div>

                        <h2 id="distance_settings">
                            <span class="example-val" id="distance-value"></span> miles
                        </h2>
                    </div>

                    <div class="clearfix"></div>

                    <!-- Submit the form -->
                    <div id="settings_submit">
                        <button class="btn btn-primary pull-right" type="submit" name="submit" value="submit">Done</button>
                        <div class="clearfix"></div>
                    </div>
                </form>
            </div>

            <!-- Write all of the variables for the JS to work -->
            <div class="hidden" id="distance"><?php echo $distance; ?></div>
            <div class="hidden" id="min"><?php echo $min; ?></div>
            <div class="hidden" id="max"><?php echo $max; ?></div>
            <div class="hidden" id="gender_filter"><?php echo $gender_filter; ?></div>
            <div class="hidden" id="lon"><?php echo $lon; ?></div>
            <div class="hidden" id="lat"><?php echo $lat; ?></div>
        </div>