<?php
    $base_url = $this->config->base_url();

    // FormatArray($pics);
?>
            <div class="col-lg-12" id="focus_box">
                <div class="col-lg-4">
                    <div id="polaroid_wrap">
                        <img src="http://images.gotinder.com/<?php echo $tinder_id.'/172x172_'.$profile_pic; ?>" alt="<?php echo $name; ?>" id="main_img" />
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
                                <?php echo $bio; ?>
                            </span>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                </div>

                <div id="pic_bar">
                    <ul class="polaroid-images" id="sub_pics">
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
                        <li name="http://images.gotinder.com/<?php echo $tinder_id.'/172x172_'.$pics[$i]; ?>">
                            <a href="#">
                                <img src="http://images.gotinder.com/<?php echo $tinder_id.'/84x84_'.$pics[$i]; ?>" alt="<?php echo $name; ?>" />
                            </a>
                        </li>
<?php
    }
?>
                    </ul>
                </div>
            </div>

            <div class="hidden" id="user_tinder_id"><?php echo $tinder_id; ?></div>

            <div class="clearfix"></div>
