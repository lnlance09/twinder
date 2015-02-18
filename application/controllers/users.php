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

				// Load the URL helper
				$this->load->helper('url');
			}

			public function Index() {
				// Get the ID from the URL
				$id = $this->uri->segment(2, NULL);

				// Get the info about the user
				$user_info = $this->database_model->GetUserInfo($id);
				// FormatArray($user_info);
				// die;

				// If the user actually exists in the DB
				if($user_info !== FALSE) {
					//FormatArray($user_info);
					//die;

					// Set the session data
					$user_id = $this->session->userdata('user_id');

					// Find out if the user is logged in or not
					if(is_numeric($user_id)) {
						$session = TRUE;
					} else {
						$session = FALSE;
					}

					// If the client is logged in, then get their stats
					if($session) {
						// Get the stats of the user who is logged in
						$stats = $this->database_model->GetThreeStats($this->session->userdata('tinder_id'));
						$like_count = $stats['like_count'];
						$match_count = $stats['match_count'];
						$pass_count = $stats['pass_count'];

						// Format the user's profile link
						$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

						/* UPDATE THE USER'S PROFILE */
						// Make a request to Tinder to get the most recent info about this user
						$live_info = $this->users_model->UserLookup($user_info['tinder_id'], $this->auth);

						// If the user actually exists according to Tinder, then get their info and update the profile
						if($live_info !== FALSE) {
							// FormatArray($live_info);
							// die;

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
							$last_seen = $this->database_model->EditLastSeen($live_info['distance'], // The distance
																			$this->session->userdata('tinder_id'), // The Tinder ID of the user who is logged in
																			$live_info['tinder_id'], // The Tinder ID of the profile
																			$this->session->userdata('lon'), // The lon & lat coordinates of the user who is logged in
																			$this->session->userdata('lat'));

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
							// FormatArray($user_info);
							// die;

							// Check to see if this user is allowed to report this user
							$report = $this->database_model->CheckReport($this->session->userdata('tinder_id'), $user_info['tinder_id']);

							if($report == 0) {
								$report = TRUE;
							} else {
								$report = FALSE;
							}

							// Determine whether or not the client is able to like the given user
							$like = $this->users_model->CanLike($user_info['tinder_id'], $this->session->userdata('tinder_id'), $session);

							// Determine whether or not the client is able to edit the bio for the profile
							$edit = $this->users_model->CanEdit($user_info['tinder_id'], $this->session->userdata('tinder_id'));
						}
					} else {
						// If the user ins't logged in, then set all of the variables as NULL
						$like_count = NULL;
						$match_count = NULL;
						$pass_count = NULL;
						$profile_link = NULL;
						$report = FALSE;
						$like = FALSE;
						$edit = FALSE;

						// Look up the user's last seen location
						$last_seen = $this->database_model->GetLastSeen($user_info['tinder_id']);
						$last_data = array('user' => $user_info, $last_seen);
					}

					// var_dump($session);
					// echo '<br><br><br><br>';
					// FormatArray($last_seen);
					// die;

					// Get the text for the popup window on the google maps marker
					// FormatArray($last_seen);
					// die;

					$last = FormatLastSeenText($last_data, $this->base_url);
					// echo $last;
					// die;

					// Define the meta tags
					$meta_info = array('description' => MetaSubject($user_info['username'], $user_info['name']).' on WeTinder',
									'img' => 'http://images.gotinder.com/'.$user_info['tinder_id'].'/'.$user_info['profile_pic'],
									'url' => $this->base_url.$user_info['link']);

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => $user_info['name'],
										'session' => $session,
										'header' => $user_info['name'],
										'auth' => $this->session->userdata('token'),
										'tinder_id' => $this->session->userdata('tinder_id'),
										'name' => $this->session->userdata('first_name'),
										'like_count' => $like_count,
										'match_count' => $match_count,
										'pass_count' => $pass_count,
										'meta' => $meta_info,
										'profile_link' => $profile_link);
					// var_dump($edit);
					// die;

					// Get all of the stats of the user who is being viewed
					$stats = $this->database_model->GetThreeStats($user_info['tinder_id']);

					$user_stats = $this->database_model->GetUserStats($user_info['tinder_id'], '5495df819983685e07f138f2');
					// FormatArray($user_stats);
					// die;

					// Set all of the info that needs to be passed to the body view
					$body_info = array('user_info' => $user_info,
										'session' => $session,
										'report' => $report,
										'like' => $like,
										'edit' => $edit,
										'lat' => $last_seen['data']['lat'],
										'lon' => $last_seen['data']['lon'],
										'distance' => $last_seen['data']['miles_away'],
										'last_seen' => $last,
										'stats' => $user_stats,
										'like_count' => $stats['like_count'],
										'match_count' => $stats['match_count'],
										'pass_count' => $this->database_model->GetPassCount($id, FALSE));

					// FormatArray($body_info);
					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('profile', $body_info); 
					$this->load->view('templates/footer'); 
				} else {
					redirect('', 'location');
				}
			} 

			public function Discover() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Get all of the stats of the user who is logged in
					$stats = $this->database_model->GetThreeStats($this->session->userdata('tinder_id'));
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];
					$pass_count = $stats['pass_count'];

					// Save the user's link to their profile
					$profile_link = FormatUserLink($this->session->userdata('tinder_id'), $this->session->userdata('username'));

					$meta_info = array('description' => 'Discover on WeTinder',
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url.'users/Discover');

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Play',
										'session' => TRUE,
										'header' => '',
										'auth' => $this->session->userdata('token'),
										'tinder_id' => $this->session->userdata('tinder_id'),
										'like_count' => $like_count,
										'match_count' => $match_count,
										'pass_count' => $pass_count,
										'name' => $this->session->userdata('first_name'),
										'meta' => $meta_info,
										'profile_link' => $profile_link);

					// Set all of the info that needs to be passed to the dashboard view
					$body_info = array('pic' => $this->session->userdata('tinder_id').'/'.$this->session->userdata('profile_pic_medium'));

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('find_users', $body_info); 
					$this->load->view('templates/footer'); 
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function DiscoverLoad() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
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
						$info = $this->users_model->PingUser($lon, $lat, $this->session->userdata('token'));

						// Insert the user's ping into the DB
						if($info['status'] == 200 && !array_key_exists('error', $info)) {
							$this->database_model->InsertPing($lon, $lat, $this->session->userdata('tinder_id'));
						}

						// Get the current batch of users
						$info = $this->users_model->PresentUsers($this->session->userdata('token')); 

						// If there isn't a recs timeout
						if($info !== FALSE) {
							// Remove all of the batches from the previous load
							$this->database_model->RemoveAllBatch($this->session->userdata('user_id'));

							// Insert the user batch into the DB
							$this->database_model->InsertBatch($this->session->userdata('user_id'), $this->session->userdata('tinder_id'), $info, $lon, $lat);

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
						$next = $this->database_model->GetBatchUser($this->session->userdata('user_id'));

						// Lookup the user to see if there's any mutual likes or friends
						$lookup = $this->users_model->UserLookup($next, $this->session->userdata('token'));

						$this->load->view('find_users_two', $lookup); 
					} else {
						$this->load->view('errors/timeout'); 
					}
				}
			}

			public function GetConnections() {
				// Get the page and type from the URL
				$page = $this->input->get('page');
				$type = $this->input->get('type');
				$id = $this->input->get('id');
				$q = $this->input->get('q');

				// Get the results depening on what the user is looking for
				switch($type) {
					case'likes';

						$results = $this->database_model->GetLikeCount($id, FALSE, $q);
						break;

					case'mutual_likes';

						$results = $this->database_model->GetMutualLikeCount($id, $this->session->userdata('tinder_id'), $q);
						break;

					case'liked_by';

						$results = $this->database_model->GetLikeCount($id, TRUE, $q);
						break;

					case'matches';

						$results = $this->database_model->GetMatchCount($id, $q);
						break;

					case'mutual_matches';

						$results = $this->database_model->GetMutualMatchCount($id, $this->session->userdata('tinder_id'));
						break;

					case'passes';

						$results = $this->database_model->GetPassCount($id, FALSE, $q);
						break;

					case'mutual_passes';

						$results = $this->database_model->GetMutualPassCount($id, $this->session->userdata('tinder_id'), $q);
						break;

					case'passed_by';

						$results = $this->database_model->GetPassCount($id, TRUE, $q);
						break;
				}

				// Get the stats for the pagination
				$count = $results['count'];
				$per_page = 20;
				$new_page = $page+1;
				$pages = ceil($count/$per_page);
				$start = $page*$per_page;

				if($page == ($pages-1)) {
					$mod = $count/$per_page;

					if($mod > 0) {
						$end = $start+$mod;
					} else {
						$end = $start+$per_page;
					}
				} else {
					$end = $start+$per_page;
				}

				$view_info = array('connections' => $results['users'],
								'id' => $id,
								'type' => $type,
								'count' => $count,
								'left_over' => $count-(($new_page)*$per_page),
								'end' => $end,
								'pages' => $pages,
								'page' => $page,
								'new_page' => $new_page);

				FormatArray($view_info);
				// Load the view
				$this->load->view('backend/connections', $view_info);
			}

			public function GetCouple() {
				// Get the parameters from the URL
				$sex = $this->input->get('sex');
				$state = $this->input->get('state');

				// Get the hottest user from the given state
				$info = $this->database_model->HottestByState($sex, $state);

				if($info) {
					echo json_encode($info);
				} else {
					echo 'error';
				}
			}

			public function GetMatchInfo() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Get the match ID from the URL
					$id = $this->input->get('match_id');

					// Get the match info
					$match = $this->users_model->GetMatchInfo($id, $this->session->userdata('token'));
					$user_id = $match['results']['participants'][1];

					$data = array('name' => $match['results']['person']['name'],
								'pic' => ReturnProfilePic($match['results']['person']['photos']),
								'id' => $match['results']['person']['_id']);
					echo json_encode($data);
				}
			}

			public function GetUpdates() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Call the GetUpdates function in the users model 
					$updates = $this->users_model->GetUpdates($this->session->userdata('token'), 'now');
					echo json_encode($updates);
				}
			}

			public function LikeUser() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Get the user ID from the URL
					$id = $this->input->get('id');

					// Call the LikeUser function in the users model 
					$like = $this->users_model->LikeUser($id, $this->session->userdata('token'));
					
					// Get the match ID
					if($like['match'] > 1) {
						$match_id = $like['match']['_id'];
					} else {
						$match_id = NULL;
					}

					// Remove the batch user from the DB and then insert him/her into the likes table
					$this->database_model->RemoveBatchUser($id, $this->session->userdata('user_id'));
					$this->database_model->InsertIntoLikes($this->session->userdata('tinder_id'), $id, $match_id);

					// Echo out the match ID
					echo $match_id;
				}
			}

			public function Logout() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Log the user out of Tinder
					$logout = $this->users_model->Logout($this->session->userdata('token'));

					// Destroy the session
					$this->session->sess_destroy();

					// Redirect the user to the home page
					header('Location: '.$this->base_url);
				}
			}

			public function PassUser() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Get the user ID from the URL
					$id = $this->input->get('id');

					// Call the PassUser function in the users model 
					$pass = $this->users_model->PassUser($id, $this->session->userdata('token'));

					// Remove the batch user from the DB and then insert him/her into the passes table
					$this->database_model->RemoveBatchUser($id, $this->session->userdata('user_id'));
					$this->database_model->InsertIntoPasses($this->session->userdata('tinder_id'), $id);
				}
			}

			public function ReportUser() {
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Get the Tinder ID from the URL
					$id = $this->input->get('id');
					$reason = $this->input->get('reason');
					$text = $this->input->get('text');

					if($text == '') {
						$text = NULL;
					}

					// Check to see if this user has already reported this user before
					$check = $this->database_model->CheckReport($this->session->userdata('tinder_id'), $id);

					if($check == 0) {
						// Send a request to Tinder's API to report this user
						$report = $this->users_model->ReportUser($id, $this->session->userdata('token'), $reason, $text);

						// If the report was successfully sent to Tinder, then record it in the DB
						if(array_key_exists('status', $report)) {
							if($report['status'] == 200) {
								$this->database_model->InsertReport($this->session->userdata('tinder_id'), $id);
							}
						}

						echo json_encode($report);
					}
				}
			}

			public function SendMessage() {
				// Find out if the user is logged in or not
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Get the match ID and the message from the URL
					$id = $this->input->post('id');
					$msg = $this->input->post('msg');

					// Call the SendMessage function in the users model 
					$message = $this->users_model->SendMessage($id, $msg, $this->session->userdata('token'));
					// FormatArray($message);
				}
			}

			public function UpdateProfile() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					// Update the pic order
					// $this->users_model->ChangePicOrder($pics, $auth);

					// Update the bio and/or gender
					$bio = $this->input->post('bio');
					$gender = $this->session->userdata('gender');
					$update = $this->users_model->UpdateProfile($this->session->userdata('auth'), $bio, $gender);

					// Update the user's row in the DB
					$this->database_model->UpdateUser($this->session->userdata('tinder_id'), array('bio' => $bio, 'gender' => $gender));
					// FormatArray($update);
				}
			}
		}
	}