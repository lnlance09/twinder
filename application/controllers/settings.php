<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Settings extends CI_Controller {
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
				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				//$coord = CircleDistance(-73.9844, 40.7590, -117.1801, 32.8288);
				//echo $coord;
				//die;

				if(is_numeric($user_id)) {
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get the most recent info about the user from Tinder
					$info = $this->users_model->ProfileInfo($auth);
					$lon = $info['pos']['lon'];
					$lat = $info['pos']['lat'];

					// Get the user's like count
					$like_count = $this->database_model->GetLikeCount($tinder_id, FALSE);

					// Find out how many matches the user has
					$match_count = $this->database_model->GetMatches($tinder_id);

					// Get the pass count
					$pass_count = NULL;

					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					$settings_info = array('distance' => $info['distance_filter'],
											'min' => $info['age_filter_min'],
											'max' => $info['age_filter_max'],
											'gender_filter' => $info['gender_filter'],
											'gender' => $info['gender'],
											'username' => $this->session->userdata('username'),
											'lon' => $lon,
											'lat' => $lat);

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Settings',
										'session' => TRUE,
										'header' => 'Settings',
										'first_name' => $this->session->userdata('first_name'),
										'last_name' => $this->session->userdata('last_name'),
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'like_count' => $like_count,
										'pass_count' => $pass_count,
										'match_count' => $match_count['count'],
										'profile_link' => $profile_link);

					// Load all of the views
					$this->load->view('header', $header_info); 
					$this->load->view('settings', $settings_info); 
					$this->load->view('footer'); 
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function UpdateSettings() {
				$query = $this->input->post();
						
				foreach($query as $key => $value) {
					$$key = $value;
				}

				// Update all of the settings
				$info = $this->users_model->UpdateSettings($auth, $distance, $max, $min, $interested_in, $gender);
	
				// $this->database_model->Update
				header('Location: '.$this->base_url.'settings');
			}

			public function CheckUsername() {
				// Get the username from the URL
				$username = $this->input->get('username');

				// Check to see if the username exists
				$check = $this->database_model->CheckUsername($username);
				echo $check;
			}
		}
	}