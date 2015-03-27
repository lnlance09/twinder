<?php 
	class Facebook_model extends CI_Model {
		public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
		public $client_id = 464891386855067;
		public $permissions = array('basic_info', 'email', 'public_profile', 'user_about_me', 'user_activities', 'user_birthday', 'user_education_history', 'user_friends', 'user_interests', 'user_likes', 'user_location', 'user_photos', 'user_relationship_details');
		// public $permissions = array('baseline', 'email', 'public_profile', 'user_about_me', 'user_activities', 'user_birthday', 'user_friends', 'user_interests', 'user_likes', 'user_photos', 'user_relationship_details', 'user_status');
		
		public function __construct() {       
			parent:: __construct();

			$this->load->helper('common_helper');
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
		 * @param {string} [password] The password of the user trying to log in
		 * @return {int} The HTTP code of the request
		 */
		public function FacebookLogin($email, $password) {  
			// Define the cookies files
			$cookies = $this->CookieFile($email);
		    
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
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
			curl_setopt($ch, CURLOPT_HEADER, TRUE);     
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));     
			curl_setopt($ch, CURLOPT_REFERER, 'https://www.facebook.com/');  
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
			$data = curl_exec($ch); 					
		    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
			curl_close($ch);
			
			if($http == 302) {
				return $data;
			}   
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