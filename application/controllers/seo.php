<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Seo extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				// Get the base URL
				$this->base_url = $this->config->base_url();

				// Load all of the models
				$this->load->model('users_model', 'user');
				$this->load->model('twitter_model', 'twitter');
			}

			public function Index() {
				// Set all of the info that needs to be passed to the header view
				$info = $this->database->GetAllUsers();

				// Load all of the views
				$this->load->view('sitemap', array('users' => $info)); 
			}

			public function Test() {
				$this->database->FlushDB();
				die;
				
			}
		}
	}