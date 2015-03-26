<?php
    $base_url = $this->config->base_url();
    // FormatArray($pics);
?>
    <div class="jumbotron">
        <h1>
            <img src="<?php echo ChangePicSize($profile_pic, 172); ?>" alt="<?php echo $name; ?>" class="img-circle">
            <?php echo $name.', '.$age; ?>
        </h1>

        <div id="profile_discover">
            <p>
                <?php echo $bio; ?>
            </p>

            <p>
                <button type="button" class="btn btn-primary" onclick="location.href='<?php echo $base_url; ?>users/<?php echo $tinder_id; ?>'">View profile</button>
                <button type="button" class="btn btn-default"><?php echo number_format($distance); ?> miles away</button>
            </p>
        </div>
    </div>

    <div class="clearfix"></div>

    <!-- Write for JS -->
    <div class="hidden" id="user_tinder_id"><?php echo $tinder_id; ?></div>
