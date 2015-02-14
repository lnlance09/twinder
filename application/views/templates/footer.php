<?php
    $base_url = $this->config->base_url();
    $js_url = $base_url.'public/js/';

    // Get the controller name
    $controller = $this->router->fetch_class();

    // Get the method
    $method = $this->router->fetch_method();

    $maps_pages = array('users', 'settings', 'search', 'hot');
    $slider_pages = array('users', 'settings', 'search', 'hot');
?>
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
                <ul>
                    <!-- Twitter Button -->
                    <li>Follow Us</li>

                    <li>
                        <a href="https://twitter.com/WeTinder" class="twitter-follow-button" data-show-count="true" data-size="medium">Follow @WeTinder</a>

                        <script>
                            !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
                        </script>
                    </li>

                    <!-- Facebook Button -->
                    <li id="fb_like_button">
                        <div id="fb-root"></div>

                        <script>
                            (function(d, s, id) {
                                var js, fjs = d.getElementsByTagName(s)[0];
                                if (d.getElementById(id)) return;
                                js = d.createElement(s); js.id = id;
                                js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=1430551347233092&version=v2.0";
                                fjs.parentNode.insertBefore(js, fjs);
                            } (document, 'script', 'facebook-jssdk'));
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

    <!-- jQuery and Bootstrap JS files -->
    <script src="<?php echo $js_url; ?>jquery/jquery.js"></script>
    <script src="<?php echo $js_url; ?>ui/jquery-ui.min.js"></script>
    <script src="<?php echo $js_url; ?>bootstrap/bootstrap.min.js"></script>
    <script src="<?php echo $js_url; ?>main.js"></script>
    
    <!-- Google Maps JS -->
<?php
    if(in_array($controller, $maps_pages)) {
?>
    <script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/src/infobox.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.3/d3.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/topojson/1.6.9/topojson.min.js"></script>
    <script src="<?php echo $js_url; ?>datamaps/datamaps.world.min.js"></script>
<?php
    }

    if(in_array($controller, $slider_pages)) {
?>
    <!-- Slider JS -->
    <script src="<?php echo $js_url; ?>slider/nouislider.all.min.js"></script>
    <script src="<?php echo $js_url; ?>slider/nouislider.min.js"></script>
<?php
    }
?>
    <!-- The JavaScript for each page -->
<?php 
    if($controller == 'users' && strtolower($method) == 'discover') {
?>
    <script src="<?php echo $js_url; ?>discover.js"></script>
<?php
    } else {
?>
    <script src="<?php echo $js_url.$controller; ?>.js"></script>
<?php
    }
?>

    <!-- StatCounter -->
    <!--
    <script type="text/javascript">
        var sc_project = 10187180; 
        var sc_invisible = 1; 
        var sc_security = "f1a35707"; 
        var scJsHost = (("https:" == document.location.protocol) ? "https://secure." : "http://www.");
        document.write("<sc"+"ript type='text/javascript' src='" + scJsHost + "statcounter.com/counter/counter.js'></"+"script>");
    </script>
    -->
</body>
</html>