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

				// Get the most popular place
				// $places = $this->loc->RandomLocations();

				// Define the info for the sitemap view
				$info = array('users' => $users, 
							'matches' => $matches
							// 'places' => $places
							);

				// Load all of the views
				$this->load->view('sitemap', $info); 
			}

			public function Test() {
				$info = $this->loc->FooterPlaces(-80.26355, 25.771126);
				// $info = $this->database->NewQuery(-73.9844, 40.7590);
				FormatArray($info, TRUE);
				
				/*
				$auth = '2740a27d-fa00-405c-afb2-a866ae38886f';
				$info = $this->user->UpdateSettings($auth, 20, 20, 18, 1, 1);
				FormatArray($info, TRUE);
				*/
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