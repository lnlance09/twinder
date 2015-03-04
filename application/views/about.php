<?php
    $base_url = $this->config->base_url();
?>
		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>

                <div id="about">
                    <p>
                        Twinder is what I like to describe as a splendid mix of elements from both Twitter and Tinder. 
                        Everything that you do while logged into Twinder will also be done on the app itself. 
                        But, the opposite isn't entirely true.  that you do on the actual Tinder app will be reflected on WeTinder.
                    </p>

                    <p>
                        About one year ago, I <a href="http://lancenewman.me/reverse-engineering-the-tinder-api/" target="_blank">reverse engineered Tinder's API</a> with a little help from <a href="http://www.charlesproxy.com/" target="_blank">Charles</a>, a reverse proxy tool that allows people to sniff out the traffic from a third party device like a tablet or a phone.
                        Once I knew what types of HTTP requests triggered the right kind of actions on the app, I effectively replicated them with cURL, a HTTP library that's used to send and receive requests.
                    </p>

                    <p>
                        Soon thereafter, it came to my attention that although Tinder was a wildly popular app, it was lacking in some key areas.
                        For starters, there was too much anonymity. Sure, anonymity is great because it helps users keep their distance from the crowds of creepy males on Tinder that will swipe right on just about anything with two legs.
                        But, an online directory of Tinder users would serve an enormously useful purpose. It would grant more choices to millions of Tinder users. It would let users search for people in partcular instead of just having to hope that you run into thanks to to sheer luck. It would also rank the most popular and most desired Tinder users based upon a number of key factors.
                    </p>
                </div>
            </div>
        </div>