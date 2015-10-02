<?php 
	class Facebook_model extends CI_Model {
		public $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3';
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
		    return getcwd().'cookies/'.$file.'.txt';
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
            $headers = array('Accept-Charset: utf-8',
							'Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3',
							'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'
							);

            // Define all of the post data in an array
            $data = array('charset_test' => '€,´,€,´,水,Д,Є',
            			'email' => $email,
            			'pass' => $pass,
            			'login' => 'Login'
            			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://m.facebook.com/login.php');
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);  
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies); 
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
			curl_setopt($ch, CURLOPT_REFERER, 'http://m.facebook.com'); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    
			$data = curl_exec($ch); 
			$info = curl_getinfo($ch);
		    curl_close($ch);
		    // echo $data;
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
			// echo $login;
			// die;

			if($login == 200) {
				// Define the cookies file
				$cookies = $this->CookieFile($email);
			    $uri = 'https://www.facebook.com/connect/login_success.html';
				$url = 'https://www.facebook.com/v2.0/dialog/oauth?client_id='.$this->client_id.'&redirect_uri='.urlencode($uri).'&scope='.implode(',', $this->permissions).'&response_type=token';

				// Define the cookies for the request headers
            	$headers = array('Accept-Charset: utf-8',
								'Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3',
								'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'
								);

				$ch = curl_init();  
				curl_setopt($ch, CURLOPT_URL, $url);  
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.93 Safari/537.36");  
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_REFERER, 'http://m.facebook.com');
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
				$data = curl_exec($ch) or die(curl_error($ch));   
				$info = curl_getinfo($ch);
				curl_close($ch);
				
				// echo $data;
				// echo '<pre>';
				// print_r($info);
				// echo '</pre>';

				// Make sure that the HTTP redirects to a location that has an access token in the URL
				if($info['http_code'] == 302) {
					$headers = substr($data, 0, $info['header_size']);
					preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
					$break = explode('access_token=', $matches[1]);

					// echo '<pre>';
					// print_r($break);
					// echo '</pre>';

					if(count($break) == 2) {
						$exp = explode('&', $break[1]);
						$token = trim($exp[0]);	
					} else {
						// echo $break[0];
						
						$ch = curl_init();  
						curl_setopt($ch, CURLOPT_URL, trim($break[0]));  
						curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent); 
						curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
						curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
						curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
						$data = curl_exec($ch);
						$info = curl_getinfo($ch);
						curl_close($ch);
						echo $data;

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
				} elseif($info['http_code'] == 200) {
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
						'fields' => 'name,picture'

						);

			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v2.2/'.$page.'?'.http_build_query($data));
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$data = curl_exec($ch); 					 
			curl_close($ch);  	
			return @json_decode($data, TRUE);  
		}
	}