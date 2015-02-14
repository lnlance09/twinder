<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $css_url = $public_url.'css/';
    $js_url = $public_url.'js/';
    $img_url = $public_url.'img/';
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
    <link rel="stylesheet" href="<?php echo $public_url; ?>font-awesome/css/font-awesome.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>custom.css">

    <title>Not Found - WeTinder</title>
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
                        <a href="<?php echo $base_url; ?>hot">browse</a>
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
                            <li><a href="<?php echo $base_url; ?>users/discover">Play</a></li>
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

    <div id="search_container">
        <form method="GET" action="<?php echo $base_url; ?>hot/both">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search fa-lg"></i></span>
                <input type="text" class="form-control" placeholder="Search" name="q" id="users_autocomplete" value="" autocomplete="off">
                <span class="input-group-addon"></span>

                <div class="clearfix"></div>
            </div>
        </form>
    </div>

    <div id="header-section">
        <div id="signin">
            <h1 class="page-header">
                It looks like that page doesn't exist.
            </h1>

            <div class="ajax-loader">
                <img class="not_found" src="<?php echo $img_url; ?>svg/404.svg" width="200" height="200" alt="Page not found"/>
            </div>
        </div>
    </div>

    <div class="text-center" id="footer">
        <div id="sub_footer">
            <div class="col-lg-4">
                <ul>
                    <li>About</li>
                    <li><a href="<?php echo $base_url; ?>about">about</a></li>
                    <li><a href="<?php echo $base_url; ?>terms">terms</a></li>
                    <li><a href="<?php echo $base_url; ?>faq">faq</a></li>
                    <li><a href="<?php echo $base_url; ?>contact">contact</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <ul>
                    <li>Trending</li>
                </ul>
            </div>

            <div class="col-lg-4">
                <!-- Twitter Button -->
                <ul>
                    <li>Follow Us</li>

                    <li>
                        <a href="https://twitter.com/WeTinder" class="twitter-follow-button" data-show-count="true" data-size="medium">Follow @WeTinder</a>

                        <script>
                            !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
                        </script>
                    </li>

                    <li id="fb_like_button">
                        <div id="fb-root"></div>

                        <script>
                            (function(d, s, id) {
                                var js, fjs = d.getElementsByTagName(s)[0];
                                if (d.getElementById(id)) return;
                                js = d.createElement(s); js.id = id;
                                js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=1430551347233092&version=v2.0";
                                fjs.parentNode.insertBefore(js, fjs);
                            }(document, 'script', 'facebook-jssdk'));
                        </script>

                        <div class="fb-like-box" data-href="https://www.facebook.com/WeTinder" data-colorscheme="light" data-show-faces="false" data-header="false" data-stream="false" data-show-border="false"></div>
                    </li>
                
                </ul>
            </div>

            <div class="clearfix"></div>
        </div>
            
        <div id="copyright">
            <p>
                WeTinder © 2015 - a Lance Newman production
            </p>
        </div>
    </div>

    <script src="<?php echo $js_url; ?>jquery/jquery.js"></script>
    <script src="<?php echo $js_url; ?>ui/jquery-ui.min.js"></script>
    <script src="<?php echo $js_url; ?>bootstrap/bootstrap.min.js"></script>
    <script src="<?php echo $js_url; ?>main.js"></script>
</body>
</html>
    