<?php 
	class Users_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Set the base URL
			$this->base_url = $this->config->base_url();

			// Load the database
			$this->load->database();

			// Load the helpers file
			$this->load->helper('common_helper');

			// Load the database model
			$this->load->model('database_model');

			// Load the facebook model
			$this->load->model('facebook_model');
		}

		public function AuthToken($email, $password) {
			// Get the Facebook token
			$token = $this->facebook_model->FacebookToken($email, $password);
			
			if($token != 'Error'
			&& $token != 'Failed'
			&& $token != 'Permissions') {
				// Send a request to Tinder's auth endpoint to get a new token
				$info = SendRequest('auth', NULL, TRUE, array('facebook_id' => NULL, 'facebook_token' => $token));
				$decode = @json_decode($info, TRUE);
				//FormatArray($decode, 'style');
				//die;

				// Get all of the info about the user who just logged in
				$user = $decode['user'];

				// Get all of the user's matches since they have joined
				$updates = $this->GetUpdates($user['api_token'], $user['create_date']);

				// Sync all of the messages from the user's Tinder account
				$this->database_model->SyncMessages($updates['matches'], $user['_id']);

				// Seperate the first name from the last
				$names = FormatNames($user['full_name']);

				// Get the photos
				$photos = $user['photos'];

				for($i=0;$i<count($photos);$i++) {
					$pics[$i] = array('big' => StripPic($photos[$i]['processedFiles'][0]['url']),
									'large' => StripPic($photos[$i]['processedFiles'][1]['url']),
									'medium' => StripPic($photos[$i]['processedFiles'][2]['url']),
									'tiny' => StripPic($photos[$i]['processedFiles'][3]['url']),
									'fb_id' => $photos[$i]['fbId']); 
				}

				// Download and copy the user's pics on to the server
				$this->database_model->InsertPics($user['_id'], $pics);

				// Define all of the user's info in an array
				$users = array('tinder_id' => $user['_id'],
							'token' => $user['api_token'],
							'first_name' => $names['first_name'],
							'last_name' => $names['last_name'],
							'age' => ReturnAge($user['birth_date']),
							'dob' => $user['birth_date'],
							'gender' => $user['gender'],
							'profile_pic_tiny' => StripPic($pics[0]['tiny']),
							'profile_pic_medium' => StripPic($pics[0]['medium']));

				// Define the settings array for the query on the settings table
				$settings = array('age_min' => $user['age_filter_min'],
								'age_max' => $user['age_filter_max'],
								'distance_filter' => $user['distance_filter'],
								'interested_in' => $user['gender_filter']);

				// Query the DB to see if there is a record of this user existing
				$this->db->select('id, username');
				$this->db->where('tinder_id', $user['_id']);
				$query = $this->db->get('users');
				$count = $query->num_rows();

				if($count == 0) {
					// If there isn't, then insert a row into the users table
					$this->db->insert('users', $users);
					$user_id = $this->db->insert_id();

					// Insert a row into the settings table
					$settings['tinder_id'] = $user['_id'];
					$this->db->insert('settings', $settings);

					$username = NULL;
				} else {
					// Get the user's ID
					$row = $query->row_array();
					$user_id = $row['id'];
					$username = $row['username'];

					// Update the users table with the most recent info
					$this->db->where('tinder_id', $user['_id']);
					$this->db->update('users', $users);

					// Update the settings table with the most recent info
					$this->db->where('tinder_id', $user['_id']);
					$this->db->update('settings', $settings);
				}

				// Define the user_id and username keys of the data array
				$users['user_id'] = $user_id;
				$users['username'] = $username;

				// Merge the settings and users arrays
				return array_merge($users, $settings);
			} else {
				return $token;
			}
		}

		public function ChangePicOrder($pics, $auth) {
			$info = SendRequest('media', $auth, 'PUT', array('change_order' => $pics));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function FindUsers($auth) {
			$info = SendRequest('user/recs', $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function GetMatchInfo($match_id, $auth) {
			$info = SendRequest('matches/'.$match_id, $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function GetLikes($auth) {
			$time = RequestTime();
			$info = SendRequest('updates', $auth, TRUE, array('last_activity_date' => $time));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function GetMoments($auth) {
			$time = RequestTime();
			$info = SendRequest('feed/moments', $auth, TRUE, array('last_activity_date' => $time, 'last_moment_id' => NULL));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function GetMyMoments($auth) {
			$time = RequestTime();
			$info = SendRequest('user/moments', $auth, TRUE, array('last_activity_date' => $time, 'last_moment_id' => NULL));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function GetMomentLikes($auth, $moment_id) {
			$info = SendRequest('moments/'.$moment_id.'/likes', $auth, FALSE, NULL);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function GetUpdates($auth, $time = NULL) {
			$time = RequestTime($time);
			//echo $time;
			$info = SendRequest('updates', $auth, TRUE, array('last_activity_date' => $time));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function LikeUser($tinder_id, $auth) {
			$info = SendRequest('like/'.$tinder_id, $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function Logout($auth) {
			$device_id = '513340ce4df0f6bb011feb8a10aece07780808414d8a95bce442712bba6896cd';
			$info = SendRequest('user/devices/ios/'.$device_id, $auth, 'DELETE', FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function PassUser($tinder_id, $auth) {
			$info = SendRequest('pass/'.$tinder_id, $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function PingUser($lon, $lat, $auth) {
			$info = SendRequest('user/ping', $auth, TRUE, array('lon' => $lon, 'lat' => $lat));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function PresentUsers($auth) {
			// Get a new batch of users
			$users = $this->FindUsers($auth);
			FormatArray($users);
			$results = $users['results'];

			$users = array();

			for($i=0;$i<count($results);$i++) {
				$photos = $results[$i]['photos'];

				$pics = array();

				for($x=0;$x<count($photos);$x++) {
					$pics[$x] = array('big' => $photos[$x]['processedFiles'][0]['url'],
									'large' => $photos[$x]['processedFiles'][1]['url'],
									'medium' => $photos[$x]['processedFiles'][2]['url'],
									'tiny' => $photos[$x]['processedFiles'][3]['url'],
									'fb_id' => NULL); 
				}

				//FormatArray($results[$i]);
				//die;
				$users[$i] = array('tinder_id' => $results[$i]['_id'],
								'name' => $results[$i]['name'],
								'bio' => BioLinks($results[$i]['bio']),
								'gender' => $results[$i]['gender'],
								'birth_date' => $results[$i]['birth_date'],
								'age' => ReturnAge($results[$i]['birth_date']),
								'distance' => $results[$i]['distance_mi'],
								'ping_time' => date('M j @ g:i A', strtotime($results[$i]['ping_time'])),
								'time_format' => FormatTime($results[$i]['ping_time']),
								'pics' => $pics);
			}

			return $users;
		}

		public function ProfileInfo($auth) {
			$info = SendRequest('profile', $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function ReportUser($tinder_id, $auth, $cause, $text = NULL) {
			$data = array('cause' => $cause);

			if($text !== NULL) {
				$data['text'] = $text;
			}

			$info = SendRequest('report/'.$tinder_id, $auth, TRUE, $data);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function SendMessage($id, $msg, $auth) {
			// Add the signature to each message
			$signed_msg = $msg."\r\n \r\n Sent from <a href='http://wetinder.com'>WeTinder</a> - Tinder for Web";
			$info = SendRequest('user/matches/'.$id, $auth, TRUE, array('message' => $signed_msg));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function SendSMS($number, $auth) {
			$info = SendRequest('send_token', $auth, TRUE, array('number' => '+1'.$number));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function UpdateProfile($auth, $bio, $gender) {
			$data = array('bio' => $bio,
						'gender' => (int)$gender);
			$info = SendRequest('profile', $auth, TRUE, $data);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function UpdateSettings($auth, $distance, $max, $min, $interested_in, $gender) {
			$data = array('distance_filter' => (int)$distance,
						'age_filter_max' => $max,
						'age_filter_min' => $min,
						'gender_filter' => (int)$interested_in,
						'gender' => (int)$gender);
			$info = SendRequest('profile', $auth, TRUE, $data);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function UnmatchUser($match_id) {
			$info = SendRequest('user/matches/'.$match_id.'/', $auth, "DELETE", FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function UploadPic($fb_pic_id, $auth) {
			$info = '{"transmit": "fb",
						"assets": [{
							"xdistance_percent": 0.75,
							"id": "'.$fb_pic_id.'",
							"xoffset_percent": 0.1253906,
							"yoffset_percent": 0,
							"ydistance_percent": 1,
							"main": false
						}]}';
			$info = SendRequest('media', $auth, TRUE, $info);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function UserLookup($tinder_id, $auth) {
			$info = SendRequest('user/'.$tinder_id, $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function ValidateSMS($auth, $code) {
			$info = SendRequest('validate', $auth, TRUE, array('token' => ''.$code.''));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}
	}