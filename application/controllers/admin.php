<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Admin extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				// Get the base URL
				$this->base_url = $this->config->base_url();

				// Load the session library
				$this->load->library('session');

				// Load all of the models
				$this->load->model('admin_model', 'admin');
			}

			public function Index() {
				$admin_id = $this->session->userdata('admin_id');

				if($admin_id) {
					header('Location: '.$this->base_url);
				} else {
					// Load the view
					$this->load->view('admin'); 
				}
			}

			public function Login() {
				// Get the username and password from the form
				$username = $this->input->post('username');
				$password = $this->input->post('password');

				// Store all of the login credentials in an array
				$creds = array('lance' => 'Codecall87!');

				// If the login was successful, then redirect the user to the home page
				if(array_key_exists($username, $creds)) {
					if($creds[$username] == $password) {
						// Set the session data
						$this->session->set_userdata(array('admin_id' => 1));

						// Set the session for 1 day
						$this->config->set_item('sess_expiration', 86400);

						echo 'true';
					} else {
						echo 'false';
					}
				} else {
					echo 'false';
				}
			}

			public function Logout() {
				// Make sure that the user is logged in
				$admin_id = $this->session->userdata('admin_id');

				if($admin_id) {
					// Unset the session
					$this->session->unset_userdata('admin_id');
				}

				// Redirect the user to the home page
				header('Location: '.$this->base_url);
			}
		}
	}