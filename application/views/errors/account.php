<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
    <div id="header-section">
        <div id="signin">
            <h1 class="page-header">
                <img src="<?php echo $user['pic']; ?>" class="img-circle" id="error_pic" alt="<?php echo $user['name']; ?>" />
                <?php echo $user['name']; ?> has deleted his Tinder
            </h1>

            <div class="ajax-loader">
                <img class="not_found" src="<?php echo $img_url; ?>svg/snowden.svg" width="200" height="200" alt="Page not found"/>
            </div>
        </div>
    </div>