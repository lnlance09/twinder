<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';

    $filters = array(array('num' => 0, 'name' => 'Men'),
                    array('num' => 1, 'name' => 'Women'),
                    array('num' => -1, 'name' => 'Both'));

    $genders = array(array('num' => 0, 'name' => 'Male'),
                    array('num' => 1, 'name' => 'Female'));
?>
		<div id="header-section">
            <div class="container" id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>

                <form method="POST" action="" id="settings_form">
                    <div class="col-lg-12 settings_box">
                        <span>
                            interested in
                        </span>
    
                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" type="button" id="interested_in_button" data-toggle="dropdown" aria-expanded="true" value="<?php echo $gender_filter; ?>"><?php echo FormatInterestedIn($gender_filter); ?> <span class="caret"></span></button>

                            <ul class="dropdown-menu" id="interested_in">
<?php
    for($i=0;$i<count($filters);$i++) {
?>
                                <li><a tabindex="-1" href="#" title="<?php echo $filters[$i]['num']; ?>"><?php echo $filters[$i]['name']; ?></a></li>
<?php
    }
?>
                            </ul>
                        </div>

                        <div class="clearfix"></div>
                    </div>

                    <div class="col-lg-12 settings_box">
                        <span>
                            gender
                        </span>

                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" type="button" id="gender_button" data-toggle="dropdown" aria-expanded="true" value="<?php echo $gender; ?>"><?php echo FormatGender($gender); ?> <span class="caret"></span></button>

                            <ul class="dropdown-menu" id="gender">
<?php
    for($i=0;$i<count($genders);$i++) {
?>
                                <li><a tabindex="-1" href="#" title="<?php echo $genders[$i]['num']; ?>"><?php echo $genders[$i]['name']; ?></a></li>
<?php
    }
?>
                            </ul>
                        </div>

                        <div class="clearfix"></div>
                    </div> 

                    <div class="col-lg-12 settings_box">
                        <span>
                            username
                        </span>

                        <div class="pull-right" id="username_div">
                            <div class="input-group pull-right">
                                <input type="text" class="form-control" name="username" id="username" placeholder="Username" value="<?php echo $username; ?>" autocomplete="off">
                                <span class="input-group-addon">@</span>
                            </div>
                        </div>
    
                        <div class="clearfix"></div>
                    </div>

                    <div class="col-lg-12 text-center settings_box">
                        <h2>age</h2>

                        <div id="age_slider" class="slider"></div>

                        <h2 id="age_settings">
                            <span class="example-val" id="lower-value"></span> - <span class="example-val" id="upper-value"></span>
                        </h2>
                    </div>

                    <div class="col-lg-12" id="maps_bar">
                        <div id="google_maps"></div>
                    </div>

                    <div class="col-lg-12 text-center" id="distance_bar">
                        <h2>distance</h2>

                        <div id="distance_slider" class="slider"></div>

                        <h2 id="distance_settings">
                            <span class="example-val" id="distance-value"></span>
                            miles
                        </h2>
                    </div>

                    <div class="clearfix"></div>

                    <div id="settings_submit">
                        <button class="btn btn-primary pull-right" type="submit" name="submit" value="submit">Done</button>
                        <div class="clearfix"></div>
                    </div>
                </form>
            </div>

            <div class="hidden" id="distance"><?php echo $distance; ?></div>
            <div class="hidden" id="min"><?php echo $min; ?></div>
            <div class="hidden" id="max"><?php echo $max; ?></div>
            <div class="hidden" id="gender_filter"><?php echo $gender_filtr; ?></div>
            <div class="hidden" id="lon"><?php echo $lon; ?></div>
            <div class="hidden" id="lat"><?php echo $lat; ?></div>
        </div>