<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $css_url = $public_url.'css/';
    $img_url = $public_url.'img/';

    // Get the controller name
    $controller = $this->router->fetch_class();

    // Get the method
    $method = $this->router->fetch_method();
    
    // Define the pages that need the slider and the ones that need meta tags
    $slider_pages = array('settings', 'hot');
    $meta_pages = array('about', 'contact', 'faq', 'hot', 'signin', 'terms', 'users');
    $val = ($controller == 'hot' ? $q : NULL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf8mb4">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="Tinder, Twinder, Tinder for Web, Twinder.io, Tinder Online, Tinder Client, Tinder Web Client, Tinder API">

    <!-- Google Plus -->
    <link rel="publisher" href="https://plus.google.com/lnlance09">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo $img_url; ?>favicon.ico">
<?php
    if(in_array($controller, $meta_pages)) {
?>
    <!-- Open Graph Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="Twinder">
    <meta property="og:type" content="<?php echo $meta['type']; ?>">
    <meta property="og:image" content="<?php echo $meta['img']; ?>">
    <meta property="og:url" content="<?php echo $meta['url']; ?>">
    <meta property="og:description" name="description" content="<?php echo $meta['description']; ?>">
<?php
        if($controller == 'users' && $method == 'index') {
?>
    <!-- Profile Meta Tags -->
    <link rel="canonical" href="http://examples.opengraphprotocol.us/profile.html">
    <meta property="profile:first_name" content="<?php echo $profile_name; ?>">
    <meta property="profile:username" content="<?php echo $meta['username']; ?>">
    <meta property="profile:gender" content="<?php echo FormatGender($gender); ?>">
<?php
        }
?>
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@TwinderIO">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $meta['description']; ?>">
    <meta name="twitter:url" content="<?php echo $meta['url']; ?>">
    <meta name="twitter:image" content="<?php echo $meta['img']; ?>">
<?php
    }
?>
    <!-- CSS -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
    <link rel="stylesheet" href="<?php echo $css_url; ?>bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $css_url; ?>font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo $css_url; ?>custom.css">

<?php
    if(in_array($controller, $slider_pages)) {
?>
    <link rel="stylesheet" href="<?php echo $css_url; ?>stateface.css">
    <link rel="stylesheet" href="<?php echo $css_url; ?>nouislider.min.css">
    <link rel="stylesheet" href="<?php echo $css_url; ?>nouislider.pips.min.css">
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

                <button type="button" class="navbar-toggle" id="main_icon">
                    <span class="glyphicon glyphicon-menu-hamburger"></span>
                </button>

                <div class="clearfix"></div>
            </div>

            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right pull-right">
<?php
    if($session) {
?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <img src="<?php echo $pic; ?>" alt="Me" class="thumbnail">
                            <span id="header_name"><?php echo $name; ?></span>
                        </a>
                     
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo $base_url; ?>users/discover">Play <span class="glyphicon glyphicon-play-circle"></span></a></li>
                            <li><a href="<?php echo $base_url.$link; ?>">Profile <span class="glyphicon glyphicon-user"></span></a></li>
                            <li><a href="<?php echo $base_url; ?>settings">Settings <span class="glyphicon glyphicon-cog"></span></a></li>
                            <li><a href="<?php echo $base_url; ?>users/Logout">Logout <span class="glyphicon glyphicon-log-out"></span></a></li>
                        </ul>
                    </li>
<?php
    } else {
?>
                    <li class="active">
                        <button class="btn btn-primary" type="button" onclick="location.href='<?php echo $base_url; ?>signin'"><i class="fa fa-refresh fa-lg"></i> Sync my account</button>
                    </li>
<?php
    }
?>
                </ul>

                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div id="wrapper">
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav" id="sidebar">     
<?php
    if($session) {
?>
                <li><a href="<?php echo $base_url.'users/discover'; ?>"><span class="glyphicon glyphicon-play-circle"></span> Play</a></li>
                <li><a href="<?php echo $base_url.$link; ?>"><span class="glyphicon glyphicon-user"></span> Profile</a></li>
                <li><a href="<?php echo $base_url.'settings'; ?>"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                <li><a href="<?php echo $base_url.'users/Logout'; ?>"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
<?php
    } else {
?>
                <li><a href="<?php echo $base_url.'signin'; ?>"><i class="fa fa-refresh fa-lg"></i> Sync my account</a></li>
<?php
    }
?>
            </ul>
        </div>

        <div id="search_container">
            <form method="GET" id="navbar_form" action="<?php echo $base_url; ?>hot/gender/both">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search" name="q" id="users_autocomplete" value="<?php echo $val; ?>" autocomplete="off">
                    <div class="clearfix"></div>
                </div>
            </form>
        </div>

        <!-- Write all of the hidden values that need to be used by JS files -->
        <div id="base_url" class="hidden"><?php echo $base_url; ?></div>
        <div id="my_tinder_id" class="hidden"><?php echo $tinder_id; ?></div>
        <div id="auth" class="hidden"><?php echo $auth; ?></div>