<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Chat extends CI_Controller {
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

				// Get the ID from the URL
				$id = $this->uri->segment(2, NULL);

				// Get the two participants in this chat
				$match_users = $this->database_model->GetMatchInfo($id);

				if(UserExists($match_users['user_one']) == 1) {
					$person_id = $match_users['user_one'];
				} else {
					$person_id = $match_users['user_two'];
				}	

				// Get the auth token of the user who is one of the participants in the chat
				$token = $this->database_model->GetUserAuthToken($person_id);

				// Get the info about the chat
				$info = $this->users_model->GetMatchInfo($id, $token);

				if(is_numeric($user_id)) {
					$session = TRUE;
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get all of the stats for the header if the client is logged in
					$stats = $this->database_model->GetThreeStats($tinder_id);
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];
					$pass_count = $stats['pass_count'];
				} else {
					$session = FALSE;
					$auth = NULL;
					$tinder_id = NULL;
					$like_count = NULL;
					$match_count = NULL;
					$pass_count = NULL;
				}

				$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

				// Define the meta tags
				$meta_info = array('description' => ,
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url.'terms');

				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => 'Terms of Service',
									'session' => $session,
									'header' => 'Terms of Service',
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'like_count' => $like_count,
									'match_count' => $match_count,
									'pass_count' => $pass_count,
									'first_name' => $this->session->userdata('first_name'),
									'last_name' => $this->session->userdata('last_name'),
									'meta' => $meta_info,
									'profile_link' => $profile_link);

				// Load all of the views
				$this->load->view('header', $header_info); 
				$this->load->view('terms'); 
				$this->load->view('footer'); 
			}
		}
	}