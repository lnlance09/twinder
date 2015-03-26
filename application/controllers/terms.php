<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Terms extends CI_Controller {
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
					// Get the user ID
					$user_id = $this->session->userdata('user_id');

					if($user_id) {
						$session = TRUE;
						$auth = $this->session->userdata('token');
						$tinder_id = $this->session->userdata('tinder_id');

						// Get all of the stats for the header if the client is logged in
						$stats = $this->database->GetThreeStats($tinder_id);
						$match_count = $stats['match_count'];
					} else {
						$session = FALSE;
						$auth = NULL;
						$tinder_id = NULL;
						$match_count = NULL;
					}

					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));
					$profile_pic = ChangePicSize($this->session->userdata('profile_pic'), 174);

					// Define the meta tags
					$meta_info = array('description' => "Twinder's Terms of Service",
										'img' => $this->base_url.'public/img/',
										'url' => $this->base_url.'terms');

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Terms of Service',
										'session' => $session,
										'header' => 'Terms of Service',
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'match_count' => $match_count,
										'name' => $this->session->userdata('first_name'),
										'meta' => $meta_info,
										'profile_link' => $profile_link,
										'profile_pic' => $profile_pic);

					// Get all of the data for the footer view
					$locations = $this->loc->RandomLocations();
					$rand_users = $this->database->GetAllUsers();
					$footer_info = array('locations' => $locations, 'users' => $rand_users);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('terms'); 
					$this->load->view('templates/footer', $footer_info); 
				} else {
					header('Location: '.$this->base_url.'admin');
				}
			}
		}
	}