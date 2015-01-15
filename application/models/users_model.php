<?php 
	class Users_model extends CI_Model {
		public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
		public $client_id = 464891386855067;
		public $scope = 'basic_info,email,public_profile,user_about_me,user_activities,user_birthday,user_education_history,user_friends,user_interests,user_likes,user_location,user_photos,user_relationship_details';
		public $device_id = '513340ce4df0f6bb011feb8a10aece07780808414d8a95bce442712bba6896cd';

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
		}

		public function AuthToken($email, $password) {
			// Get the Facebook Token
			$token = $this->FacebookToken($email, $password);
			
			if($token != 'Error'
			&& $token != 'Failed'
			&& $token != 'Permissions') {
				$info = SendRequest('auth', NULL, TRUE, array('facebook_id' => NULL, 'facebook_token' => $token));
				$decode = @json_decode($info, TRUE);

				/*
				echo '<div style="color: #090127;text-shadow:none;text-align:left;">';
				FormatArray($decode);
				echo '</div>';
				die;
				*/

				// Get all of the info about the user who just logged in
				$user = $decode['user'];
				$tinder_id = $user['_id'];
				$token = $user['api_token'];
				$name = $user['full_name'];
				$age_max = $user['age_filter_max'];
				$age_min = $user['age_filter_min'];
				$bio = $user['bio'];
				$dob = date('M j, Y', strtotime($user['birth_date']));
				$age = date_diff(date_create(), date_create($dob))->format('%y');
				$distance = $user['distance_filter'];
				$gender = $user['gender'];
				$interested_in = $user['gender_filter'];
				$last_activity = $user['ping_time'];

				// Seperate the first name from the last
				$exp = explode(' ', $name);
				$exp_num = count($exp);
				$first_name = $exp[0];
				$last_name = $exp[$exp_num-1];

				// Get the photos
				$photos = $user['photos'];

				for($i=0;$i<count($photos);$i++) {
					$large = $photos[$i]['processedFiles'][0]['url'];
					$big = $photos[$i]['processedFiles'][1]['url'];
					$med = $photos[$i]['processedFiles'][2]['url'];
					$tiny = $photos[$i]['processedFiles'][3]['url'];

					$pics[$i] = array('big' => StripPic($big),
									'large' => StripPic($large),
									'medium' => StripPic($med),
									'tiny' => StripPic($tiny),
									'fb_id' => $photos[$i]['fbId']); 
				}

				// Download and copy the user's pics on to the server
				$this->database_model->InsertPics($tinder_id, $pics);

				// Get the pics
				$tiny_pic = StripPic($pics[0]['tiny']);
				$med_pic = StripPic($pics[0]['medium']);
				$large_pic = StripPic($pics[0]['large']);

				$users = array('tinder_id' => $tinder_id,
							'token' => $token,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'age' => $age,
							'dob' => $dob,
							'bio' => trim($bio),
							'gender' => $gender,
							'last_activity_date' => $last_activity,
							'profile_pic_tiny' => $tiny_pic,
							'profile_pic_medium' => $med_pic,
							'profile_pic_large' => $large_pic);

				// Define the settings array for the query on the settings table
				$settings = array('age_min' => $age_min,
								'age_max' => $age_max,
								'distance_filter' => $distance,
								'interested_in' => $interested_in);

				// Get the user's pings
				$pings = $this->database_model->GetPings($tinder_id);

				// Merge the settings and users arrays
				$data = array_merge($users, $settings);

				// Query the DB to see if there is a record of this user existing
				$this->db->select('id, username');
				$this->db->where('tinder_id', $tinder_id);
				$query = $this->db->get('users');
				$count = $query->num_rows();

				if($count == 0) {
					$username = NULL;

					// If there isn't, then insert a row into the users table
					$this->db->insert('users', $users);
					$user_id = $this->db->insert_id();

					// Insert a row into the settings table
					$settings['tinder_id'] = $tinder_id;
					$this->db->insert('settings', $settings);
				} else {
					// Get the user's ID
					$row = $query->row_array();
					$user_id = $row['id'];
					$username = $row['username'];

					// Update the users table with the most recent info
					$this->db->where('tinder_id', $tinder_id);
					$this->db->update('users', $users);

					// Update the settings table with the most recent info
					$this->db->where('tinder_id', $tinder_id);
					$this->db->update('settings', $settings);
				}

				// Define the user_id and username keys of the data array
				$data['user_id'] = $user_id;
				$data['username'] = $username;

				return $data;
			} else {
				return $token;
			}
		}

		public function ChangePicOrder($pics, $auth) {
			$info = SendRequest('media', $auth, 'PUT', array('change_order' => $pics));
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

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
			$post_data = http_build_query($data);  
              
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'https://www.facebook.com/login.php');
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    
			curl_setopt($ch, CURLOPT_POST, TRUE);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);     
			curl_setopt($ch, CURLOPT_REFERER, 'https://www.facebook.com/');  
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);  
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
			curl_exec($ch); 					

		    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
			curl_close($ch);
				  	
			return $http;   
		}

		// Grab the access token from the FB API
		public function FacebookToken($email, $password) {
			$login = $this->FacebookLogin($email, $password);

			if($login == 200) {
				// Define the cookies file
				$cookies = CookieFile($email);
			    $uri = 'https://www.facebook.com/connect/login_success.html';
				$url = 'https://www.facebook.com/dialog/oauth?client_id='.$this->client_id.'&redirect_uri='.urlencode($uri).'&scope='.$this->scope.'&response_type=token';
						
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
					$break = explode("access_token=", $matches[1]);
					// FormatArray($break);

					if(count($break) == 2) {
						// Split the URL once more to get the access token value
						$exp = explode("&", $break[1]);
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

		public function GetMatches($tinder_id, $auth) {
			// Get the user's matches from the DB
			$matches = $this->database_model->GetMatches($tinder_id);

			for($i=0;$i<$matches['count'];$i++) {
				// Get the match ID for each match
				$match_obj = $matches['likes'][$i];
				$match_id = $match_obj['match_id'];
				$match_info = $this->GetMatchInfo($match_id, $auth);
				// FormatArray($match_info);
			}
		}

		public function GetUpdates($auth) {
			$real = date('H:i', strtotime('-5 minutes'));
			$time = date('y').'-'.date('m').'-'.date('d').'T'.$real.':'.date('s').'.906Z';
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
			$info = SendRequest('user/devices/ios/'.$this->device_id, $auth, 'DELETE', FALSE);
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
			$results = $users['results'];

			$users = array();

			for($i=0;$i<count($results);$i++) {
				$photos = $results[$i]['photos'];

				for($x=0;$x<count($photos);$x++) {
					$large = $photos[$x]['processedFiles'][0]['url'];
					$big = $photos[$x]['processedFiles'][1]['url'];
					$med = $photos[$x]['processedFiles'][2]['url'];
					$tiny = $photos[$x]['processedFiles'][3]['url'];

					$pics[$x] = array('big' => $big,
									'large' => $large,
									'medium' => $med,
									'tiny' => $tiny,
									'fb_id' => NULL); 
				}

				// Find out each user's age
				$date_birth = date_create($results[$i]['birth_date']);
				$age = date_diff(date_create(), $date_birth)->format('%y');

				$users[$i] = array('distance' => $results[$i]['distance_mi'],
								'common_like_count' => $results[$i]['common_like_count'],
								'common_likes' => $results[$i]['common_likes'],
								'common_friend_count' => $results[$i]['common_friend_count'],
								'common_friends' => $results[$i]['common_friends'],
								'tinder_id' => $results[$i]['_id'],
								'bio' => $results[$i]['bio'],
								'gender' => $results[$i]['gender'],
								'age' => $age,
								'birth_date' => date('M j, Y', strtotime($results[$i]['birth_date'])),
								'name' => $results[$i]['name'],
								'ping_time' => date('M j @ g:i A', strtotime($results[$i]['ping_time'])),
								'time_format' => FormatTime($results[$i]['ping_time']),
								'pics' => $pics);

				unset($pics);
			}

			return $users;
		}

		public function ProfileInfo($auth) {
			$info = SendRequest('profile', $auth, FALSE, FALSE);
			$decode = @json_decode($info, TRUE);
			return $decode;
		}

		public function SendMessage($id, $msg, $auth) {
			// Add the signature to each message
			$signed_msg = $msg."\r\n \r\n Sent from WeTinder - Tinder for Web";
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
			$info = SendRequest('user/matches/'.$match_id.'/', $auth, 'DELETE', FALSE);
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