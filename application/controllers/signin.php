<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Signin extends CI_Controller {
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

				// Check to make sure the user is logged in
				if($user_id) {
					header('Location: '.$this->base_url);
				} else {
					// Define the meta tags
					$meta_info = array('description' => 'Sign In to WeTinder',
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url.'signin');

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Sign In',
										'session' => FALSE,
										'header' => 'Sign in to Facebook',
										'meta' => $meta_info,
										'auth' => '');

					// Set all of the info that needs to be passed to the dashboard view
					$body_info = [];

					// Get all of the data for the footer view
					$locations = $this->loc->RandomLocations();
					$rand_users = $this->database->GetAllUsers();
					$footer_info = array('locations' => $locations, 'users' => $rand_users);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('signin'); 
					$this->load->view('templates/footer', $footer_info); 
				}
			}

			public function Login() {
				$submit = $this->input->post('submit');

				// Make sure the form was submitted
				if($submit == 'submit') {
					$username = $this->input->post('username');
					$password = $this->input->post('password');

					// Log the user in and get the auth token
					$login = $this->user->SyncAccount($username, $password);

					// Use if internet not available
					// $login = $this->database->GetUserInfo('5495df819983685e07f138f2');
					// FormatArray($login);

					if($login) {
						// Set the session data
						$this->session->set_userdata($login);

						// Set the session for 1 day
						$this->config->set_item('sess_expiration', 86400);
						// FormatArray($this->session->all_userdata());
						// die;

						echo 'true';
					} else {
						echo 'error';
					}
				} else {
					echo FALSE;	
				}
			}
		}
	}