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
				$this->load->model('users_model');
			}

			public function Index() {
				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					$tinder_id = $this->session->userdata('tinder_id');
					$auth = $this->session->userdata('token');

					// Get the ID from the URL
					$id = $this->uri->segment(2, NULL);

					// Get the user's like count
					$like_count = $this->database_model->GetLikeCount($tinder_id, FALSE);

					// Find out how many matches the user has
					$match_count = $this->database_model->GetMatches($tinder_id);

					// Get the pass count
					$pass_count = NULL;

					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					// Set all of the info that needs to be passed to the header view
					$header_info = array('session' => TRUE,
										'first_name' => $this->session->userdata('first_name'),
										'last_name' => $this->session->userdata('last_name'),
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'like_count' => $like_count,
										'match_count' => $match_count['count'],
										'pass_count' => $pass_count,
										'profile_link' => $profile_link);

					if($id != '') {
						// Get info about the given match
						$match = $this->users_model->GetMatchInfo($id, $auth);

						//FormatArray($match);

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
						$match_info = array();

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
				// Save the auth token as a variable
				$auth = $this->session->userdata('token');

				// Get the match ID from the URL
				$match_id = $this->input->get('match_id');

				$info = $this->users_model->GetMatchInfo($match_id, $auth);
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
				$matches = $this->database_model->GetMatches($id, $auth);

				//FormatArray($match['results']['messages']);
				//die;

				// Load the users backend view
				$this->load->view('backend/matches', array('matches' => $matches, 'page' => $page)); 
			}

			public function ThreadBackend() {
				// Save the auth as a variable
				$auth = $this->session->userdata('token');

				// Get the filter from the URL
				$id = $this->input->get('id');

				// Get the page num from the URL
				$page = $this->input->get('page');

				// Get all of the users sorted by the given filter
				$match = $this->users_model->GetMatchInfo($id, $auth);

				//FormatArray($match['results']['messages']);
				//die;

				// Load the users backend view
				$this->load->view('backend/thread', array('messages' => $match['results']['messages'], 'page' => $page)); 
			}
		}
	}