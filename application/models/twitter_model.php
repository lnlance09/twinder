<?php
	class Twitter_model extends CI_Model {
		// Define the API key and secret
		public $api_key = '5S2buTVsPQb13zCvJfVmnYWm8';
		public $api_secret = 'Jr5Lb3oHB8akqCYJnOLnArofpcKxjpdF9Bynu979O3tyuUb09n';

		// Define the user agent for finding direct users
		public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
		
		// Define the API URLs
		public $token_url = 'https://api.twitter.com/oauth/request_token';
		public $access_url = 'https://api.twitter.com/oauth/access_token';

		// Define the user search URL
		public $search_url = 'https://twitter.com/i/search/typeahead.json';

		// Define the users statuses API endpoint
		public $users_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

		// Define the users statuses API endpoint
		public $home_url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';

		// Define the verification URL
		public $verify_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';

		/**
		 * [__construct description]
		 */
		public function __construct() {       
			parent:: __construct();

			// Load the database
			$this->load->database();

			// Load the session library
			$this->load->library('session');
		}

		/**
		 * Convert the OAuth token into an access token
		 * @param {string} [token] The OAuth token
		 * @param {string} [verifier] The OAuth verifier
		 * @return {string} The access token from Twitter's API
		 */
		public function AccessToken($token, $verifier) {
			// Define the signature base string
			$data = array('oauth_verifier' => $verifier, 'oauth_token' => $token); 
			$nonce = $this->OAuthNonce();
			$string = 'POST&'.urlencode($this->access_url).'&'.urlencode(http_build_query($data));
			$sign_key = urlencode($this->api_secret).'&';
			$sig = base64_encode(hash_hmac('sha1', $string, $sign_key, TRUE));
			$header = array('Authorization: OAuth oauth_consumer_key="'.$this->api_key.'", oauth_nonce="'.$nonce.'", oauth_signature="'.urlencode($sig).'", oauth_signature_method="HMAC-SHA1", oauth_timestamp="'.time().'", oauth_version="1.0"');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->access_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}

		/**
		 * Redirect the user to the authentication page
		 * @return 
		 */
		public function Authenticate() {
			// Get the OAuth token
			$request = $this->RequestToken();
			$token = $request['oauth_token'];

			// Send the request for the user to either login and/or authorize the app
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.twitter.com/oauth/authenticate?oauth_token='.$token);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			$response = curl_exec($ch);
			$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			// Redirect the user to the authorization page
			if($http == 302) {
				preg_match('/href="([^\s"]+)/', $response, $match);
				header('Location: '.$match[1]);
			} else {
				header('Location: https://api.twitter.com/oauth/authenticate?oauth_token='.$token);
			}
		}

		/**
		 * Get a given user's tweets
		 * @param {string} [username] The Twitter user's username
		 * @return {array} A JSON decoded array from Twitter's API
		 */
		public function FetchTweets($username, $count) {
			// Define the signature base string
			$nonce = $this->OAuthNonce();
			$data = array('count' => $count,
						'oauth_consumer_key' => $this->api_key,
						'oauth_nonce' => $nonce,
						'oauth_signature_method' => 'HMAC-SHA1',
						'oauth_timestamp' => time(),
						'oauth_version' => '1.0',
						'screen_name' => $username); 
			$string = 'GET&'.urlencode($this->users_url).'&'.urlencode(http_build_query($data));
			$sign_key = urlencode($this->api_secret).'&';
			$sig = base64_encode(hash_hmac('sha1', $string, $sign_key, TRUE));
			$header = array('Authorization: OAuth oauth_consumer_key="'.$this->api_key.'", oauth_nonce="'.$nonce.'", oauth_signature="'.urlencode($sig).'", oauth_signature_method="HMAC-SHA1", oauth_timestamp="'.time().'", oauth_version="1.0"');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->users_url.'?screen_name='.$username.'&count='.$count);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$response = curl_exec($ch);
			curl_close($ch);
			return @json_decode($response, TRUE);
		}

		/**
		 * Send an HTTP request to Twitter to get JSON of related users based upon what was searched for
		 * @param {string} [q] The search query
		 * @return {array} An array containing the list of relevant Twitter users
		 */
		public function FindUsers($q) {
			$data = array('count' => 10,
						'experiments' => '',
						'filters' => TRUE,
						'q' => $q,
						'result_type' => 'users',
						'src' => 'SEARCH_BOX');
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->users_url.'?'.http_build_query($data));
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
			curl_setopt($ch, CURLOPT_REFERER, 'https://twitter.com/');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Phx: true'));
			$response = curl_exec($ch);
			curl_close($ch);
			return @json_decode($response, TRUE);
		}

		/**
		 * Generate the OAuth Nonce for Twitter's API request header. This will return a 32 character alphanumeric string
		 * @return {string} A 32 character alphanumeric string
		 */
		public function OAuthNonce() {
			// Define the length of the string
			$length = 32;
		    $charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
		    $str = '';
		    
		    while($length--) {
		        $str .= $charset[mt_rand(0, strlen($charset)-1)];
		    }

		    return $str;
		}

		/**
		 * Get a request token for Twitter's API
		 * @return {array} An array containing the request token
		 */
		public function RequestToken() {
			// Define the signature base string
			$nonce = $this->OAuthNonce();
			$data = array('oauth_consumer_key' => $this->api_key,
						'oauth_nonce' => $nonce,
						'oauth_signature_method' => 'HMAC-SHA1',
						'oauth_timestamp' => time(),
						'oauth_version' => '1.0');
			$string = 'GET&'.urlencode($this->token_url).'&'.urlencode(http_build_query($data));
			$sign_key = urlencode($this->api_secret).'&';
			$sig = base64_encode(hash_hmac('sha1', $string, $sign_key, TRUE));
			$header = array('Authorization: OAuth oauth_consumer_key="'.$this->api_key.'", oauth_nonce="'.$nonce.'", oauth_signature="'.urlencode($sig).'", oauth_signature_method="HMAC-SHA1", oauth_timestamp="'.time().'", oauth_version="1.0"');
			// FormatArray($header);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->token_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$response = curl_exec($ch);
			curl_close($ch);
	
			// Save all of the data to an array
			parse_str($response, $array);
			return $array;
		}

		/**
		 * Get a given user's tweets
		 * @return {array} A JSON decoded array from Twitter's API
		 */
		public function Verify($token) {
			// Define the signature base string
			$nonce = $this->OAuthNonce();
			$data = array('oauth_consumer_key' => $this->api_key,
						'oauth_nonce' => $nonce,
						'oauth_signature_method' => 'HMAC-SHA1',
						'oauth_timestamp' => time(),
						'oauth_token' => $token,
						'oauth_version' => '1.0'); 
			$string = 'GET&'.urlencode($this->verify_url).'&'.urlencode(http_build_query($data));
			$sign_key = urlencode($this->api_secret).'&';
			$sig = base64_encode(hash_hmac('sha1', $string, $sign_key, TRUE));
			$header = array('Authorization: OAuth oauth_consumer_key="'.$this->api_key.'", oauth_nonce="'.$nonce.'", oauth_signature="'.urlencode($sig).'", oauth_signature_method="HMAC-SHA1", oauth_timestamp="'.time().'", oauth_token="'.$token.'", oauth_version="1.0"');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->verify_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$response = curl_exec($ch);
			curl_close($ch);
			return @json_decode($response, TRUE);
		}
	}