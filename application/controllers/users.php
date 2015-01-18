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

				if($user_info != 'error') {
					// If the client is logged in, then get their stats
					if(is_numeric($user_id)) {
						$session = TRUE;
						$auth = $this->session->userdata('token');
						$tinder_id = $this->session->userdata('tinder_id');

						$stats = $this->database_model->GetThreeStats($tinder_id);
						$like_count = $stats['like_count'];
						$match_count = $stats['match_count'];
						$pass_count = $stats['pass_count'];

						// Format the user's profile link
						$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

						/* UPDATE THE USER'S PROFILE */
						// Make a request to Tinder to get the most recent info about this user
						$user_info = $this->users_model->UserLookup($user_info['tinder_id'], $auth);

						if(array_key_exists('status', $user_info)) {
							if($user_info['status'] == 200) {
								//FormatArray($user_info);
								//die;

								// Update the users table with the most recent info about this user
								$data = array('bio' => $user_info['results']['bio'],
											'dob' => $user_info['results']['birth_date'],
											'gender' => $user_info['results']['gender'],
											'first_name' => $user_info['results']['name'],
											'last_activity_date' => $user_info['results']['ping_time']);
								$this->database_model->UpdateUser($user_info['results']['_id'], $data);

								$his_id = $user_info['results']['_id'];
								unset($user_info);

								// echo $his_id;

								// Get the most recent info about this user
								$user_info = $this->database_model->GetUserInfo($his_id);
								//FormatArray($user_info);
								//die;
							}
						}
					} else {
						$session = FALSE;
						$auth = NULL;
						$tinder_id = NULL;
						$like_count = NULL;
						$match_count = NULL;
						$pass_count = NULL;
						$profile_link = NULL;
					}

					// Get all of the user's pings
					$pings = $this->database_model->GetPings($user_info['tinder_id']); 

					// Define the meta tags
					$meta_info = array('description' => MetaSubject($user_info['username'], $user_info['first_name']).' on WeTinder',
									'img' => 'http://images.gotinder.com/'.$user_info['tinder_id'].'/'.$user_info['pics'][0]['tiny'],
									'url' => $this->base_url.$user_info['link']);

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => $user_info['first_name'],
										'session' => $session,
										'header' => $user_info['first_name'],
										'auth' => $auth,
										'tinder_id' => $user_info['tinder_id'],
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
					} else {
						$like = FALSE;
					}

					// Determine whether or not the client is able to edit the bio for the profile
					if($user_info['tinder_id'] == $this->session->userdata('tinder_id')) {
						$edit = 'true';
					} else {
						$edit = 'false';
					}

					// Set all of the info that needs to be passed to the dashboard view
					$body_info = array('tinder_id' => $user_info['tinder_id'],
										'like' => $like,
										'name' => $user_info['first_name'],
										'session' => $session,
										'edit' => $edit);

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
				$auth = $this->session->userdata('token');

				// Get all of the parameters from the URL
				$params = $this->input->get();
						
				foreach($params as $key => $value) {
					$$key = $value;
				}

				if($type == 'new') {
					// Ping the user's current location
					$info = $this->users_model->PingUser($lon, $lat, $auth);

					// Insert the user's ping into the DB
					if($info['status'] == 200
					&& !array_key_exists('error', $info)) {
						$tinder_id = $this->session->userdata('tinder_id');
						$this->database_model->InsertPing($lon, $lat, $tinder_id);
					}

					// Get the current batch of users
					$info = $this->users_model->PresentUsers($auth); 
					//FormatArray($info);

					// Remove all of the batches from the previous load
					$this->database_model->RemoveAllBatch($my_id);

					// Insert the user batch into the DB
					$this->database_model->InsertBatch($my_id, $info);

					// Get the most recent batch user
					$next = $this->database_model->GetBatchUser($my_id);

					// Lookup the user to see if there's any mutual likes or friends
					$lookup = $this->users_model->UserLookup($next['tinder_id'], $auth);
					// FormatArray($lookup);

					// Load the view
					$view_info = array('user' => $lookup);
				} else {
					// Get the next user in the line up
					$next = $this->database_model->GetBatchUser($my_id);

					// Lookup the user to see if there's any mutual likes or friends
					$lookup = $this->users_model->UserLookup($next['tinder_id'], $auth);
					// FormatArray($lookup);
					
					// Define the info that will be passed to the view
					$view_info = array('user' => $lookup);
				}

				$this->load->view('find_users_two', $view_info); 
			}

			public function EditProfile() {
				// Get all of the parameters from the URL
				$params = $this->input->get();
						
				foreach($params as $key => $value) {
					$$key = $value;
				}
				
				// Get the like count
				$like_count = $this->database_model->GetLikeCount($id, FALSE);

				// Find out how many matches the user has
				$match_count = $this->database_model->GetMatchCount($id);

				// Get the pass count
				$pass_count = $this->database_model->GetPassCount($id, FALSE);

				// Get the info about the user
				$user_info = $this->database_model->GetUserInfo($id);
				
				$user_info['like_count'] = FormatNumber($like_count);
				$user_info['pass_count'] = FormatNumber($pass_count);
				$user_info['match_count'] = FormatNumber($match_count);
				$user_info['edit'] = $edit;
				// FormatArray($user_info);

				// Load the view
				$this->load->view('profile_edit', $user_info); 
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
						$count = $this->database_model->GetMatchCount($tinder_id);
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

				// Get info about the user who was just matched
				$user_info = $this->database_model->GetUserInfo($user_id);

				$data = array('name' => $user_info['first_name'],
							'pic' => $user_info['pics'][0]['medium'],
							'id' => $user_id);
				echo json_encode($data);
			}

			public function GetPings() {
				// Save the user's session ID as a variable
				$tinder_id = $this->session->userdata('tinder_id');

				// Get the user's pings
				$pings = $this->database_model->GetPings($tinder_id);
			}

			public function GetUpdates() {
				// Call the GetUpdates function in the users model 
				$auth = $this->session->userdata('token');
				$updates = $this->users_model->GetUpdates($auth);
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
				$match = $like['match'];

				if(count($match) > 1) {
					$match_id = $match['_id'];
				} else {
					$match_id = FALSE;
				}

				// Remove the batch user from the DB and then insert him/her into the likes table
				$this->database_model->RemoveBatchUser($id);
				$this->database_model->InsertIntoLikes($tinder_id, $id, $match_id);

				// Echo out the match ID
				echo $match_id;
			}

			public function Logout() {
				// Save the auth token as a variable
				$auth = $this->session->userdata('token');
				
				// Log the user out of Tinder
				$logout = $this->users_model->Logout($auth);

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
				$this->database_model->RemoveBatchUser($id);
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