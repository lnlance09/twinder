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

				// Make sure the user is logged in
				if(is_numeric($user_id)) {
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get the most recent info about the user from Tinder
					$info = $this->users_model->ProfileInfo($auth);
					$lon = $info['pos']['lon'];
					$lat = $info['pos']['lat'];

					// Get all of the stats for the header if the client is logged in
					$stats = $this->database_model->GetThreeStats($tinder_id);
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];
					$pass_count = $stats['pass_count'];

					// Get the user's profile link
					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					// Store all of the gender filters in an array
					$filters = array(array('num' => 0, 'name' => 'Men'),
			                    	array('num' => 1, 'name' => 'Women'),
			                    	array('num' => -1, 'name' => 'Both'));

					// Store all of the gender values in an array
					$genders = array(array('num' => 0, 'name' => 'Male'),
									array('num' => 1, 'name' => 'Female'));

					$settings_info = array('distance' => $info['distance_filter'],
											'min' => $info['age_filter_min'],
											'max' => $info['age_filter_max'],
											'gender_filter' => $info['gender_filter'],
											'gender' => $info['gender'],
											'username' => $this->session->userdata('username'),
											'city' => $city,
											'state' => $state,
											'lon' => $lon,
											'lat' => $lat,
										    'filters' => $filters,
										    'genders' => $genders);

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
										'match_count' => $match_count,
										'profile_link' => $profile_link);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('settings', $settings_info); 
					$this->load->view('templates/footer'); 
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
				// FormatArray($info);

				// Update the username in the DB
				$this->database_model->UpdateUser($this->session->userdata('tinder_id'), array('username' => $username));

				// Update the username session variable
				$this->session->set_userdata('username', $username);
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