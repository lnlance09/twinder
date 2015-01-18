<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Error extends CI_Controller {
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
				$this->load->view('errors/error'); 
			}
		}
	}