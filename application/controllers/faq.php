<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Faq extends CI_Controller {
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
				$meta_info = array('description' => 'Commonly asked questions about WeTinder',
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url.'faq');

				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => 'Frequently Asked Questions',
									'session' => $session,
									'header' => 'Frequently Asked Questions',
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'like_count' => $like_count,
									'match_count' => $match_count,
									'pass_count' => $pass_count,
									'name' => $this->session->userdata('first_name'),
									'meta' => $meta_info,
									'profile_link' => $profile_link);

				// Load all of the views
				$this->load->view('templates/header', $header_info); 
				$this->load->view('faq'); 
				$this->load->view('templates/footer'); 
			}
		}
	}