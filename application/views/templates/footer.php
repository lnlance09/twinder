<?php
    $base_url = $this->config->base_url();
    $js_url = $base_url.'public/js/';

    // Get the controller name
    $controller = $this->router->fetch_class();

    // Get the method
    $method = $this->router->fetch_method();

    $maps_pages = array('users', 'settings', 'hot');
    $slider_pages = array('settings', 'hot');
?>
    <div class="text-center" id="footer">
        <div id="list_name">
            <div class="col-lg-3">
                <i class="fa fa-rocket"></i> About
            </div>

            <div class="col-lg-3">
                <i class="fa fa-globe"></i> People
            </div>

            <div class="col-lg-3">
                <i class="fa fa-map-marker"></i> Places
            </div>

            <div class="col-lg-3">
                <i class="fa fa-twitter"></i> Follow Us
            </div>

            <div class="col-lg-12" id="slogan">Tinder meets Twitter</div>

            <div class="clearfix"></div>
        </div>

        <div class="clearfix"></div>

        <div id="sub_footer">
            <div class="col-lg-3">
                <ul>
                    <li><a href="<?php echo $base_url; ?>about">about</a></li>
                    <li><a href="<?php echo $base_url; ?>terms">terms</a></li>
                    <li><a href="<?php echo $base_url; ?>faq">faq</a></li>
                    <li><a href="<?php echo $base_url; ?>contact">contact</a></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <ul>
                    <li id="users_foot">Random Users</li>
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
                <ul>
                    <li id="places_foot">Random Places</li>
<?php
    // Loop thru the random locations
    for($i=0;$i<4;$i++) {
        $url = $base_url.'hot/gender/both/city/'.$locations[$i]['city'].'/state/'.$locations[$i]['state'].'/';
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
                <ul>
                    <!-- Twitter Button -->
                    <li>
                        <a href="https://twitter.com/TwinderIO" class="twitter-follow-button" data-show-count="true" data-size="medium">Follow @TwinderIO</a>

                        <script>
                            !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
                        </script>
                    </li>
                </ul>
            </div>

            <div class="clearfix"></div>
        </div>
            
        <div id="copyright">
            <p>
                Twinder © 2015 - a Lance Newman production
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
    <!-- The JS for each page -->
<?php 
    if($controller == 'users' && strtolower($method) == 'discover') {
?>
    <script src="<?php echo $js_url; ?>discover.js"></script>
<?php
    } else {
        if($controller == 'hot') {
?>
    <!-- Charts JS -->
    <script src="<?php echo $js_url; ?>charts/chart.min.js"></script>
<?php
        }
?>
    <script src="<?php echo $js_url.$controller; ?>.js"></script>
<?php
    } if($controller == 'matches') {
?>
    <script>
        var disqus_shortname = 'twinder';
        
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
<?php
    }

    if($base_url != '/wetinder/') {
?>
    <!-- StatCounter -->
    <script type="text/javascript">
        var sc_project = 10187180; 
        var sc_invisible = 1; 
        var sc_security = "f1a35707"; 
        var scJsHost = (("https:" == document.location.protocol) ? "https://secure." : "http://www.");
        document.write("<sc"+"ript type='text/javascript' src='" + scJsHost + "statcounter.com/counter/counter.js'></"+"script>");
    </script>
<?php
    }
?>
</body>
</html>