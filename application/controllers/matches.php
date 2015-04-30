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
						// Make sure that the person viewing the thread is allowed to
						if($tinder_id == $match['user_one']['id'] || $tinder_id == $match['user_two']['id']) {
							// Get info about the given match
							$live = $this->user->GetMatchInfo($id, $auth);

							// Make sure the match still exists
							if($live['status'] == 200) {
								$this->database->UpdateThread($live['results']['messages'], count($live['results']['messages']));
								$can_send = 'true';
							} else {
								$can_send = FALSE;
							}

							// Format the user's profile pic and their page link
							$profile_img = ChangePicSize($profile_pic, 172);
							$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

							// Set all of the info that needs to be passed to the header view
							$header = array('title' => $match['user_one']['name'].' and '.$match['user_two']['name'],
											'name' => $this->session->userdata('first_name'),
											'auth' => $auth,
											'session' => $session,
											'tinder_id' => $tinder_id,
											'profile_link' => $profile_link,
											'profile_pic' => $profile_img);

							// Define the body info
							$body = array('match_id' => $id,
										'user_one' => $match['user_one'],
										'user_two' => $match['user_two'],
										'my_tinder_id' => $tinder_id = $this->session->userdata('tinder_id'),
										'unmatched' => $match['user_one']['unmatched'],
										'can_send' => $can_send);

							// Get all of the data for the footer view
							$places = $this->loc->FooterPlaces();
							$users = $this->database->GetAllUsers(5);
							$footer = array('locations' => $places, 'users' => $users);

							// Load all of the views
							$this->load->view('templates/header', $header); 
							$this->load->view('match', $body); 
							$this->load->view('templates/footer', $footer); 
						} else {
							header('Location: '.$this->base_url);
						}
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

				// Save the logged in user's Tinder ID
				$my_id = $this->session->userdata('tinder_id');

				// Get all of the users sorted by the given filter
				$thread = $this->database->GetThread($id);
				// FormatArray($thread);
				// die;

				// Get the match info
				$match = $this->database->GetMatchInfo($id);
				// FormatArray($match);
				// die;

				$him = ($match['user_one'] == $my_id ? $match['user_two'] : $match['user_one']);

				// Load the view
				$data = array('messages' => $thread['data'], 
							'count' => $thread['count'],
							'user_one' => $match['user_one'],
							'user_two' => $match['user_two'],
							'his_name' => $him['name'],
							'his_img' => $him['pic'],
							'his_link' => $this->base_url.$him['link'],
							'datetime' => $match['created_at'],
							'page' => $page);
				$this->load->view('backend/thread', $data); 
			}
		}
	}