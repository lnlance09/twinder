<?php
    $base_url = $this->config->base_url();
?>
    <div id="header-section">
        <div id="signin">
            <h1 class="page-header">
                Find new users
            </h1>

            <div class="slide_wrapper">
                <div id="filter_box">
                    <div class="col-lg-12 settings_box" id="location_select_box">
                        <!-- State Autocomplete -->
                        <div class="col-lg-6 pull-left">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-building-o"></i></div>
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

                    <div class="clearfix"></div>
                </div>
            </div>

            <!-- For the JS to work -->
            <div class="hidden" id="state_ref"></div>

            <!-- Load the results -->
            <div id="lance_load">
                
            </div>
        </div>
    </div>