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
				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				if(is_numeric($user_id)) {
					$session = TRUE;
				} else {
					$session = FALSE;
				}

				// Get the ID from the URL
				$id = $this->uri->segment(2, NULL);

				// Get the info about the user
				$user_info = $this->database_model->GetUserInfo($id);
				//FormatArray($user_info);
				//die;

				if($user_info != 'error') {
					// Get the like count
					$like_count = $this->database_model->GetLikeCount($tinder_id, FALSE);

					// Find out how many matches the user has
					$match_count = $this->database_model->GetMatchCount($tinder_id);

					// Get the pass count
					$pass_count = NULL;

					// Get all of the user's pings
					$pings = $this->database_model->GetPings($user_info['tinder_id']); 

					// Define the meta tags 
					if($user_info['username'] == '') {
						$subject = $user_info['first_name'];
					} else {
						$subject = $user_info['username'];
					}

					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					$meta_info = array('description' => $subject.' on WeTinder',
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
						/*
						if($liked == 0) {
							$like = TRUE;
						} else {
							$like = 'liked';
						}
						*/
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
										// 'like' => $like,
										'edit' => $edit,
										'pass_count' => $pass_count);

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

					// Get the user's like count
					$like_count = $this->database_model->GetLikeCount($tinder_id, FALSE);

					// Find out how many matches the user has
					$match_count = $this->database_model->GetMatches($tinder_id);

					// Get the pass count
					$pass_count = NULL;

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
										'match_count' => $match_count['count'],
										'pass_count' => $pass_count,
										'first_name' => $this->session->userdata('first_name'),
										'last_name' => $this->session->userdata('last_name'),
										'meta' => $meta_info,
										'profile_link' => $profile_link);

					// $info = $this->users_model->PresentUsers($auth); 

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
				// Get all of the parameters from the URL
				$params = $this->input->get();
						
				foreach($params as $key => $value) {
					$$key = $value;
				}

				// Save the user's session ID as a variable
				$my_id = $this->session->userdata('user_id');
				$auth = $this->session->userdata('token');

				if($type == 'new') {
					$auth = $this->session->userdata('token');

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
					//die;

					// Remove all of the batches from the previous load
					$this->database_model->RemoveAllBatch($my_id);

					// Insert the user batch into the DB
					$this->database_model->InsertBatch($my_id, $info);

					// Get the most recent batch user
					$info = $this->database_model->GetBatchUser($my_id);

					// Lookup the user to see if there's any mutual likes or friends
					$lookup = $this->users_model->UserLookup($info['tinder_id'], $auth);
					//FormatArray($lookup);

					// Load the view
					$view_info = array('user' => $info);
				} else {
					$info = $this->database_model->GetBatchUser($my_id);

					// Lookup the user to see if there's any mutual likes or friends
					$lookup = $this->users_model->UserLookup($info['tinder_id'], $auth);
					// FormatArray($lookup);
					
					// Define the info that will be passed to the view
					$view_info = array('user' => $info);
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

				switch($type) {
					case'likes';

						if($inverse == 'true') {
							$inverse = TRUE;
						} else {
							$inverse = FALSE;
						}

						$results = $this->database_model->GetLikes($tinder_id, $inverse, $q);
						break;

					case'matches';

						$results = $this->database_model->GetMatches($tinder_id, $q);
						break;

					case'passes';

						if($inverse == 'true') {
							$inverse = TRUE;
						} else {
							$inverse = FALSE;
						}

						$results = $this->database_model->GetPasses($tinder_id, $inverse, $q);
						break;
				}

				// FormatArray($results);

				$body_info = array('type' => $type,
									'page' => $page,
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

				//echo 'Tinder ID: '.$id;
				//print_r($like);
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

			public function SendMessage() {
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
				$this->database_model->UpdateUser($tinder_id, $bio, $gender);
				FormatArray($update);
			}

			public function ValidateSMS() {

			}
		}
	}