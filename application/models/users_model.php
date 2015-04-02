<?php 
	class Users_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Set the base URL
			$this->base_url = $this->config->base_url();

			// Load the DB and the helper
			$this->load->database();
			$this->load->helper('common_helper');

			// Load the models
			$this->load->model('database_model', 'database');
			$this->load->model('location_model', 'loc');
			$this->load->model('facebook_model', 'fb');
		}

		/**
		 * Make a request to Tinder's API to get a token that will be used to send each request
		 * @param {string} [email] The email of the person trying to log in
		 * @param {string} [password] The password of the person trying to log in
		 * @return {array|booean} An array from Tinder's API with an API token or FALSE
		 */
		public function AuthToken($email, $password) {
			// Get the Facebook token
			$token = $this->fb->FacebookToken($email, $password);
			// var_dump($token);

			if($token != 'Error' && $token != 'Failed' && $token != 'Permissions') {
				// Send a request to Tinder's auth endpoint to get a new token
				$info = SendRequest('auth', NULL, TRUE, array('facebook_id' => NULL, 'facebook_token' => $token));
				$decode = @json_decode($info, TRUE);

				if(is_array($decode)) {
					return (array_key_exists('user', $decode) ? $decode['user'] : FALSE);
				} else {
					return "Tinder couldn't authenticate";
				}
			} else {
				return FALSE;
			}
		}

		/**
		 * Check to see if a given user has permission to edit a profile
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the user whose page is being viewed
		 * @return {boolean}
		 */
		public function CanEdit($my_id, $his_id) {
			return ($my_id == $his_id ? TRUE : FALSE);
		}

		/**
		 * See if a given user is able to like another user. 
		 * Depends on whether or they have already liked that user before
		 * @param {string} [my_id] The Tinder ID of the user who is currently logged in
		 * @param {string} [his_id] The Tinder ID of the user who is trying to be liked
		 * @return {boolean|string}
		 */
		public function CanLike($my_id, $his_id) {
			if($my_id != $his_id) {
				// See if there is already a liking between these two users
				$me_like = $this->database->SeeIfLiked($my_id, $his_id, FALSE);

				if($me_like['count'] == 0) {
					$like = array('perm' => 'can_like', 'match_id' => NULL);
				} else {
					if(empty($me_like['match_id']) || $me_like['match_id'] == 'false') {
						$like = array('perm' => 'liked', 'match_id' => NULL);
					} else {
						if($me_like['unmatched']) {
							$like = array('perm' => 'unmatched', 'match_id' => $me_like['match_id']);
						} else {
							$like = array('perm' => 'matched', 'match_id' => $me_like['match_id']);
						}
					}
				}
			} else {
				$like = array('perm' => FALSE, 'match_id' => FALSE);
			}

			return $like;
		}

		/**
		 * Change the order of a user's pics
		 * @param {array} [pics] An array of pictures in the order that the user has selected
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function ChangePicOrder($pics, $auth) {
			$info = SendRequest('media', $auth, 'PUT', array('change_order' => $pics));
			return @json_decode($info, TRUE);
		}

		/**
		 * Delete a user's Tinder account
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function DeleteAccount($auth) {
			$info = SendRequest('profile', $auth, 'DELETE', FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Search for new users to like
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function FindUsers($auth) {
			$info = SendRequest('user/recs?locale=en', $auth, FALSE, FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Get information about a given match by making a request to Tinder's API.
		 * @param {string} [match_id] The ID of the match
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function GetMatchInfo($match_id, $auth) {
			$info = SendRequest('user/matches/'.$match_id, $auth, FALSE, FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Get updates since a given point in time. This includes new matches and messages received
		 * @param {string} [auth] The API token
		 * @param {time} [time] The time to get updates since
		 * @return {array} An array from Tinder's API
		 */
		public function GetUpdates($auth, $time = NULL) {
			$info = SendRequest('updates', $auth, TRUE, array('last_activity_date' => RequestTime($time)));
			return @json_decode($info, TRUE);
		}

		/**
		 * Like another Tinder user
		 * @param {string} [tinder_id] The Tinder ID of the user who is being liked
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function LikeUser($tinder_id, $auth) {
			$info = SendRequest('like/'.$tinder_id, $auth, FALSE, FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Log a user out of Tinder
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function Logout($auth) {
			$device_id = '513340ce4df0f6bb011feb8a10aece07780808414d8a95bce442712bba6896cd';
			$info = SendRequest('user/devices/ios/'.$device_id, $auth, 'DELETE', FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Search for users on Tinder by specific lat & lon coordinates
		 * @param {string} [auth] The auth token of the user who is logged in
		 * @param {decimal} [lon] The longitude coordinate
		 * @param {decimal} [lat] The latitude coordinate
		 * @return {array} An array returned from Tinder's API
		 */
		public function Passport($auth, $lon, $lat) {
			$info = SendRequest('passport/user/travel', $auth, TRUE, array('lon' => $lon, 'lat' => $lat));
			return @json_decode($info, TRUE);
		}

		/**
		 * Pass another Tinder user
		 * @param {string} [tinder_id] The Tinder ID of the user who is being passed
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function PassUser($tinder_id, $auth) {
			$info = SendRequest('pass/'.$tinder_id, $auth, FALSE, FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Ping a user to a given location
		 * @param {decimal} [lon] The longitude coordinate
		 * @param {decimal} [lat] The latitude coordinate
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function PingUser($lon, $lat, $auth) {
			$info = SendRequest('user/ping', $auth, TRUE, array('lon' => $lon, 'lat' => $lat));
			return @json_decode($info, TRUE);
		}

		/**
		 * Find a fresh batch of users to like/pass and return array containing only their relevant information
		 * @param {string} [auth] The API token
		 * @return {array} An containing all of the relevant info about each user
		 */
		public function PresentUsers($auth) {
			// Get a new batch of users
			$users = $this->FindUsers($auth);
			
			if(array_key_exists('message', $users)) {
				if(trim($users['message']) == 'recs timeout') {
					return FALSE;
				} 
			} else {
				$results = $users['results'];
				$users = [];

				for($i=0;$i<count($results);$i++){
					$users[$i] = array('tinder_id' => $results[$i]['_id'],
									'name' => $results[$i]['name'],
									'bio' => BioLinks($results[$i]['bio']),
									'gender' => $results[$i]['gender'],
									'birth_date' => $results[$i]['birth_date'],
									'age' => ReturnAge($results[$i]['birth_date']),
									'distance' => $results[$i]['distance_mi'],
									'ping_time' => date('M j @ g:i A', strtotime($results[$i]['ping_time'])),
									'time_format' => FormatTime($results[$i]['ping_time']),
									'profile_pic' => ReturnProfilePic($results[$i]['photos']),
									'pics' => ReturnPicsArray($results[$i]['photos']));
				}

				return $users;
			}
		}

		/**
		 * Make a request to the profile API endpoint.
		 * @param {string} [auth] The API token
		 * @return {array} An array from Tinder's API
		 */
		public function ProfileInfo($auth) {
			$info = SendRequest('profile', $auth, FALSE, FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Repoart a user on Tinder. A message can be optional
		 * @param {string} [tinder_id] The Tinder ID of the user who is being reported
		 * @param {string} [auth] The API token
		 * @param {int} [cause] The reason. Values include 1, 2 or 3
		 * @param {string} [text] The message that goes along with the report
		 * @return {array} An array from Tinder's API
		 */
		public function ReportUser($tinder_id, $auth, $cause, $text = NULL) {
			$data = array('cause' => (int)$cause);
			$data['text'] = ($text ?: $text);
			$info = SendRequest('report/user/'.$tinder_id, $auth, TRUE, $data);
			return @json_decode($info, TRUE);
		}

		/**
		 * Send a message to another user. Add the signature to each message
		 * @param {string} [id] The Tinder ID of the user who is meant to receive the message
		 * @param {string} [msg] The content of the message
		 * @param {string} [auth] The API token of the user who is currently logged in
		 * @return {array} An array from Tinder's API
		 */
		public function SendMessage($id, $msg, $auth) {
			// $sig = "Twinder.io - Twitter meets Tinder";
			$sig = "";
			$info = SendRequest('user/matches/'.$id, $auth, TRUE, array('message' => $msg."\r\n \r\n".$sig));
			return @json_decode($info, TRUE);
		}

		/**
		 * Sync the content of a user's Tinder account with their WeTinder account
		 * @param {string} [email] The email of the person trying to log in
		 * @param {string} [password] The password of the person trying to log in
		 * @return {array|boolean} An array containing a user's personal info and settings
		 */
		public function SyncAccount($email, $password) {
			// Get the Tinder API token
			$auth = $this->AuthToken($email, $password);

			if($auth && $auth != "Tinder couldn't authenticate") {
				// Seperate the first name from the last
				$names = FormatNames($auth['full_name']);

				// Get the user's latitude and longitude coordinates
				$profile = $this->ProfileInfo($auth['api_token']);
				$miles = $profile['distance_filter'];
				$lon = $profile['pos']['lon'];
				$lat = $profile['pos']['lat'];

				// Define all of the user's info in an array
				$user = array('tinder_id' => $auth['_id'],
							'fb_id' => $profile['facebook_id'],
							'token' => $auth['api_token'],
							'first_name' => $names['first_name'],
							'last_name' => $names['last_name'],
							'age' => ReturnAge($auth['birth_date']),
							'dob' => date('M j, Y', strtotime($auth['birth_date'])),
							'bio' => $profile['bio'],
							'gender' => $auth['gender'],
							'profile_pic' => ReturnProfilePic($auth['photos']));

				// Define the settings array for the query on the settings table
				$settings = array('age_min' => $profile['age_filter_min'],
								'age_max' => $profile['age_filter_max'],
								'distance_filter' => $miles,
								'interested_in' => $profile['gender_filter']);

				// Insert/update this user into the DB and get their info returned from the query
				$info = $this->database->InsertUser($user, $settings);
				$user['user_id'] = $info['user_id'];
				$user['username'] = $info['username'];

				// Insert a record in the DB for their last seen location
				$this->database->EditLastSeen($auth['_id'], $auth['_id'], 0, $lon, $lat); 

				// Insert the user's pics
				$this->database->InsertPics($auth['_id'], ReturnPicsArray($auth['photos']));

				// Get the name of the city and state based upon the user's longitude and latitude coordinates
				$loc = $this->loc->MapquestLatLon($lat, $lon);
				$city = $loc['city'];
				$state = $loc['state'];

				// Get all of the user's matches since they have joined
				$updates = $this->GetUpdates($auth['api_token'], $profile['create_date']);

				// Sync all of the messages from the user's Tinder account
				$this->database->SyncMessages($updates['matches'], $auth['_id'], $miles, $lon, $lat, $city, $state);

				// Update all of the blocks
				$this->database->UpdateBlocks($auth['_id'], $updates['blocks']);
			
				// Merge the settings and users arrays
				return array_merge($user, $settings);
			} else {
				return FALSE;
			}
		}

		/**
		 * Update a user's bio and/or gender
		 * @param {string} [auth] The API token of the user who is currently logged in
		 * @param {string} [bio] The bio submitted by the user
		 * @param {int} [gender] The gender submitted by the user
		 * @return {array} An array from Tinder's API
		 */
		public function UpdateProfile($auth, $bio, $gender) {
			$info = SendRequest('profile', $auth, TRUE, array('bio' => $bio, 'gender' => (int)$gender));
			return @json_decode($info, TRUE);
		}

		/**
		 * Update a user's discovery settings
		 * @param {string} [auth] The API token of the user who is logged in
		 * @param {int} [distance] The distance filter value
		 * @param {int} [max] The maximum age
		 * @param {int} [min] The minimum age
		 * @param {int} [interested_in] The interested value. 0 for men. 1 for women. -1 for both
		 * @param {int} [gender] The gender value. 0 for men. 1 for women
		 * @return {array} An array from Tinder's API
		 */
		public function UpdateSettings($auth, $distance, $max, $min, $interested_in, $gender) {
			$data = array('distance_filter' => (int)$distance,
						'age_filter_max' => $max,
						'age_filter_min' => $min,
						'gender_filter' => (int)$interested_in,
						'gender' => (int)$gender);
			$info = SendRequest('profile', $auth, TRUE, $data);
			return @json_decode($info, TRUE);
		}

		/**
		 * Unmatch a user on Tinder
		 * @param {string} [match_id] The match ID on Tinder
		 * @param {string} [auth] The API token of the user who is logged in
		 * @return {array} An array from Tinder's API
		 */
		public function UnmatchUser($match_id, $auth) {
			$info = SendRequest('user/matches/'.$match_id.'/', $auth, "DELETE", FALSE);
			return @json_decode($info, TRUE);
		}

		/**
		 * Lookup a Tinder user's information
		 * @param {string} [tinder_id] The Tinder ID of the user targetted
		 * @param {string} [auth] The API token
		 * @return {array|boolean} An array from Tinder's API
		 */
		public function UserLookup($tinder_id, $auth) {
			$info = SendRequest('user/'.$tinder_id, $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			
			if($decode['status'] == 200) {
				$user = $decode['results'];
				return array('tinder_id' => $user['_id'],
							'distance' => $user['distance_mi'],
							'name' => $user['name'],
							'dob' => date('M j, Y', strtotime($user['birth_date'])),
							'bio' => BioLinks(BioDefault($user['bio'], $user['name'])),
							'gender' => $user['gender'],
							'gender_format' => FormatGender($user['gender']),
							'age' => ReturnAge($user['birth_date']),
							'last_activity_date' => $user['ping_time'],
							'profile_pic' => ReturnProfilePic($user['photos']),
							'pics' => ReturnPicsArray($user['photos'])); 
			} else {
				return FALSE;
			}
		}

		/**
		 * Validate all of the parameters from the Hot page. 
		 * Make sure all of the parameters values are of accepted values
		 * @param {array} [params] An array containing all of the URL parameters
		 * @return {array} An array containing all of the validated params
		 */
		public function ValidateParams($params) {
			// Set all of the param variables to their default values
			$gender = 'both';
			$city = array('name' => '', 'lon' => '', 'lat' => '');
			$state = array('name' => '', 'abbrev' => '', 'lon' => '', 'lat' => '');
			$distance = 50;
			$min = 18;
			$max = 50;
			$page = 0;

			// Loop thru each URI segment
			foreach($params as $key => $val) {
				switch($key) {
					// Set the default gender
					case'gender':
				
						if($val == 'men' || $val == 'women') {
							$gender = $val;
						} 
						break;

					// Set the default city
					case'city':

						// The default city is NULL
						if(!empty($val)) {
							// If the state is set, then query the DB to see if the city in the given state exists
							if(isset($params['state'])) {
								$check = $this->loc->CheckCityAndState(urldecode($val), urldecode($params['state']));

								// If the place exists, then decode it
								if($check) {
									$city['name'] = urldecode($val);

									// Get the lat & lon coordinates
									$coords = $this->loc->MapquestLocation($city['name'], urldecode($params['state']));
									$city['lon'] = $coords['lng'];
									$city['lat'] = $coords['lat'];
								}
							}
						}
						break;

					// Set the default state
					case'state':

						// The default state is NULL
						if(!empty($val)) {
							// Check to see if the state exists
							$check = $this->loc->CheckState(urldecode($val));

							// If the state exists, then decode it
							if($check) {
								// If the state is the full name, get its abbreviation
								if(strlen(urldecode($val)) != 2) {
									$state['abbrev'] = $this->loc->ConvertState(urldecode($val));
									$state['name'] = ucwords(strtolower(urldecode($val)));
								} else {
									// If not, get its full name
									$state['abbrev'] = strtolower(urldecode($val));
									$state['name'] = $this->loc->FullFromAbbrev(urldecode($val));
								}
								
								// Get the place's lat & lon coordinates
								$coords = $this->loc->MapquestLocation(NULL, $state['abbrev']);
								$state['lon'] = $coords['lng'];
								$state['lat'] = $coords['lat'];
							}
						}
						break;

					// Set the default distance
					case'distance':

						// If the distance is greater than 0 and less than 10,000 set it
						if($val > 0 && $val < 100) {
							$distance = $val;
						} 
						break;

					// Set the min & max ages
					case'min':
					case'max':

						// If the age is between 18 and 50, then set it
						if($val > 17 && $val < 51) {
							$$key = $val;
						} 
						break;

					case'page':

						if(is_numeric($val) && $val > 0) {
							$page = $val-1;
						} else {
							$page = 0;
						}
						break;
				}
			}
			// var_dump($gender);

			return array('gender' => $gender, 
						'city' => $city, 
						'state' => $state,  
						'distance' => $distance, 
						'min' => $min, 
						'max' => $max, 
						'page' => $page);
		}
	}