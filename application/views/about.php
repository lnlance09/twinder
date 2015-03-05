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

        <!-- Twitter Login -->
        <script id="sdk-js" src="https://cdn.digits.com/1/sdk.js" async></script>

        <script>
            document.getElementById('sdk-js').onload = function() {
                println('SDK Loaded');
                  
                var btn = document.createElement('input');
                btn.setAttribute('type', 'submit');
                btn.setAttribute('value', 'Log in');
                btn.addEventListener('click', login);
                document.body.appendChild(btn);
                
              /* Initialize Digits SDK using your application's consumer key. */
              Digits.init({ consumerKey: '5S2buTVsPQb13zCvJfVmnYWm8'})
                .done(function() {
                  println("Digits is initialized")
                  // Digits.getLoginStatus()
                  //   .done(onLoginStatus)
                  //   .fail(onLoginStatusFailure);
                }).fail(function() {
                  println("Digits failed to initialize")
                })
                
                /* Launch the Login to Digits flow. */
                function login(){
                println('SDK Start Login flow');

                Digits.logIn()
                  .done(onLogin)
                  .fail(onLoginFailure);
                } 
              
                /*
                * loginStatusResponse = {
                *   status: string ["unknown"|"not_authorized"|"authorized"],
                *   oauth_echo_headers: {
                *     'X-Verify-Credentials-Authorization': string (HTTP Request header)
                *     'X-Auth-Service-Provider': string (HTTP Request Url)
                *   } 
                * }
                *
                *    unknown:          User is not logged in to Digits, may or may not have authorized your app
                *    not_authorized:   User is logged in to Digits but has not authorized your app yet.
                *    authorized:       User is logged in to Digits and has authorized your app.
                *
                *    NOTE: The OAuth Echo headers will only be returned if User has authorized your app.
                */
                function onLoginStatus(loginStatusResponse) {
                    println('Login status: ', loginStatusResponse);
                }

                /*
                * error = {
                *   type: string,
                *   message: string
                * }
                */
                function onLoginStatusFailure(error) {
                    println('Login status error: ', error); 
                }  
                     
                /*
                * loginResponse = {
                *   oauth_echo_headers: {
                *     'X-Verify-Credentials-Authorization': string (HTTP Request header)
                *     'X-Auth-Service-Provider': string (HTTP Request Url)
                *   }
                * }
                *
                */
                function onLogin(loginResponse){
                    println('oAuthEcho Headers: ', loginResponse);

                    // You must POST these headers to your server to safely invoke Digits' API
                    // and get the logged-in user's data. You will not be able to call it directly
                    // from the browser.
                    var oAuthHeaders = parseOAuthHeaders(loginResponse.oauth_echo_headers);

                    // For DEMO purposes 
                    var requestUrl = ["curl '", oAuthHeaders.apiUrl, "' -H 'Authorization: ", oAuthHeaders.headers, "'"].join('');
                    println('cURL:'); 
                    println('', requestUrl); 
                }

                /*
                * error = {
                *   type: string,
                *   message: string
                * }
                *
                * Note: type == 'abort' means the user closed the Login flow
                */
                function onLoginFailure(error) {
                    println('Login error: ', error); 
                }  

                function parseOAuthHeaders(oAuthEchoHeaders) {
                    var credentials = oAuthEchoHeaders['X-Verify-Credentials-Authorization'];
                    var apiUrl = oAuthEchoHeaders['X-Auth-Service-Provider'];
                              
                    return {
                      apiUrl: apiUrl,
                      headers: credentials
                    };
                } 

                function println(text, response) {
                    var message = document.createElement('p');
                    message.innerText = text;

                    if(response) {
                      var code = document.createElement('code');
                      code.innerText = typeof response == 'string' ? response : JSON.stringify(response);
                      message.appendChild(code);
                    }

                    document.body.appendChild(message);
                } 
            };
        </script>