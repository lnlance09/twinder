<?php 
	class Facebook_model extends CI_Model {
		// Define the user agents
		public $ios_agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B440 [FBAN/FBIOS;FBAV/23.0.0.7.11;FBBV/6551639;FBDV/iPhone7,2;FBMD/iPhone;FBSN/iPhone OS;FBSV/8.1.2;FBSS/2; FBCR/AT&T;FBID/phone;FBLC/en_US;FBOP/5]';
		public $android_agent = '[FBAN/FB4A;FBAV/24.0.0.30.15;FBBV/5955490;FBDM/{density=2.0,width=1600,height=2560};FBLC/en_US;FBCR/;FBMF/samsung;FBBD/samsung;FBPN/com.facebook.katana;FBDV/SM-T700;FBSV/4.4.2;FBBK/1;FBOP/1;FBCA/armeabi-v7a:armeabi;]';
		public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
		
		// Define the client ID and the scope of the app
		public $client_id = 464891386855067;
		public $permissions = array('basic_info', 'email', 'public_profile', 'user_about_me', 'user_activities', 'user_birthday', 'user_education_history', 'user_friends', 'user_interests', 'user_likes', 'user_location', 'user_photos', 'user_relationship_details');
		// public $permissions = array('baseline', 'email', 'public_profile', 'user_about_me', 'user_activities', 'user_birthday', 'user_friends', 'user_interests', 'user_likes', 'user_photos', 'user_relationship_details', 'user_status');
		
		public $api_key = '882a8490361da98702bf97a021ddc14d';
		public $oauth = 'CAAAAUaZA8jlABACUPdBxkI4AKi0SMGAgO3y4TaDqYHo5LjOJORiegzGJjiIb3oZBbWLRQ9YjhvQv3nceXW8osOoE4EN3NxMnv8FZAfxwU8Fur17mZAOpKcXkPh8D3PATNDiLEZBvYfC13yuP57tWVi46HokYVFiaZCrtRBFVr9UqPnW9tYAkTX8F7FulTv1HmZCT3FSBnJpgL8BbVy1wfxv';

		public function __construct() {       
			parent:: __construct();

			// Load the helpers file
			$this->load->helper('common_helper');
		}

		public function AcceptApp() {
			// Define all of the post parameters
			$data = array('format' => 'json',
						'proxied_app_id' => $this->client_id,
						'android_key_hash' => 'YJgjuu05nZqXE41jZVDr6CAUzy4',
						'is_refresh_only' => TRUE,
						'locale' => 'en_US',
						'client_country_code' => 'US',
						'method' => 'auth.androidauthorizeapp',
						'fb_api_req_friendly_name' => 'authorize_app_method',
						'fb_api_caller_class' => 'com.facebook.platform.auth.server.AuthorizeAppMethod');
			
			// Define all of the headers
			$headers = array('Authorization: OAuth '.$this->oauth,
							'Content-Type: application/x-www-form-urlencoded');

			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://api.facebook.com/method/auth.androidauthorizeapp');
			curl_setopt($ch, CURLOPT_USERAGENT, $this->android_agent);    
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));     
			$data = curl_exec($ch); 					
		    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
			curl_close($ch);
				  	
			return array('code' => $http, 'data' => @json_decode($data, TRUE));
		}

		public function AndroidLogin($email, $password) {
			$data = array('api_key' => $this->api_key,
						'client_country_code' => 'US',
						'credentials_type' => 'password',
						'email' => $email,
						'error_detail_type' => 'button_with_disabled',
						'fb_api_caller_class' => 'com.facebook.katana.server.handler.Fb4aAuthHandler',
						'fb_api_req_friendly_name' => 'authenticate',
						'format' => 'json',
						'generate_session_cookies' => 1,
						'locale' => 'en_US',
						'machine_id' => 'cJJbVDVx43_CZW-6z8lelOf-',
						'method' => 'auth.login',
						'password' => $password,
						//'sig' => 'c1e8309348522f4d51484f3fb69a838f'
						);

			// Define all of the headers
			$headers = array('Content-Type: application/x-www-form-urlencoded');

			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://b-api.facebook.com/method/auth.login');
			curl_setopt($ch, CURLOPT_USERAGENT, $this->android_agent);    
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));     
			$data = curl_exec($ch); 					
		    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
			curl_close($ch);
				  	
			return array('code' => $http, 'data' => @json_decode($data, TRUE));
		}

		/**
		 * Log into Facebook
		 * @param {string} [email] The email of the user tring to log in
		 * @param {string} [password] The password of the user trying to log in
		 */
		public function FacebookLogin($email, $password) {  
			// Define the cookies files
			$cookies = CookieFile($email);
		    
		    // Build the query
		    $data = array('charset_test' => htmlspecialchars("&euro;,&acute;,â‚¬,Â´,æ°´,Ð”,Ð„"),
		            	'lsd' => 'OsC-Z',
		            	'locale' => 'en_US',
		            	'email' => $email,
		            	'pass' => $password,
		            	'persistent' => 1,
		            	'default_persistent' => 0); 
              
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://www.facebook.com/login.php');
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));     
			curl_setopt($ch, CURLOPT_REFERER, 'https://www.facebook.com/');  
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
			curl_exec($ch); 					
		    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
			curl_close($ch);
				  	
			return $http;   
		}

		public function FacebookOAuth($email) {
			// Define the cookies files
			$cookies = CookieFile($email);

			//echo $cookies;
			//die;
			$data = array('fb_dtsg' => 'AQERE3vuQGXO',
						'charset_test' => htmlspecialchars("&euro;,&acute;,â‚¬,Â´,æ°´,Ð”,Ð„"),
						'e2e' => '{"init":'.time().',"submit_0":'.time().'}',
						'from_post' => 1,
						'app_id' => $this->client_id,
						'redirect_uri' => 'fbconnect://success',
						'display' => 'touch',
						'access_token' => '',	
						'sdk' => 'ios',
						'proxy_access_token' => '',	
						'encoded_state' => '{"is_open_session":true,"is_active_session":true,"com.facebook.sdk_client_state":true,"3_method":"fb_application_web_auth","0_auth_logger_id":"D12769EF-5E1C-4DFF-9BD6-4ADBBE4784C9"}',
						'private' => '',	
						'login' => '',	
						'read' => '', //implode(',', $this->permissions),
						'write' => '',	
						'readwrite' => '',	
						'extended' => '',	
						'social_confirm' => '',	
						'confirm' => '',	
						'sheet_name' => 'initial',
						'gdp_version' => 4,
						'seen_scopes' => '', //implode(',', $this->permissions),
						'return_format' => 'return_scopes,denied_scopes,access_token',
						'domain' => '',	
						'sso_device' => 'ios',
						'auth_type' => '',	
						'auth_nonce' => '',	
						'auth_token' => '',	
						'default_audience' => '',	
						'seen_revocable_perms_nux' => '',	
						'ref' => 'Default',
						'__CONFIRM__' => 'OK');
			// FormatArray($data);
			// die;
			// Define all of the headers
			$headers = array('Content-Type: application/x-www-form-urlencoded');

			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://m.facebook.com/v2.1/dialog/oauth/read');
			// curl_setopt($ch, CURLOPT_USERAGENT, $this->ios_agent);   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);    
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);     
			$data = curl_exec($ch); 					
			curl_close($ch);
	 	
			return $data;
		}

		public function FacebookCheck() {
			$token = 'CAAGm0PX4ZCpsBAEs4aOioOeIyT0ZCLeyOPWZBuEAgbslXzN45ilI08RdagpZBJue5l3ZCmIekreg0fZBParDG00mNalraeKkL1kGxoV57W0f1JdLuvZACNaZBkLZAhCelTLzSGNZAnpEXyYtZAp8pnXObJwSZBjlGUX1aZA3AkiMHXQyRl7Tk9hyxiqClq8jj2TAUz7chJvGZAZC6KnDuzdqUPPEq0BIQ0bvOKXwYgZD';
			
			// Define the query parameters that need to be sent along with the request
			$batch[0] = array('method' => 'GET',
							'relative_url' => 'me?format=json&access_token='.$token.'&sdk=ios');
			$batch[1] = array('method' => 'GET',
							'relative_url' => 'me/permissions?format=json&access_token='.$token.'&sdk=ios');

			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v2.1');
			curl_setopt($ch, CURLOPT_USERAGENT, 'FBiOSSDK.3.17');   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);     
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('batch_app_id' => $this->client_id, 'batch' => json_encode($batch))));    
			$data = curl_exec($ch); 					
			curl_close($ch);
				  	
			return @json_decode($data, TRUE);	
		}

		/**
		 * Get a Facebook access token for Tinder so a user can log in
		 * @param {string} [email] The email of the user trying to log in
		 * @param {string} [password] The passwod of the user trying to log in
		 */
		public function FacebookToken($email, $password) {
			$login = $this->FacebookLogin($email, $password);

			if($login == 200) {
				// Define the cookies file
				$cookies = CookieFile($email);
			    $uri = 'https://www.facebook.com/connect/login_success.html';
				$url = 'https://www.facebook.com/dialog/oauth?client_id='.$this->client_id.'&redirect_uri='.urlencode($uri).'&scope='.implode(',', $this->permissions).'&response_type=token';
						
				$ch = curl_init();  
				curl_setopt($ch, CURLOPT_URL, $url);  
				curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);  
				curl_setopt($ch, CURLOPT_HEADER, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);  
				$data = curl_exec($ch);   

				// echo $data;
			    $curl_info = curl_getinfo($ch);

				// Get the headers and then the HTTP code
				$headers = substr($data, 0, $curl_info['header_size']);
				$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				// Make sure that the HTTP redirects to a location that has an access token in the URL
				if($code == 302) {
					preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
					$break = explode('access_token=', $matches[1]);
					// FormatArray($break);

					if(count($break) == 2) {
						// Split the URL once more to get the access token value
						$exp = explode('&', $break[1]);
						$token = $exp[0];	
					}  else {
						$token = 'Failed';
					}
				} elseif($code == 200) {
					$token = 'Permissions';
				} else {
					$token = 'Failed';
				}
						
				return $token;  
			} else {
				return 'Error';
			}
		} 

		/**
		 * Get a Facebook page's name and profile picture. Can be used for Facebook pages and users
		 * @param {string} [token] The Facebook access token
		 * @param {string} [page] The page ID or user ID
		 */
		public function ScrapePage($token, $page) {
			$data = array('access_token' => $token,
						'format' => 'json',
						'method' => 'get',
						'pretty' => 0,
						'suppress_http_code' => 1,
						'fields' => 'name,picture');

			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v2.2/'.$page.'?'.http_build_query($data));
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$data = curl_exec($ch); 					 
			curl_close($ch);
				  	
			return @json_decode($data, TRUE);  
		}

		// Generate an API signature
		public function GenerateSignature() {
			$sig = base64_encode(hash_hmac('sha256', $time."\n00.0000000000,0.0000000000", 'Sq<uW-&Q2s%Q=s!', TRUE));
		}
	}