<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class About extends CI_Controller {
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
				$meta_info = array('description' => "Twinder is an online directory of Tinder users. Browse by location, age, gender and name. Use Twinder the same way you use Tinder on your phone. Sync your account now. It's free.",
									'img' => 'http://twinder.io/public/img/logo.jpg',
									'url' => 'http://twinder.io/about',
									'type' => 'article');
				
				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => 'About',
									'type' => 'article',
									'session' => $session,
									'header' => 'About',
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'name' => $this->session->userdata('first_name'),
									'meta' => $meta_info,
									'profile_link' => $profile_link,
									'profile_pic' => $profile_pic);

				// Get all of the data for the footer view
				$locations = $this->loc->FooterPlaces();
				$rand_users = $this->database->GetAllUsers(5);
				$footer_info = array('locations' => $locations, 'users' => $rand_users);

				// Load all of the views
				$this->load->view('templates/header', $header_info); 
				$this->load->view('about'); 
				$this->load->view('templates/footer', $footer_info); 
			}
		}
	}