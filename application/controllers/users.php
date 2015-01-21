<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Users extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				// Get the base URL
				$this->base_url = $this->config->base_url();

				// Load the session library
				$this->load->library('session');

				// Load all of the models
				$this->load->model('users_model');
			}

			public function Index() {
				// Find out if the user is logged in or not
				$user_id = $this->session->userdata('user_id');

				// Get the ID from the URL
				$id = $this->uri->segment(2, NULL);

				// Get the info about the user
				$user_info = $this->database_model->GetUserInfo($id);
				//FormatArray($user_info);
				//die;

				// If the user actually exists in the DB
				if($user_info !== FALSE) {
					//FormatArray($user_info);
					//die;

					// If the client is logged in, then get their stats
					if(is_numeric($user_id)) {
						$session = TRUE;
						$auth = $this->session->userdata('token');
						$tinder_id = $this->session->userdata('tinder_id');
						$lon = $this->session->userdata('lon');
						$lat = $this->session->userdata('lat');

						// Get the stats of the user who is logged in
						$stats = $this->database_model->GetThreeStats($tinder_id);
						$like_count = $stats['like_count'];
						$match_count = $stats['match_count'];
						$pass_count = $stats['pass_count'];

						// Format the user's profile link
						$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));


						/* UPDATE THE USER'S PROFILE */
						// Make a request to Tinder to get the most recent info about this user
						$live_info = $this->users_model->UserLookup($user_info['tinder_id'], $auth);

						// If the user actually exists, then get their info and update the profile
						if($live_info !== FALSE) {
							//FormatArray($live_info);
							//die;

							// Update the users table with the most recent info about this user
							$data = array('first_name' => $live_info['name'],
										'bio' => $live_info['bio'],
										'dob' => $live_info['dob'],
										'age' => $live_info['age'],
										'gender' => $live_info['gender'],
										'first_name' => $live_info['name'],
										'last_activity_date' => $live_info['last_activity_date']);
							$this->database_model->UpdateUser($live_info['tinder_id'], $data);

							// Update the user's last seen position
							$last_seen = $this->database_model->EditLastSeen($live_info['distance'], $tinder_id, $live_info['tinder_id'], $lon, $lat);
							
							// Add these elements to the array
							$user_info['name'] = $live_info['name'];
							$user_info['bio'] = $live_info['bio'];
							$user_info['distance'] = $live_info['distance'];
							$user_info['age'] = $live_info['age'];
							$user_info['gender'] = $live_info['gender'];
							$user_info['gender_format'] = $live_info['gender_format'];
							$user_info['last_activity_date'] = $live_info['last_activity_format'];
							$user_info['profile_pic'] = $live_info['profile_pic'];
							$user_info['fb_like_count'] = $live_info['fb_like_count'];
							$user_info['fb_likes'] = $live_info['fb_likes'];
							$user_info['fb_friend_count'] = $live_info['fb_friend_count'];
							$user_info['fb_friends'] = $live_info['fb_friends'];

							//FormatArray($user_info);
							//die;
						}
					} else {
						$session = FALSE;
						$auth = NULL;
						$tinder_id = NULL;
						$like_count = NULL;
						$match_count = NULL;
						$pass_count = NULL;
						$profile_link = NULL;

						$last_seen = $this->database_model->GetLastSeen($user_info['tinder_id']);
					}

					//FormatArray($last_seen);

					// Get the text for the popup window on the google maps marker
					$last = FormatLastSeenText($last_seen, $this->base_url);
					//echo $last;
					//die;

					// Define the meta tags
					$meta_info = array('description' => MetaSubject($user_info['username'], $user_info['name']).' on WeTinder',
									'img' => 'http://images.gotinder.com/'.$user_info['tinder_id'].'/'.$user_info['profile_pic'],
									'url' => $this->base_url.$user_info['link']);

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => $user_info['name'],
										'session' => $session,
										'header' => $user_info['name'],
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'first_name' => $this->session->userdata('first_name'),
										'last_name' => $this->session->userdata('last_name'),
										'like_count' => $like_count,
										'match_count' => $match_count,
										'pass_count' => $pass_count,
										'meta' => $meta_info,
										'profile_link' => $profile_link);

					// Determine whether or not the client is able to like the given user
					if($user_info['tinder_id'] != $this->session->userdata('tinder_id')
					&& $session == TRUE) {
						// See if there is already a liking between these two users
						$me_like = $this->database_model->SeeIfLiked($tinder_id, $user_info['tinder_id'], FALSE);

						if($me_like == 0) {
							$like = TRUE;
						} else {
							$like = 'liked';
						}

						// Get the user's mutual likes and friends on Facebook

					} else {
						$like = FALSE;
					}

					// Determine whether or not the client is able to edit the bio for the profile
					if($user_info['tinder_id'] == $this->session->userdata('tinder_id')) {
						$edit = TRUE;
					} else {
						$edit = FALSE;
					}

					// Get all of the stats of the user who is logged in
					$stats = $this->database_model->GetThreeStats($user_info['tinder_id']);

					// Set all of the info that needs to be passed to the body view
					$body_info = array('user_info' => $user_info,
										'session' => $session,
										'like' => $like,
										'edit' => $edit,
										'lat' => $lat,
										'lon' => $lon,
										'distance' => $last_seen['data']['miles_away'],
										'last_seen' => $last,
										'like_count' => $stats['like_count'],
										'match_count' => $stats['match_count'],
										'pass_count' => $this->database_model->GetPassCount($id, FALSE));

					//FormatArray($body_info);
					// Load all of the views
					$this->load->view('header', $header_info); 
					$this->load->view('profile', $body_info); 
					$this->load->view('footer'); 
				} else {
					header('Location: '.$this->base_url);
				}
			} 

			public function Discover() {
				// Find out if the user is logged in or not
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get all of the stats of the user who is logged in
					$stats = $this->database_model->GetThreeStats($tinder_id);
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];
					$pass_count = $stats['pass_count'];

					// Save the user's link to their profile
					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					$meta_info = array('description' => 'Discovery on WeTinder',
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url.'users/Discover');

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Play',
										'session' => TRUE,
										'header' => '',
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'like_count' => $like_count,
										'match_count' => $match_count,
										'pass_count' => $pass_count,
										'first_name' => $this->session->userdata('first_name'),
										'last_name' => $this->session->userdata('last_name'),
										'meta' => $meta_info,
										'profile_link' => $profile_link);

					// Set all of the info that needs to be passed to the dashboard view
					$body_info = array('pic' => $tinder_id.'/'.$this->session->userdata('profile_pic_medium'));

					// Load all of the views
					$this->load->view('header', $header_info); 
					$this->load->view('find_users', $body_info); 
					$this->load->view('footer'); 
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function DiscoverLoad() {
				// Save the user's session ID as a variable
				$my_id = $this->session->userdata('user_id');
				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				// Get all of the parameters from the URL
				$params = $this->input->get();
						
				foreach($params as $key => $value) {
					$$key = $value;
				}

				// If the user is requesting a new batch, then ping the location in request a fresh batch of users
				if($type == 'new') {
					// Set the session data for the latitude and longitude
					$this->session->set_userdata(array('lon' => $lon, 'lat' => $lat));

					// Ping the user's current location
					$info = $this->users_model->PingUser($lon, $lat, $auth);

					// Insert the user's ping into the DB
					if($info['status'] == 200
					&& !array_key_exists('error', $info)) {
						$this->database_model->InsertPing($lon, $lat, $tinder_id);
					}

					// Get the current batch of users
					$info = $this->users_model->PresentUsers($auth); 

					// If there isn't a recs timeout
					if($info !== FALSE) {
						// Remove all of the batches from the previous load
						$this->database_model->RemoveAllBatch($my_id);

						// Insert the user batch into the DB
						$this->database_model->InsertBatch($my_id, $tinder_id, $info, $lon, $lat);

						$new = TRUE;
					} else {
						$new = FALSE;
					}
				} else {
					$new = TRUE;
				}


				// If there wasn't an error, then present him/her with their most recent info from Tinder
				if($new) {
					// Get the most recent batch user
					$next = $this->database_model->GetBatchUser($my_id);

					// Lookup the user to see if there's any mutual likes or friends
					$lookup = $this->users_model->UserLookup($next, $auth);

					$this->load->view('find_users_two', $lookup); 
				} else {
					$this->load->view('errors/timeout'); 
				}
			}

			public function GetConnections() {
				// Get the page and type from the URL
				$page = $this->input->get('page');
				$type = $this->input->get('type');
				$inverse = $this->input->get('inverse');
				$tinder_id = $this->input->get('id');
				$q = $this->input->get('q');

				// Save the auth token as a variable
				$auth = $this->session->userdata('token');

				$per_page = 5;
				$limit = $page*$per_page;

				switch($type) {
					case'likes';

						if($inverse == 'true') {
							$inverse = TRUE;
						} else {
							$inverse = FALSE;
						}

						$results = $this->database_model->GetLikes($tinder_id, $inverse, $limit, $per_page, $q);
						$count = $this->database_model->GetLikeCount($tinder_id, FALSE);
						break;

					case'matches';

						$results = $this->database_model->GetMatches($tinder_id, $inverse, $limit, $per_page, $q);
						$count = $results['count'];
						$results = $results['users'];
						break;

					case'passes';

						if($inverse == 'true') {
							$inverse = TRUE;
						} else {
							$inverse = FALSE;
						}

						$results = $this->database_model->GetPasses($tinder_id, $inverse, $limit, $per_page, $q);
						$count = $this->database_model->GetPassCount($tinder_id, FALSE);
						break;
				}

				// FormatArray($results);

				$body_info = array('type' => $type,
									'page' => $page,
									'count' => $count,
									'connections' => $results,
									'id' => $tinder_id);

				// Load the view
				$this->load->view('backend/connections', $body_info);
			}

			public function GetMatchInfo() {
				// Save the auth token as a variable
				$auth = $this->session->userdata('token');

				// Get the match ID from the URL
				$id = $this->input->get('match_id');

				// Get the match info
				$match = $this->users_model->GetMatchInfo($id, $auth);
				$user_id = $match['results']['participants'][1];

				$data = array('name' => $match['results']['person']['name'],
							'pic' => ReturnProfilePic($match['results']['person']['photos']),
							'id' => $match['results']['person']['_id']);
				echo json_encode($data);
			}

			public function GetUpdates() {
				// Call the GetUpdates function in the users model 
				$auth = $this->session->userdata('token');
				$updates = $this->users_model->GetUpdates($auth, 'now');
				echo json_encode($updates);
			}

			public function LikeUser() {
				// Save the user's session ID as a variable
				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				// Get the user ID from the URL
				$id = $this->input->get('id');

				// Call the LikeUser function in the users model 
				$like = $this->users_model->LikeUser($id, $auth);
				
				// Get the match ID
				if($like['match'] > 1) {
					$match_id = $like['match']['_id'];
				} else {
					$match_id = NULL;
				}

				// Remove the batch user from the DB and then insert him/her into the likes table
				$this->database_model->RemoveBatchUser($id, $this->session->userdata('user_id'));
				$this->database_model->InsertIntoLikes($tinder_id, $id, $match_id);

				// Echo out the match ID
				echo $match_id;
			}

			public function Logout() {
				// Log the user out of Tinder
				$logout = $this->users_model->Logout($this->session->userdata('token'));

				// Destroy the session
				$this->session->sess_destroy();

				// Redirect the user to the home page
				header('Location: '.$this->base_url);
			}

			public function PassUser() {
				// Save the user's session ID as a variable
				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				// Get the user ID from the URL
				$id = $this->input->get('id');

				// Call the PassUser function in the users model 
				$pass = $this->users_model->PassUser($id, $auth);

				// Remove the batch user from the DB and then insert him/her into the passes table
				$this->database_model->RemoveBatchUser($id, $this->session->userdata('user_id'));
				$this->database_model->InsertIntoPasses($tinder_id, $id);
			}

			public function ReportUser($tinder_id) {
				$report = $this->users_model->ReportUser($tinder, $this->session->userdata('token'));
				return $report;
			}

			public function SendMessage() {
				// Save the tinder ID and API token as variables
				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				// Get the match ID and the message from the URL
				$id = $this->input->post('id');
				$msg = $this->input->post('msg');

				// Call the SendMessage function in the users model 
				$message = $this->users_model->SendMessage($id, $msg, $auth);
				
				// Insert the message into the DB
				$this->database_model->InsertMessage($id, $msg, $tinder_id);
				FormatArray($message);
			}

			public function SendSMS() {

			}

			public function UpdateProfile() {
				// Update the pic order
				// $this->users_model->ChangePicOrder($pics, $auth);

				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				// Update the bio and/or gender
				$bio = $this->input->post('bio');
				$gender = $this->session->userdata('gender');
				$update = $this->users_model->UpdateProfile($auth, $bio, $gender);

				// Update the user's row in the DB
				$this->database_model->UpdateUser($tinder_id, array('bio' => $bio, 'gender' => $gender));
				FormatArray($update);
			}

			public function ValidateSMS() {

			}
		}
	}