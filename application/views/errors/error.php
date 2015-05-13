<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $css_url = $public_url.'css/';
    $img_url = $public_url.'img/';
    $js_url = $public_url.'js/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo $img_url; ?>favicon.ico">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>font-awesome/css/font-awesome.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>custom.css">

    <title>Not Found - Twinder</title>
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

                <input type="text" class="form-control" placeholder="Search" name="q" id="users_autocomplete" value="" autocomplete="off">

                <div class="clearfix"></div>
            </div>
        </form>
    </div>

    <!-- Write all of the hidden values that need to be used by JS files -->
    <div id="base_url" class="hidden"><?php echo $base_url; ?></div>
    <div id="auth" class="hidden"><?php echo $auth; ?></div>

    <div id="header-section">
        <div id="signin">
            <h1 class="page-header">
                That page doesn't exist
            </h1>

            <div class="ajax-loader">
                <img class="not_found" src="<?php echo $img_url; ?>svg/404.svg" width="200" height="200" alt="Page not found"/>
            </div>
        </div>
    </div>


    <div class="text-center" id="footer">
        <div id="sub_footer">
            <div class="col-lg-12" id="top_footer">
                <div class="col-lg-3">
                    <div class="m_line">
                        <i class="fa fa-rocket"></i> About
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="m_line">
                        <i class="fa fa-user"></i> People
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="m_line">
                        <i class="fa fa-map-marker"></i> Places
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="m_line">
                        <i class="fa fa-twitter"></i> Follow
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="col-lg-3">
                    <div class="hide_rasp">
                        <i class="fa fa-rocket"></i> About
                    </div>

                    <ul>
                        <li><a href="<?php echo $base_url; ?>about">about</a></li>
                        <li><a href="<?php echo $base_url; ?>terms">terms</a></li>
                        <li><a href="<?php echo $base_url; ?>faq">faq</a></li>
                        <li><a href="<?php echo $base_url; ?>contact">contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <div class="hide_rasp">
                        <i class="fa fa-user"></i> People
                    </div>

                    <ul>
<?php
    // Loop thru the random users
    for($i=0;$i<4;$i++) {
?>
                        <li>
                            <a href="<?php echo $base_url.$users[$i]['link']; ?>">
                                <?php echo $users[$i]['name'].', '.$users[$i]['age']; ?>
                            </a>
                        </li>
<?php
    }
?>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <div class="hide_rasp">
                        <i class="fa fa-map-marker"></i> Places
                    </div>

                    <ul>
<?php
    // Loop thru the random locations
    for($i=0;$i<4;$i++) {
        $url = $base_url.'hot/gender/both/lat/'.$locations[$i]['lat'].'/lon/'.$locations[$i]['lon'].'/';
?>
                        <li>
                            <a href="<?php echo $url; ?>"><?php echo $locations[$i]['city'].', '.$locations[$i]['state']; ?></a>
                        </li>
<?php
    }
?>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <div class="hide_rasp">
                        <i class="fa fa-twitter"></i> Follow
                    </div>

                    <ul>
                        <!-- Twitter Button -->
                        <li>
                            <a href="https://twitter.com/TwinderIO" class="twitter-follow-button" data-show-count="true" data-size="medium">Follow @TwinderIO</a>
                        </li>

                        <li>
                            <a href="http://instagram.com/twinder.io?ref=badge" class="ig-b- ig-b-v-24"><img src="//badges.instagram.com/static/images/ig-badge-view-24.png" alt="Instagram"></a>
                        </li>
                    </ul>
                </div>

                <div class="clearfix"></div>
            </div>

            <div class="clearfix"></div>
        </div>
            
        <div id="copyright">
            <p>
                Twinder © 2015 - a <a href="http://twinder.io/users/lance">Lance Newman</a> production
            </p>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS files -->
    <script src="<?php echo $js_url; ?>jquery/jquery.js"></script>
    <script src="<?php echo $js_url; ?>bootstrap/bootstrap.min.js"></script>
    <script src="<?php echo $js_url; ?>main.js"></script>

    <!-- StatCounter -->
    <script>
        var sc_project = 10187180; 
        var sc_invisible = 1; 
        var sc_security = "f1a35707"; 
        var scJsHost = (("https:" == document.location.protocol) ? "https://secure." : "http://www.");
        document.write("<sc"+"ript type='text/javascript' src='" + scJsHost + "statcounter.com/counter/counter.js'></"+"script>");
    </script>
</body>
</html>
    