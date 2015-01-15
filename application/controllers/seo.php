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
				//$geo = GeoLocation('-73.9844', '40.7590');
				//FormatArray($geo);
				//die;

				//$auth = 'bee1d478-2a75-4602-a2a5-d47fcdfe40ca'; 
				$auth = $this->session->userdata('token');
				//$info = $this->users_model->UserLookup('53b0f42b451e714a5fd0c819', $auth);
				$info = $this->users_model->FindUsers($auth);
				//FormatArray($info);
				//die;

				// Flush the DB
				//$this->database_model->FlushDB();   
				//die;

				$hot = $this->database_model->GetHottest();  

				for($i=0;$i<count($hot['users']);$i++) {
					$bio = $hot['users'][$i]['bio'];

					echo '<a href="'.$this->base_url.'users/'.$hot['users'][$i]['tinder_id'].'">'.strlen($bio).'</a><br>';
					if($bio != '') {
						// echo 'Bio: '.$bio.'<br><br>';
						echo 'Bio: '.BioLinks($bio).'<br><br>';
						echo '<br><br>';
					}
				}
			}
		}
	}