<?php
    $base_url = $this->config->base_url();
    $js_url = $base_url.'public/js/';

    // Get the controller name
    $controller = $this->router->fetch_class();

    // Get the method
    $method = $this->router->fetch_method();

    // Define the slider and maps pages
    $maps_pages = array('users', 'settings', 'hot');
    $slider_pages = array('settings', 'hot');
?>
        <div class="text-center" id="footer">
            <div id="sub_footer">
                <div class="col-lg-12" id="top_footer">
                    <div class="col-lg-3">
                        <div class="m_line">
                            <i class="fa fa-rocket fa-lg"></i> About
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="m_line">
                            <i class="fa fa-user fa-lg"></i> Random People
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="m_line">
                            <i class="fa fa-map-marker fa-lg"></i> Random Places
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="m_line">
                            <i class="fa fa-twitter fa-lg"></i> Follow Us
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="col-lg-3">
                        <div class="hide_rasp">
                            <i class="fa fa-rocket fa-lg"></i> About
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
                            <i class="fa fa-user fa-lg"></i> Random People
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
                            <i class="fa fa-map-marker fa-lg"></i> Random Places
                        </div>

                        <ul>
<?php
    // Loop thru the random locations
    for($i=0;$i<4;$i++) {
        $url = $base_url.'hot/gender/both/lon/'.$locations[$i]['lon'].'/lat/'.$locations[$i]['lat'].'/';
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
                            <i class="fa fa-twitter fa-lg"></i> Follow Us
                        </div>

                        <ul>
                            <!-- Twitter Button -->
                            <li>
                                <a href="https://twitter.com/TwinderIO" class="twitter-follow-button" data-show-count="true" data-size="medium">Follow @TwinderIO</a>
                            </li>

                            <li>
                                <a class="twitter-share-button"
                                    data-url="<?php echo $base_url; ?>"
                                    data-text="Twinder - Tinder for Web"
                                    data-via="TwinderIO"
                                    data-hashtags="Twinder,TinderForWeb">
                                    Tweet
                                </a>
                            </li>

                            <li>
                                <a href="http://instagram.com/twinderio?ref=badge" class="ig-b- ig-b-v-24"><img src="//badges.instagram.com/static/images/ig-badge-view-24.png" alt="Instagram" /></a>
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
    </div>

    <!-- jQuery and Bootstrap JS files -->
    <script src="<?php echo $js_url; ?>jquery/jquery.js"></script>

<?php
    if(in_array($controller, $slider_pages) || $controller == 'signin') {
?>
    <script src="<?php echo $js_url; ?>ui/jquery-ui.min.js"></script>
<?php
    }
?>

    <script src="<?php echo $js_url; ?>bootstrap/bootstrap.min.js"></script>
    <script src="<?php echo $js_url; ?>main.js"></script>
<?php
    if(in_array($controller, $maps_pages)) {
?>
    <!-- Google Maps JS -->
    <script src="https://maps.googleapis.com/maps/api/js"></script>
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
?>
    <script src="<?php echo $js_url.$controller; ?>.js"></script>
<?php
    } 

    if($base_url != '/twinder/') {
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
    <!-- Twitter JS -->
    <script>
        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
    </script>
</body>
</html>