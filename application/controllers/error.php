<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Error extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				// Get the base URL
				$this->base_url = $this->config->base_url();
				$this->load->library('session');
				$this->load->model('users_model', 'user');
			}

			public function Index() {
				if($this->session->userdata('user_id')) {
					$session = TRUE;
					$name = $this->session->userdata('first_name'); 
					$tinder_id = $this->session->userdata('tinder_id');
					$token = $this->session->userdata('token');

					// Get the match count of the user who is currently logged in
					$match_count = $this->database->GetMatchCount($tinder_id);
					$link = FormatUserLink($tinder_id, $this->session->userdata('username'));
					$pic = ChangePicSize($this->session->userdata('profile_pic'), 174);
				} else {
					$session = FALSE;
					$name = NULL;
					$match_count = NULL;
					$link = NULL;
					$pic = NULL;
				}

				// Get the footer info
				$locations = $this->loc->FooterPlaces();
				$rand_users = $this->database->GetAllUsers(5);

				// Store all of the data that needs to be passed to the view as an array
				$data = array('session' => $session,
							'name' => $name,
							'auth' => $this->session->userdata('token'),
							'match_count' => $match_count,
							'profile_pic' => $pic,
							'profile_link' => $link,
							'locations' => $locations, 
							'users' => $rand_users);
				$this->load->view('errors/error', $data); 
			}
		}
	}