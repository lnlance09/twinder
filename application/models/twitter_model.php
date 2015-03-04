<?php
	class Twitter_model extends CI_Model {
		public $api_key = '5S2buTVsPQb13zCvJfVmnYWm8';
		public $api_secret = '2uuj8cQeTjtCh0LbQMoVBSgzyI3KQHou49tsys4gvJUH3oLiFn';
		public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
		public $auth_url = 'https://api.twitter.com/oauth2/request_token';
		public $users_url = 'https://twitter.com/i/search/typeahead.json';
		public $single_tweet_url = 'https://api.twitter.com/1.1/statuses/show.json?id=';

		public function __construct() {       
			parent:: __construct();

			// Load the database
			$this->load->database();

			// Load the session library
			$this->load->library('session');
		}

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

			// Decode the response
			return @json_decode($response, TRUE);
		}

		// Get the OAuth token that is necessary to execute requests using the Twitter API
		public function GetAuthToken() {
			$concat = rawurlencode($this->api_key).':'.rawurldecode($this->api_secret);
			$encode = base64_encode($concat);

			// Define the headers
			$headers = array('User-Agent: My Twitter App v1.0.23',
							'Authorization: Basic '.$encode,
							'Content-Type: application/x-www-form-urlencoded;charset=UTF-8');

			// Build the query string
			$data = array('grant_type' => 'client_credentials');
			$string = http_build_query($data);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->auth_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			curl_close($ch);
			
			// Get the access token
			$decode = @json_decode($response, TRUE);
			return $decode['access_token'];
		}

		public function GetSingleTweet($tweet_id) {
			// Get the OAuth token and put it in the header
			$token = $this->GetAuthToken();
			$headers = array('Authorization: Bearer '.$token);

			// Define the URL that needs to handle the request
			$url = $this->single_tweet_url.$tweet_id;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			curl_close($ch);

			// Decode the response
			return @json_decode($response, TRUE);
		}
	}