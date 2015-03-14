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

					// Get all of the stats for the header if the client is logged in
					$stats = $this->database->GetThreeStats($this->session->userdata('tinder_id'));
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];

					$profile_link = FormatUserLink($this->session->userdata('tinder_id'), $this->session->userdata('username'));
				} else {
					$session = FALSE;
					$name = NULL;
					$like_count = NULL;
					$match_count = NULL;
					$profile_link = NULL;
				}

				// Store all of the data that needs to be passed to the view as an array
				$data = array('session' => $session,
							'first_name' => $name,
							'like_count' => $like_count,
							'match_count' => $match_count,
							'profile_link' => $profile_link);

				$this->load->view('errors/error', $data); 
			}
		}
	}