<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Matches extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				// Get the base URL
				$this->base_url = $this->config->base_url();

				// Load the session library
				$this->load->library('session');

				// Load all of the models
				$this->load->model('users_model', 'user');
			}

			public function Index() {
				$admin_id = $this->session->userdata('admin_id');
	
				$auth = $this->session->userdata('token');
				$user_id = $this->session->userdata('user_id');
				$tinder_id = $this->session->userdata('tinder_id');
				$profile_pic = $this->session->userdata('profile_pic');

				// Make sure the user is logged in
				$session = ($user_id ? TRUE : FALSE);

				// Get the ID from the URL
				$id = $this->uri->segment(2, NULL);

				if($id) {
					// Check to see if there is a record of the match existing in the DB
					$match = $this->database->GetMatchInfo($id);
					// FormatArray($match);
					// die;

					if($match) {
						if($tinder_id == $match['user_one']['id'] || $tinder_id == $match['user_two']['id']) {
							// Get info about the given match
							$live = $this->user->GetMatchInfo($id, $auth);
							// FormatArray($live);
							// die;

							// Make sure the match still exists
							if($live['status'] == 200) {
								// Update the thread
								$messages = $live['results']['messages'];
								$this->database->UpdateThread($messages, count($messages));
								$can_send = 'true';
							} else {
								$can_send = FALSE;
							}
						}  else {
							$can_send = FALSE;
						}

						// Update the matches new views
						$views = $this->database->UpdateMatchViews($id, $match['user_one']['views']);

						// Get the mactch count of the user who is currently logged in
						$match_count = $this->database->GetMatchCount($tinder_id);

						// Format the user's profile pic and their page link
						$profile_img = ChangePicSize($profile_pic, 172);
						$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

						// Define the meta tags
						$meta_info = array('description' => '',
										'img' => $match['user_one']['pic'],
										'url' => $this->base_url.'matches/'.$id);

						// Set all of the info that needs to be passed to the header view
						$header_info = array('title' => $match['user_one']['name'].' and '.$match['user_two']['name'],
											'name' => $this->session->userdata('first_name'),
											'auth' => $auth,
											'session' => $session,
											'tinder_id' => $tinder_id,
											'match_count' => $match_count,
											'profile_link' => $profile_link,
											'meta' => $meta_info,
											'profile_pic' => $profile_img);

						// Define the body info
						$body_info = array('match_id' => $id,
										'user_one' => $match['user_one'],
										'user_two' => $match['user_two'],
										'views' => $views,
										'my_tinder_id' => $tinder_id = $this->session->userdata('tinder_id'),
										'unmatched' => $match['user_one']['unmatched'],
										'can_send' => $can_send);

						// Get all of the data for the footer view
						$locations = $this->loc->FooterPlaces();
						$rand_users = $this->database->GetAllUsers(5);
						$footer_info = array('locations' => $locations, 'users' => $rand_users);

						// Load all of the views
						$this->load->view('templates/header', $header_info); 
						$this->load->view('match', $body_info); 
						$this->load->view('templates/footer', $footer_info); 
					} else {
						header('Location: '.$this->base_url);
					}
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function GetMatchInfo() {
				// Get the match ID from the URL
				$match_id = $this->input->get('match_id');

				// Get the match info
				$info = $this->user->GetMatchInfo($match_id, $this->session->userdata('token'));
				$pic = $info['results']['person']['photos'][0]['processedFiles'][2]['url'];
				$name = $info['results']['person']['name'];
				$id = $info['results']['person']['_id'];
				echo json_encode(array('pic' => $pic, 'name' => $name, 'id' => $id));
			}

			public function Thread() {
				// Get the parameters from the URL
				$id = $this->input->get('id');
				$page = $this->input->get('page');

				// Get all of the users sorted by the given filter
				$thread = $this->database->GetThread($id);
				// FormatArray($thread);
				// die;

				// Get the match info
				$match = $this->database->GetMatchInfo($id);
				// FormatArray($match);
				// die;

				// Load the view
				$data = array('messages' => $thread, 
							'count' => count($thread),
							'user_one' => $match['user_one'],
							'user_two' => $match['user_two'],
							'page' => $page);
				$this->load->view('backend/thread', $data); 
			}
		}
	}