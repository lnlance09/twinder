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
				$users = $this->database->GetAllUsers();

				// Get all of the matches from the DB
				$matches = $this->database->GetAllMatches();

				// Load all of the views
				$this->load->view('sitemap', array('users' => $users, 'matches' => $matches)); 
			}

			public function Test() {
				$auth = '16880500-7df7-4892-b954-28676bc32eeb';

				$info = $this->user->ProfileInfo($auth);
				FormatArray($info);
				// $this->database->FlushDB();
				// die;
			}

			public function Ping() {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.gotinder.com/');
				curl_setopt($ch, CURLOPT_USERAGENT, 'Tinder/4.0.9 (iPhone; iOS 8.1.1; Scale/2.00)');
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			    $data = curl_exec($ch);
			    curl_close($ch);
			    echo $data;
			}
		}
	}