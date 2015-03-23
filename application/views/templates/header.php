<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $css_url = $public_url.'css/';
    $img_url = $public_url.'img/';

    // Get the controller name
    $controller = $this->router->fetch_class();

    // Get the method
    $method = $this->router->fetch_method();
    
    $slider_pages = array('settings', 'hot');
    $meta_pages = array('about', 'contact', 'faq', 'hot', 'signin', 'terms', 'users');

    if($controller == 'hot') {
        $val = $q;
    } else {
        $val = NULL;
    }
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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>font-awesome/css/font-awesome.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>custom.css?now=<?php echo time(); ?>">

    <!-- Stateface Font CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>stateface.css">
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
    <title><?php echo $title; ?> - Twinder</title>
</head>
    
<body>   
    <div class="navbar navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="<?php echo $base_url; ?>">
                    <img class="svg" src="<?php echo $img_url; ?>svg/match.svg" width="50" height="50" alt="logo">
                    <span id="we">Twinder</span>
                </a>

                <div class="clearfix"></div>
            </div>

            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
<?php
    if($session) {
?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <img src="<?php echo $profile_pic; ?>" alt="Me" class="thumbnail">

                            <span id="header_name"><?php echo $name; ?></span>

                            <span id="like_count">
                                <!-- The match count -->
                                <i class="fa fa-heart" id="heart_icon"></i> 
                                <span id="match_count_num"><?php echo $match_count; ?></span>
                            </span> 
                        </a>
                     
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo $base_url; ?>users/discover">Play</a></li>
                            <li><a href="<?php echo $base_url.$profile_link; ?>">Profile</a></li>

                            <li class="divider"></li>

                            <li><a href="<?php echo $base_url; ?>settings">Settings <i class="fa fa-cog"></i></a></li>
                            <li><a href="<?php echo $base_url; ?>users/Logout">Logout <i class="fa fa-sign-out"></i></a></li>
                        </ul>
                    </li>
<?php
    } else {
?>
                    <li class="active">
                        <button class="btn btn-primary" type="button" onclick="location.href='<?php echo $base_url; ?>signin'">Sign In</button>
                    </li>
<?php
    }
?>
                </ul>
            </div>
        </div>
    </div>

    <div id="search_container">
        <form method="GET" action="<?php echo $base_url; ?>hot/gender/both">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search fa-lg"></i></span>

                <input type="text" class="form-control" placeholder="Search" name="q" id="users_autocomplete" value="<?php echo $val; ?>" autocomplete="off">

                <div class="clearfix"></div>
            </div>
        </form>
    </div>

    <!--
    <div id="autocomplete_wrapper">
        <div id="autocomplete"></div>
    </div>
    -->

    <!-- Write all of the hidden values that need to be used by JS files -->
    <div id="base_url" class="hidden"><?php echo $base_url; ?></div>
    <div id="auth" class="hidden"><?php echo $auth; ?></div>
    <div id="my_tinder_id" class="hidden"><?php echo $tinder_id; ?></div>
    <div id="like_users_num" class="hidden">0</div>
    