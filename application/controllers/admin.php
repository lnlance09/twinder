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
				$this->load->model('admin_model');
			}

			public function Index() {
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
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

				// Check to see if the user is an admin
				$login = $this->admin_model->Login($username, $password);

				// If the login was successful, then redirect the user to the home page
				if($login) {
					header('Location: '.$this->base_url);
				} else {
					// If not, then redirect them to the admin page to log in again
					header('Location: '.$this->base_url.'admin');
				}
			}
		}
	}