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

    <title>Login - WeTinder</title>
</head>
    
<body>   
    <form method="post" action="<?php echo $base_url; ?>admin/login" id="signin_form">
        <input type="text" class="form-control" placeholder="Username" name="username"><br>
        <input type="password" class="form-control" placeholder="Password" name="password"><br>

        <button class="btn btn-primary pull-right" type="submit" name="submit" value="submit">Sign in</button>

        <div class="clearfix"></div>
    </form>

    <script src="<?php echo $js_url; ?>jquery/jquery.js"></script>
    <script src="<?php echo $js_url; ?>ui/jquery-ui.min.js"></script>
    <script src="<?php echo $js_url; ?>bootstrap/bootstrap.min.js"></script>
    <script src="<?php echo $js_url; ?>admin.js"></script>
</body>
</html>
    