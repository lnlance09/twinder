<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';

    // Save all of the current Tinder user's info as variables
    $tinder_id = $user['tinder_id'];
    $distance = $user['distance'];
    $name = $user['name'];
    $bio = $user['bio'];
    $bio_links = $user['bio_links'];
    $age = $user['age'];
    $gender = $user['gender'];
    $time_format = $user['time_format'];
    $pics = $user['pics'];

    if($gender == 0) {
        $sex = 'Male'; 
    } else {
        $sex = 'Female';
    }

    $img_path = 'http://images.gotinder.com/'.$tinder_id.'/'; 
?>
            <div class="col-lg-12" id="focus_box">
                <div class="col-lg-4">
                    <div id="polaroid_wrap">
                        <img src="<?php echo $img_path.$pics[0]['big']; ?>" width="250" height="250" alt="<?php echo $name; ?>" id="main_img" />
                    </div>
                </div>

                <div id="profile_discover">
                    <div class="col-lg-8">
                        <h1 class="static">
                            <a href="<?php echo $base_url; ?>users/<?php echo $tinder_id; ?>" target="_blank"><?php echo $name; ?></a>, <?php echo $age; ?> 
                            <span class="pull-right" id="distance"><?php echo $distance; ?> miles away</span>
                            <span class="clearfix"></span>
                        </h1>
                    
                        <div id="about_quote">
                            <span>
                                <?php echo preg_replace('/#(\w+)/', ' <a href="http://twitter.com/hashtag/$1" target="_blank">#$1</a> ', $bio); ?>
                            </span>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                </div>

                <ul class="polaroid-images">
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
                            <img src="<?php echo $img_path.$pics[$i]['medium']; ?>" width="100" height="100" class="" alt="<?php echo $name; ?>" />
                        </a>
                    </li>
<?php
    }
?>
                </ul>
            </div>

            <div class="hidden" id="user_tinder_id"><?php echo $tinder_id; ?></div>

            <div class="clearfix"></div>
