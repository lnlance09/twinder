<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';

    if($gender == 0) {
        $gender = 'Male';
    } else {
        $gender = 'Female';
    }

    $img_path = 'http://images.gotinder.com/'.$tinder_id.'/'; 
    //$fb_pic_id = https://www.facebook.com/photo.php?fbid='.$fb_pid_id;
?>
            <div class="col-lg-12" id="focus_box">
                <!-- Ping the user's last location -->
                <div id="ping_wrapper">
                    <div id="ping_map"></div>
                </div>

                <div id="profile_page_info">
                    <div class="col-lg-4">
                        <div id="polaroid_wrap">
                            <img src="<?php echo $img_path.$pics[0]['big']; ?>" width="200" height="200" alt="<?php echo $first_name.' '.$last_name; ?>" id="main_img" />
                        </div>
                    </div>

                    <form method="POST" action="<?php echo $base_url; ?>users/EditProfile" id="edit_profile">
                        <div class="col-lg-8" id="profile_info">
                            <h1 class="static">
<?php 
    echo $first_name.', '.$age;

    if($edit == 'true') {
?>
                                <button class="btn btn-default pull-right" type="button" id="click_to_edit">Edit</button>
                                <span class="clearfix"></span>
<?php
    }
?>
                            </h1>
                    
                            <div id="about_quote">
                                <span>
                                    <?php echo $bio_links; ?>
                                </span>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                    </form>
                </div>

                <ul id="sub_pics" class="polaroid-images">
<?php
    // Loop thru the pics
    $pic_count = count($pics);

    if($pic_count >= 5) {
        $end = 5;
    } else {
        $end = $pic_count;
    }

    for($i=0;$i<$end;$i++) {
?>
                    <li name="<?php echo $img_path.$pics[$i]['big']; ?>">
                        <a href="#">
                            <img src="<?php echo $img_path.$pics[$i]['large']; ?>" width="100" height="100" class="" alt="<?php echo $first_name.' '.$last_name; ?>" />
                        </a>
                    </li>
<?php
    }
?>
                </ul>
            </div>

            <div class="hidden" id="pic_path"><?php echo $img_url.'users/'.$tinder_id.'/'; ?></div>

            <div class="col-lg-12 text-center" id="below_box">
                <div class="col-lg-4">
                    <div class="timer_box" name="likes">
                        <h3><?php echo $like_count; ?></h3>
                        <p><i class="fa fa-thumbs-up fa-2x" id="like"></i></p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="timer_box" name="matches" id="connection_active">
                        <h3><?php echo $match_count; ?></h3>
                        <p><i class="fa fa-heart fa-2x" id="matches"></i></p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="timer_box" name="passes">
                        <h3><?php echo $pass_count; ?></h3>
                        <p><i class="fa fa-thumbs-down fa-2x" id="pass"></i></p>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="input-group" id="search_wrapper">
                    <div class="input-group-addon"><i class="fa fa-heart fa-lg" id="fa_type"></i></div>
                    <input type="text" class="form-control" placeholder="Search matches" id="search_connections" autocomplete="off">
                    <div class="input-group-addon"><i class="fa fa-share fa-lg" id="inverse_trigger"></i></div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div id="connections_box">
                <div id="con_load_box">
                    <div class="ajax-loader">
                        <i class="fa fa-refresh fa-2x fa-spin"></i>
                    </div>
                </div>

                <div class="hidden" id="type_name">matches</div>
            </div>

            <!-- The modal for the profile pic -->
            <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <img src="//placehold.it/1000x600" class="img-responsive">
                        </div>
                    </div>
                </div>
            </div>
