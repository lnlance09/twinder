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

				$info = $this->twitter->Verify('1230606690-7T0q3DBXOyXG2rXLrRaW1OPNRo85qQPdr1WG6to');
				// $info = $this->twitter->Authenticate();
				// $info = $this->twitter->AccessToken('IRXmuLE4SZmT7AlyuSGnNxCSkxYA2TNE', 'kwMC1PhAQAVbqhS4bzhumYihZhyfbBsZ');
				// $info = $this->twitter->ValidateAccount('IRXmuLE4SZmT7AlyuSGnNxCSkxYA2TNE');
				// $info = $this->twitter->Authenticate();
				// $info = $this->database->HottestByState(1, 'NY');
				// $info = $this->user->GetMatchInfo('', '');

				// $my_id = '54697f0c99a146cd3cc80b05';
				// $his_id = '5495df819983685e07f138f2';
				// $info = $this->database->GetMutualLikes($my_id, $his_id, 'eli');

				FormatArray($info);

				// Get all of the user's matches since they have joined
				// $updates = $this->user->GetUpdates('b3b5096f-b4ec-4604-ade0-34583b22200a', '-100 days');
				// FormatArray($updates);
				// die;
				*/
			}
		}
	}