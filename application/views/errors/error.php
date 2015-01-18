<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $css_url = $public_url.'css/';
    $img_url = $public_url.'img/';

    // Get the controller name
    $controller = $this->router->fetch_class();

    // Get the method
    $method = $this->router->fetch_method();
    
    $slider_pages = array('users', 'settings', 'search', 'hot');
    $meta_pages = array('about', 'contact', 'faq', 'hot', 'search', 'signin', 'terms', 'users');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo $img_url; ?>favicon.ico">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>open_sans.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>bootstrap.min.css">
<?php
    if($controller == 'users'
    && $method == 'index') {
?>
    <link rel="stylesheet" href="//frontend.reklamor.com/fancybox/jquery.fancybox.css" media="screen">
<?php
    }
?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $public_url; ?>font-awesome/css/font-awesome.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>custom.css">

<?php
    if(in_array($controller, $slider_pages)) {
?>
    <!-- CSS for sliders --> 
    <link rel="stylesheet" href="<?php echo $css_url; ?>nouislider.min.css">
    <link rel="stylesheet" href="<?php echo $css_url; ?>nouislider.pips.min.css">
<?php
    }

    if(in_array($controller, $meta_pages)) {
?>
    <!-- Meta Tags -->
    <meta name="description" content="<?php echo $meta['description']; ?>">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo $title; ?>"/>
    <meta property="og:type" content="article"/>
    <meta property="og:image" content="<?php echo $meta['img']; ?>"/>
    <meta property="og:url" content="<?php echo $meta['url']; ?>"/>
    <meta property="og:description" content="<?php echo $meta['description']; ?>"/>

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="<?php echo $title; ?>">
    <meta name="twitter:url" content="<?php echo $meta['url']; ?>">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $meta['description']; ?>">
    <meta name="twitter:image" content="<?php echo $meta['img']; ?>">
<?php
    }
?>
    <title><?php echo $title; ?> - WeTinder</title>
</head>
    
<body>   
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<?php
    if($session === FALSE) {
?>
                    <span class="icon-bar">Sign In</span>
                    <span class="icon-bar">About</span>
                    <span class="icon-bar">Terms</span>
                    <span class="icon-bar">FAQ</span>
<?php
    } else {
?>
                    <span class="icon-bar">Play</span>
                    <span class="icon-bar">Profile</span>
<?php
    }
?>
                </button>

                <a class="navbar-brand" href="<?php echo $base_url; ?>">
                    <img class="svg" src="<?php echo $img_url; ?>svg/match.svg" width="50" height="50" alt="logo"/>
                    <span id="we">WeTinder</span>
                </a>

                <div class="clearfix"></div>
            </div>

            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="<?php echo $base_url; ?>hot">so hot</a>
                    </li>
<?php
    if($session === FALSE) {
?>
                    <li class="active">
                        <a href="<?php echo $base_url; ?>signin">sign in</a>
                    </li>
<?php
    } else {
?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <?php echo $first_name; ?>

                            <span id="like_count">
                                <!-- The match count -->
                                <i class="fa fa-heart" id="heart_icon"></i> 
                                <span id="match_count_num"><?php echo $match_count; ?></span>

                                <!-- The like count -->
                                <i class="fa fa-thumbs-up" id="match_icon"></i> 
                                <span id="like_count_num"><?php echo $like_count; ?></span>
                            </span>

                            <span class="caret"></span>
                        </a>
                     
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo $base_url; ?>users/Discover">Play</a></li>
                            <li><a href="<?php echo $base_url.$profile_link; ?>">Profile</a></li>

                            <li class="divider"></li>

                            <li><a href="<?php echo $base_url; ?>settings">Settings <i class="fa fa-cog"></i></a></li>
                            <li><a href="<?php echo $base_url; ?>users/Logout">Logout <i class="fa fa-sign-out"></i></a></li>
                        </ul>
                    </li>
<?php
    }
?>
                </ul>
            </div>
        </div>
    </div>

    <div id="base_url" class="hidden"><?php echo $base_url; ?></div>
    