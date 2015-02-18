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
				$info = $this->database_model->GetAllUsers();

				// Load all of the views
				$this->load->view('sitemap', array('links' => $info)); 
			}

			public function Test() {
				// $this->database_model->FlushDB();

				// $info = $this->database_model->HottestByState(1, 'NY');
				$info = $this->location_model->MapquestLatLon('40.7665111', '-73.9874572');

				//$my_id = '54697f0c99a146cd3cc80b05';
				//$his_id = '5495df819983685e07f138f2';
				//$info = $this->database_model->GetMutualLikes($my_id, $his_id, 'eli');

				// $info = $this->location_model->PlaceExists('', 'AZ');
				// $info = $this->facebook_model->FacebookOAuth('mia_falco92');
				//$info = $this->facebook_model->FacebookCheck();
				FormatArray($info);

				// Get all of the user's matches since they have joined
				//$updates = $this->users_model->GetUpdates('b3b5096f-b4ec-4604-ade0-34583b22200a', '-100 days');
				//FormatArray($updates);
				//die;

				//$token = $this->facebook_model->FacebookToken('mia_falco92@mail.com', 'Codecall87!');
				//$info = $this->facebook_model->ScrapePage($token, 1043010065);
				//FormatArray($token);
				//die;
			}
		}
	}