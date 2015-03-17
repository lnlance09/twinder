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
    <link rel="stylesheet" href="<?php echo $public_url; ?>/css/font-awesome/font-awesome.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $css_url; ?>custom.css">

    <title>Admin Login - Twinder</title>
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
        </div>
    </div>

    <div id="header-section">
        <div id="signin" class="admin">
            <h1 class="page-header">
                Admin Login
            </h1>

            <form method="POST" action="<?php echo $base_url; ?>admin/login" id="signin_form">
                <input type="text" class="form-control" placeholder="Username" name="username"><br>
                <input type="password" class="form-control" placeholder="Password" name="password"><br>

                <button class="btn btn-primary pull-right" type="submit" name="submit" value="submit">Sign in</button>

                <div class="clearfix"></div>
            </form>
        </div>
    </div>

    <script src="<?php echo $js_url; ?>jquery/jquery.js"></script>
    <script src="<?php echo $js_url; ?>ui/jquery-ui.min.js"></script>
    <script src="<?php echo $js_url; ?>bootstrap/bootstrap.min.js"></script>
    <script src="<?php echo $js_url; ?>admin.js"></script>
    <script>
        // SVG script
        jQuery('img.svg').each(function() {
            var $img = jQuery(this);
            var imgID = $img.attr('id');
            var imgClass = $img.attr('class');
            var imgURL = $img.attr('src');

            jQuery.get(imgURL, function(data) {
                // Get the SVG tag, ignore the rest
                var $svg = jQuery(data).find('svg');

                // Add replaced image's ID to the new SVG
                if(typeof imgID !== 'undefined') {
                    $svg = $svg.attr('id', imgID);
                }
                // Add replaced image's classes to the new SVG
                if(typeof imgClass !== 'undefined') {
                    $svg = $svg.attr('class', imgClass +' replaced-svg');
                }

                // Remove any invalid XML tags as per http://validator.w3.org
                $svg = $svg.removeAttr('xmlns:a');

                // Replace image with new SVG
                $img.replaceWith($svg);
            }, 'xml');
        });
    </script>
</body>
</html>
    