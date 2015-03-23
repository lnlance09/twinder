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
	
				if($admin_id) {
					$auth = $this->session->userdata('token');
					$user_id = $this->session->userdata('user_id');
					$tinder_id = $this->session->userdata('tinder_id');
					$profile_pic = $this->session->userdata('profile_pic');

					// Make sure the user is logged in
					if($user_id) {
						$session = TRUE;
					} else {
						$session = FALSE;
					}

					// Get the ID from the URL
					$id = $this->uri->segment(2, NULL);

					if($id) {
						// Check to see if there is a record of the match existing in the DB
						$match = $this->database->GetMatchInfo($id);

						if($match) {
							if($tinder_id == $match['user_one'] || $tinder_id == $match['user_two']) {
								// Get info about the given match
								$live = $this->user->GetMatchInfo($id, $auth);
								FormatArray($live);

								// Make sure the match still exists
								if($live['status'] == 200) {
									// Update the thread
									$messages = $live['results']['messages'];
									$this->database->UpdateThread($id, $messages);
								} 
							} 

							// Get all of the stats for the header if the client is logged in
							$stats = $this->database->GetThreeStats($tinder_id);
							$like_count = $stats['like_count'];
							$match_count = $stats['match_count'];
							$pass_count = $stats['pass_count'];

							// Format the user's profile pic and their page link
							$profile_img = PicPath($profile_pic, $tinder_id).'172x172_'.$profile_pic;
							$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

							// Set all of the info that needs to be passed to the header view
							$header_info = array('name' => $this->session->userdata('first_name'),
												'auth' => $auth,
												'session' => $session,
												'tinder_id' => $tinder_id,
												'like_count' => $like_count,
												'match_count' => $match_count,
												'pass_count' => $pass_count,
												'profile_link' => $profile_link,
												'profile_pic' => $profile_img);

							// Define the body info
							$body_info = array('match_id' => $id);

							// Load all of the views
							$this->load->view('templates/header', $header_info); 
							$this->load->view('match', $body_info); 
							$this->load->view('templates/footer'); 
						} else {
							header('Location: '.$this->base_url);
						}
					} else {
						header('Location: '.$this->base_url);
					}
				} else {
					header('Location: '.$this->base_url.'admin');
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
				$match = $this->user->GetMatchInfo($id, $this->session->userdata('token'));

				// Load the view
				$data = array('messages' => $match['results']['messages'], 
							'count' => count($match['results']['messages']),
							'user_one' => '',
							'user_two' => '',
							'page' => $page);
				$this->load->view('backend/thread', $data); 
			}
		}
	}