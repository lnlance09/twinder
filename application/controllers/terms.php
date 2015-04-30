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
				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					$session = TRUE;
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');
				} else {
					$session = FALSE;
					$auth = NULL;
					$tinder_id = NULL;
				}

				$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));
				$profile_pic = ChangePicSize($this->session->userdata('profile_pic'), 174);

				// Define the meta tags
				$meta_info = array('description' => "Twinder's Terms of Service",
									'img' => 'http://twinder.io/public/img/logo.jpg',
									'url' => 'http://twinder.io/terms',
									'type' => 'article');

				// Set all of the info that needs to be passed to the header view
				$header = array('title' => 'Terms of Service',
								'type' => 'article',
								'session' => $session,
								'header' => 'Terms of Service',
								'auth' => $auth,
								'tinder_id' => $tinder_id,
								'name' => $this->session->userdata('first_name'),
								'meta' => $meta_info,
								'profile_link' => $profile_link,
								'profile_pic' => $profile_pic);

				// Get all of the data for the footer view
				$places = $this->loc->FooterPlaces();
				$users = $this->database->GetAllUsers(5);
				$footer = array('locations' => $places, 'users' => $users);

				// Load all of the views
				$this->load->view('templates/header', $header); 
				$this->load->view('terms'); 
				$this->load->view('templates/footer', $footer); 
			}
		}
	}