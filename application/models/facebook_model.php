<?php 
	class Facebook_model extends CI_Model {
		public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
		public $client_id = 464891386855067;
		public $permissions = array('basic_info', 'email', 'public_profile', 'user_about_me', 'user_activities', 'user_birthday', 'user_education_history', 'user_friends', 'user_interests', 'user_likes', 'user_location', 'user_photos', 'user_relationship_details');
		
		public function __construct() {       
			parent:: __construct();
		}

		/**
		 * Return the path to a users' cookie file
		 * @param {string} [email] The user's email
		 * @return {string} The path too the cookies file
		 */
		public function CookieFile($email) {
			$exp = explode('@', $email);
			$file = (count($exp) > 1 ? $exp[0] : $email);
		    return 'cookies/'.$file.'.txt';
		}

		/**
		 * Log into Facebook
		 * @param {string} [email] The email of the user tring to log in
		 * @param {string} [pass] The password of the user trying to log in
		 * @return {int} The HTTP code of the request
		 */
		public function FacebookLogin($email, $pass) {  
			// Define the cookies files
			$cookies = $this->CookieFile($email);
              
            // Define the cookies for the request headers
            $headers = array('Cookie: reg_ext_ref=http%3A%2F%2Faceandeverett.com%2Fwp-login.php; datr=BRSiVc-wqtVsZxRKnXP2PlM_; reg_fb_gate=https%3A%2F%2Fwww.facebook.com%2FAceandEverett; reg_fb_ref=https%3A%2F%2Fwww.facebook.com%2FAceandEverett');

            $stamp = 'W1tbNCw5LDMxLDc3LDkxLDEyNCwxMzUsMTUwLDE1MywxNzcsMjExLDIyMCwyMzEsMjM3LDI0OSwyNTMsMjc3LDI3OSwyODgsMjk5LDMwNSwzMTQsMzIxLDM0NCwzNTgsMzgwLDM4OSwzOTEsMzk2LDQxMiw0MTcsNDI0LDQ0NCw0NTQsNDU1LDQ2OCw0ODIsNDg2LDUyMSw1MjcsNjU2LDc1M11dLCJBWmtId2dnUC1nQUkzaVkwY3p6SnZVOFVxYUZhTEpjZXRXZGlXek43Xy1uaF9ORW9UNmdxU3FneXhkaVhZQ2E3d1RHbzI3X2g0OFNlNmtoMjd6YzlHZm93RFIya2t6aUx0NllkNGJ3RjltMXM3cmlpNzlPY0tWSkV2bE1hSzlZNW92OTc2NFZSbkZSaFVKSnFEWk90emR5cHI4eVNBc2hyaFJoOHQxMEJBdXhvYk9SRzNXSml4QUpQcXpTLWVfWmJJNXE1QTZnekR1MjdNNERmY3pOUHJ1Y203SURfbVVqbjJENkd2Q19OaFJkSlloTkhxLTBMZmZlRWliTUlRbGFMVWdrIl0=';
            $data = array('lsd' => 'AVqr-xYW',
						'email'	=> $email,
						'pass' => $pass,
						'default_persistent' => 0,
						'timezone' => 240,
						'lgndim' => 'eyJ3IjoxMjgwLCJoIjo4MDAsImF3IjoxMjI4LCJhaCI6Nzc3LCJjIjoyNH0=',
						'lgnrnd' => '025253_SZ_b',
						'lgnjs'	=> 1436694775,
						'locale' => 'en_US',
						'qsstamp' => $stamp);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://www.facebook.com/login.php?login_attempt=1');
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
			curl_setopt($ch, CURLOPT_REFERER, 'https://www.facebook.com/?_rdr=p'); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies); 
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);      
			curl_exec($ch); 
			$info = curl_getinfo($ch);
		    curl_close($ch);
			return $info['http_code'];
		}

		/**
		 * Get a Facebook access token for Tinder so a user can log in
		 * @param {string} [email] The email of the user trying to log in
		 * @param {string} [password] The passwod of the user trying to log in
		 * @return {string} Either an access token or a reason for the failure
		 */
		public function FacebookToken($email, $password) {
			$login = $this->FacebookLogin($email, $password);

			if($login == 200) {
				// Define the cookies file
				$cookies = $this->CookieFile($email);
			    $uri = 'https://www.facebook.com/connect/login_success.html';
				$url = 'https://www.facebook.com/v2.0/dialog/oauth?client_id='.$this->client_id.'&redirect_uri='.urlencode($uri).'&scope='.implode(',', $this->permissions).'&response_type=token';

				$ch = curl_init();  
				curl_setopt($ch, CURLOPT_URL, $url);  
				curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);  
				curl_setopt($ch, CURLOPT_HEADER, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
				$data = curl_exec($ch);   
				$info = curl_getinfo($ch);
				curl_close($ch);

				// Make sure that the HTTP redirects to a location that has an access token in the URL
				if($info['http_code'] == 302) {
					$headers = substr($data, 0, $info['header_size']);
					preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
					$break = explode('access_token=', $matches[1]);

					if(count($break) == 2) {
						$exp = explode('&', $break[1]);
						$token = trim($exp[0]);	
					}  else {
						$ch = curl_init();  
						curl_setopt($ch, CURLOPT_URL, trim($break[0]));  
						curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent); 
						curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
						curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
						curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
						$data = curl_exec($ch);
						$info = curl_getinfo($ch);
						curl_close($ch);

						if($info['http_code'] == 302) {
							$headers = substr($data, 0, $info['header_size']);
							preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $match);
							$break = explode('access_token=', $match[1]);

							if(count($break) == 2) {
								$exp = explode('&', $break[1]);
								$token = trim($exp[0]);	
							} else {
								$token = 'Failed';
							}
						} else {
							$token = 'Perm';
						}
					}
				} elseif($code == 200) {
					$token = 'Perm';
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
		 * @return {array} 
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
	}