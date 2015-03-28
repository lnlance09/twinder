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
				$this->load->model('users_model', 'user');
			}

			public function Index() {
				$admin_id = $this->session->userdata('admin_id');

				if($admin_id) {
					$user_id = $this->session->userdata('user_id');

					// Make sure the user is logged in
					if($user_id) {
						$auth = $this->session->userdata('token');
						$tinder_id = $this->session->userdata('tinder_id');
						$username = $this->session->userdata('username');

						// Get the most recent info about the user from Tinder
						$info = $this->user->ProfileInfo($auth);
						$lon = $info['pos']['lon'];
						$lat = $info['pos']['lat'];
						// FormatArray($info);

						// Get the city and state based upon the lon & lat coordinates
						$loc = $this->loc->MapquestLatLon($lat, $lon);
						
						// Get the mactch count of the user who is currently logged in
						$match_count = $this->database->GetMatchCount($tinder_id);

						// Get the user's profile link
						$profile_link = FormatUserLink($tinder_id, $username);
						$profile_pic = ChangePicSize($this->session->userdata('profile_pic'), 172);

						if($info['gender'] == 0) {
							$same = 'Gay';
							$other = 1;
						} else {
							$same = 'Lesbian';
							$other = 0;
						}

						// Store all of the gender filters in an array
						$filters = array(array('num' => $other, 'name' => 'Straight'),
					                    array('num' => $info['gender'], 'name' => $same),
					                    array('num' => -1, 'name' => 'Bi'));

						// Store all of the gender values in an array
						$genders = array(array('num' => 0, 'name' => 'Male'), array('num' => 1, 'name' => 'Female'));

						$settings_info = array('distance' => $info['distance_filter'],
												'min' => $info['age_filter_min'],
												'max' => $info['age_filter_max'],
												'gender_filter' => $info['gender_filter'],
												'gender' => $info['gender'],
												'username' => $username,
												'city' => $loc['city'],
												'state' => $loc['full_name'],
												'lon' => $lon,
												'lat' => $lat,
											    'filters' => $filters,
											    'genders' => $genders);

						// Set all of the info that needs to be passed to the header view
						$header_info = array('title' => 'Settings',
											'session' => TRUE,
											'header' => 'Settings',
											'name' => $this->session->userdata('first_name'),
											'auth' => $auth,
											'tinder_id' => $tinder_id,
											'match_count' => $match_count,
											'profile_link' => $profile_link,
											'profile_pic' => $profile_pic);

						// Get all of the data for the footer view
						$locations = $this->loc->RandomLocations();
						$rand_users = $this->database->GetAllUsers();
						$footer_info = array('locations' => $locations, 'users' => $rand_users);

						// Load all of the views
						$this->load->view('templates/header', $header_info); 
						$this->load->view('settings', $settings_info); 
						$this->load->view('templates/footer', $footer_info); 
					} else {
						header('Location: '.$this->base_url);
					}
				} else {
					header('Location: '.$this->base_url.'admin');
				}
			}

			public function UpdateSettings() {
				$query = $this->input->post();
				foreach($query as $key => $value) {
					$$key = $value;
				}

				// Set the auth token
				$auth = $this->session->userdata('token');

				// Update all of the settings
				$info = $this->user->UpdateSettings($auth, $distance, $max, $min, $interested_in, $gender);

				// Update the username in the DB
				$this->database->UpdateUser($this->session->userdata('tinder_id'), array('username' => $username));

				// Update the username session variable
				$this->session->set_userdata('username', $username);
			}

			public function CheckUsername() {
				// Get the username from the URL
				$username = $this->input->get('username');

				// Check to see if the username exists
				$check = $this->database->CheckUsername($username, $this->session->userdata('tinder_id'));
				echo $check;
			}
		}
	}