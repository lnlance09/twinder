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
				$user_id = $this->session->userdata('user_id');

				// Make sure the user is logged in
				if($user_id) {
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get the ID from the URL
					$id = $this->uri->segment(2, NULL);

					// Get all of the stats for the header if the client is logged in
					$stats = $this->database->GetThreeStats($tinder_id);
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];
					$pass_count = $stats['pass_count'];

					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					// Set all of the info that needs to be passed to the header view
					$header_info = array('session' => TRUE,
										'name' => $this->session->userdata('first_name'),
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'like_count' => $like_count,
										'match_count' => $match_count,
										'pass_count' => $pass_count,
										'profile_link' => $profile_link);

					if($id) {
						// Get info about the given match
						$match = $this->user->GetMatchInfo($id, $auth);
						// FormatArray($match);

						if($match['status'] == 200) {
							if(in_array($tinder_id, $match['results']['participants'])) {
								$messages = $match['results']['messages'];
								$activity = $match['results']['last_activity_date'];

								$tinder_match = $match['results']['person'];
								$tinder_match_id = $tinder_match['_id'];
								$tinder_name = $tinder_match['name'];
								$tinder_bio = $tinder_match['bio'];
								$tinder_birth = $tinder_match['birth_date'];
								$tinder_pic = $tinder_match['photos'][0]['processedFiles'][0]['url'];

								// Define all of the info for the match view
								$match_info = array('match_id' => $id,
													'activity' => $activity,
													'tinder_id' => $tinder_match_id,
													'name' => $tinder_name,
													'bio' => $tinder_bio,
													'birth_date' => $tinder_birth,
													'profile_pic' => $tinder_pic);
							}

							$body = 'thread';
							$match_info['type'] = 'single';
							$header_info['header'] = $tinder_name;
							$header_info['title'] = 'You and '.$tinder_name;
						} else {
							header('Location: '.$this->base_url);
						}
					} else {
						// Define all of the info for the match view
						$match_info = [];

						$body = 'matches';
						$match_info['type'] = 'all';
						$header_info['header'] = 'My Matches';
						$header_info['title'] = 'My Matches';
					}

					// Load all of the views
					$this->load->view('header', $header_info); 
					$this->load->view($body, $match_info); 
					$this->load->view('footer'); 
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

			public function MatchesBackend() {
				// Save the auth as a variable
				$auth = $this->session->userdata('token');
				$id = $this->session->userdata('tinder_id');

				// Get the page num from the URL
				$page = $this->input->get('page');

				// Get all of the users sorted by the given filter
				$matches = $this->database->GetMatches($id, $auth);
				// FormatArray($match['results']['messages']);
				// die;

				// Load the users backend view
				$this->load->view('backend/matches', array('matches' => $matches, 'page' => $page)); 
			}

			public function ThreadBackend() {
				// Get the parameters from the URL
				$id = $this->input->get('id');
				$page = $this->input->get('page');

				// Get all of the users sorted by the given filter
				$match = $this->user->GetMatchInfo($id, $this->session->userdata('token'));

				// Load the view
				$data = array('messages' => $match['results']['messages'], 'page' => $page);
				$this->load->view('backend/thread', $data); 
			}
		}
	}