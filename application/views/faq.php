<?php
    $base_url = $this->config->base_url();
?>
		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>

                <div id="faq">
                    <h2>
                        What is WeTinder?
                    </h2>

                    <p>
                        WeTinder is Tinder for the web. 
                        It serves the same, exact functionality as the app, but just with a cool, new interface that's made exclusively for the web.
                    </p>


                    <h2>
                        How does it work?
                    </h2>

                    <p>
                        WeTinder works almost entirely in conjunction with the Tinder API. 
                        Everything that you do on WeTinder will also be done on the actual app. 
                        However, none of what you do on the actual app will be done on here.  
                        You can like or pass another user. You can send and receive messages. 
                        You can search for certain users in particular.
                    </p>


                    <h2>
                        How is it Different than the app?
                    </h2>

                    <p>
                        WeTinder features a few distinctive elements that help give it a reputation that simply cannot be replicated on the mobile app. <br>
                        Search for anyone: You can search for people by their names and ages insted of just by location. <br>
                        Keep Records: Keep track of all of the people that you have liked/passed. <br>
                        Find the most popular users: See the users that have gotten the most matches and likes. <br>
                    </p>


                    <h2>
                        Can I user WeTinder if I haven't signed up for Tinder?
                    </h2>

                    <p>
                        Unfortunately, no. You must have already created a Tinder profile on your phone or tablet. 
                        Once you have done that, you can successfully use WeTinder.
                    </p>


                    <h2>
                        Why do I need to log into my Facebook?
                    </h2>

                    <p>
                        Because the only way to sign in to or sign up for Tinder is with Facebook. 
                        All of the passwords to Facebook that have been submitted on this site have been encrypted with <a href="http://php.net/manual/en/function.sha1.php" target="_blank">SHA-256</a>.
                        To paraphrase that so most people can understand, none of the raw values of the passwords are ever stored. 
                        They're all converted to a 40-character hexadecimal number.
                    </p>


                    <h2>
                        Why does WeTinder need to access my location?
                    </h2>

                    <p>
                        WeTinder needs to access your current location to match you with Tinder users that are located within a certain mile radius that yu have specified in your app's settings.
                    </p>


                    <h2>
                        Why isn't there an "AutoLike" feature?
                    </h2>

                    <p>
                        I stumbled across an iPhone app called <a href="http://tinderliker.com/" target="_blank">"Liker"</a> a few months ago. It's remarkably similar to WeTinder in a few important respects. 
                        However, this app completely disregards the many legitimate uses of Tinder so much that it almost becomes something of a robot. 
                        From my experiences reverse engineering Tinder, using "Liker" or any other third party app that automatically searches for new users and likes them all in a repeated fashion for long periods of time is a great way to get your Tinder account banned.
                        Don't make the foolish mistake of underestimating the intelligence of network administarators. Sooner or later, the folks at Tinder will recoginize that it's just a script going through the motions and not a real person.
                    </p>


                    <h2>
                        Is there a way to disguise your location?
                    </h2>

                    <p>
                        Yes, of course. But, again, this app is for using Tinder for legitimate purposes. 
                        WeTinder will not allow you to use anything other than your real location.
                    </p>
                </div>
            </div>
        </div>