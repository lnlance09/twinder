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
				$this->load->model('users_model');

				// Load the session library
				$this->load->library('session');
			}

			public function Index() {
				// Set all of the info that needs to be passed to the header view
				$info = $this->database_model->GetHottest();

				// Load all of the views
				$this->load->view('sitemap', array('users' => $info)); 
			}

			public function Test() {
				$this->database_model->FlushDB();
				//$auth = $this->session->userdata('token');
				//$info = $this->users_model->ReportUser('52cbac34fe5f7abb63000661', $auth, 1, NULL);

				/*
				$center_lat = 40.7590;
				$center_lng = -73.9844;
				$lat = 34.0617;
				$lng = -118.2458;
				$info = Haversine($center_lat, $center_lng, $lat, $lng);
				echo $info;
				*/

				// Get all of the user's matches since they have joined
				//$updates = $this->users_model->GetUpdates('b3b5096f-b4ec-4604-ade0-34583b22200a', '-100 days');
				//FormatArray($updates);
				//die;

				//$token = $this->facebook_model->FacebookToken('mia_falco92@mail.com', 'Codecall87!');
				//$info = $this->facebook_model->ScrapePage($token, 1043010065);
				//FormatArray($info);
				die;

				// Flush the DB
				//$this->database_model->FlushDB();   
				//die;
			}
		}
	}