<?php
    $base_url = $this->config->base_url();
?>
	<div id="header-section">
        <div class="container" id="signin">
            <h1 class="page-header">
                <?php echo $header; ?>
            </h1>

            <form method="POST" action="" id="settings_form">
                <!-- Interested In -->
                <div class="col-lg-12 settings_box" id="interested_in">
                    <p>
                        Sexuality
                    </p>
<?php
    for($i=0;$i<count($filters);$i++) {
        $class = ($filters[$i]['num'] == $gender_filter ? ' active' : '');
?>
                    <div class="col-lg-4 text-center selector<?php echo $class; ?>" title="<?php echo $filters[$i]['num']; ?>">
                        <?php echo $filters[$i]['name']; ?>
                    </div>
<?php
    }
?>
                </div>

                <!-- Gender -->
                <div class="col-lg-12 settings_box" id="gender">
                    <p>
                        Gender
                    </p>
<?php
    for($i=0;$i<count($genders);$i++) {
        $class = ($genders[$i]['num'] == $gender ? ' active' : '');
?>
                    <div class="col-lg-6 text-center selector<?php echo $class; ?>" title="<?php echo $genders[$i]['num']; ?>">
                        <?php echo $genders[$i]['name']; ?>
                    </div>
<?php
    }
?>
                </div> 

                <!-- Username -->
                <div class="col-lg-12 settings_box">
                    <p>
                        Username
                    </p>

                    <div id="username_div">
                        <div class="input-group">
                            <input type="text" class="form-control" name="username" id="username" placeholder="Username" value="<?php echo $username; ?>" autocomplete="off">
                            <span class="input-group-addon">@</span>
                        </div>
                    </div>                        
                </div>

                <!-- Age -->
                <div class="col-lg-12 settings_box">
                    <p>
                        Age
                    </p>

                    <div class="compartment">
                        <div id="age_slider" class="slider"></div>

                        <h2 id="age_settings">
                            <span class="example-val" id="lower-value"></span> - <span class="example-val" id="upper-value"></span>
                        </h2>
                    </div>
                </div>

                <!-- Distance -->
                <div class="col-lg-12 settings_box" id="distance_bar">
                    <p>
                        Distance
                    </p>

                    <div class="compartment">
                        <div id="distance_slider" class="slider"></div>

                        <h2 id="distance_settings">
                            <span class="example-val" id="distance-value"><?php echo $distance; ?></span> miles of <span id="address_components"><?php echo $city.', '.$state; ?></span>
                        </h2>
                    </div>

                    <div id="google_maps">
                        <div class="ajax-loader">
                            <i class="fa fa-cog fa-4x fa-spin"></i>
                        </div>
                    </div>
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