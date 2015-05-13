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
					$meta_info = array('description' => 'Sign In to Twinder.',
									'img' => 'http://twinder.io/public/img/logo.jpg',
									'url' => 'http://twinder.io/signin',
									'type' => 'article');

					// Set all of the info that needs to be passed to the header view
					$header = array('title' => 'Sign In',
									'type' => 'article',
									'session' => FALSE,
									'header' => 'Sign in',
									'meta' => $meta_info,
									'auth' => NULL);

					// Get all of the data for the footer view
					$places = $this->loc->FooterPlaces();
					$users = $this->database->GetAllUsers(5);
					$footer = array('locations' => $places, 'users' => $users);

					// Load all of the views
					$this->load->view('templates/header', $header); 
					$this->load->view('signin'); 
					$this->load->view('templates/footer', $footer); 
				}
			}

			public function Login() {
				// Make sure the form was submitted
				if($this->input->post('submit') == 'submit') {
					$username = $this->input->post('username');
					$password = $this->input->post('password');

					// Log the user in and get the auth token
					$login = $this->user->SyncAccount($username, $password);

					if($login) {
						$this->session->set_userdata($login);
						$this->config->set_item('sess_expiration', 86400);
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