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
			}

			public function Index() {
				// Set all of the info that needs to be passed to the header view
				// $users = $this->database->GetAllUsers();

				// Get the most popular place
				$places = $this->loc->RandomLocations();

				// Define the info for the sitemap view
				$info = array('places' => $places);

				// Load all of the views
				$this->load->view('sitemap', $info); 
			}

			public function Test() {
				$info = $this->user->UserLookup('543cebbe4c1508686cb3cd3a', '8ebdf6ff-ba6a-4e7e-b711-1d80dedf3651');
				var_dump($info);
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