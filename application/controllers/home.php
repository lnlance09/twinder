<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Home extends CI_Controller {
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
				header('Location: '.$this->base_url.'hot');
				die;

				// Get the user ID
				$user_id = $this->session->userdata('user_id');
			
				if(!$user_id) {
					header('Location: '.$this->base_url.'hot');
				} else {
					// Define the meta tags
					$meta_info = array('description' => 'Tinder for Web',
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url);

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'WeTinder',
										'session' => FALSE,
										'header' => '',
										'meta' => $meta_info);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('main'); 
					$this->load->view('templates/footer'); 
				}
			}

			public function DrawPieChart() {
				// Get the state abbreviation from the URL
				$state = $this->input->get('state');
				
				// Query the DB to see if the state is an acceptable value
				$valid = $this->loc->CheckState($state);

				if($valid) {
					// Get info about the state for each gender
					$all = $this->database->GetUsersInState($state);
					$male = $this->database->GetUsersInState($state, 0);
					$female = $this->database->GetUsersInState($state, 1);

					// Get the hottest user from the given state
					$mr = $this->database->HottestByState($state, 0);
					$mrs = $this->database->HottestByState($state, 1);

					$data = array('total_count' => FormatNumber($all['count']),
								'male_count' => $male['count'],
								'female_count' => $female['count'],
								'avg' => $all['avg_age'],
								'mr_link' => FormatUserLink($mr['tinder_id'], $mr['username']),
								'mrs_link' => FormatUserLink($mrs['tinder_id'], $mr['username']),
								'mr_pic' => ChangePicSize($mr['pic'], 172),
								'mrs_pic' => ChangePicSize($mrs['pic'], 172),
								'mr_name' => $mr['name'],
								'mrs_name' => $mrs['name'],
								'mr_count' => $mr['match_count'],
								'mrs_count' => $mrs['match_count'],
								'mr_age' => $mr['age'],
								'mrs_age' => $mrs['age'],
								'state' => $this->loc->FullFromAbbrev(strtoupper($state)),
								'abbrev' => $state);
					$this->load->view('backend/chart', $data); 
				}
			}

			// Autocomplete for states
			public function GetStates() {
				// Call this method to query the DB for matching states
				$states = $this->loc->GetStates($this->input->get('state'));

				// Load the autocomplete view
				$this->load->view('autocomplete/states', $states); 
			}

			// Autocomplete for cities
			public function GetCities() {
				// Get the city and state from the URL
				$state = $this->input->get('state');
				$city = $this->input->get('city');

				// Call this method to query the DB for matching states
				$cities = $this->loc->GetCities($state, $city);

				// Load the autocomplete view
				$this->load->view('autocomplete/cities', $cities); 
			}

			public function Lance() {
				// Get the user ID
				$tinder_id = $this->session->userdata('tinder_id');
				$auth = $this->session->userdata('token');

				// Make sure Lance is the only one able to execute this
				if($tinder_id == '54e82129676261064e096aac') {
					// Get the lon & lat coordinates from the URL
					$city = $this->input->get('city');
					$state = $this->input->get('state');

					// Get the match count of the user who is currently logged in
					$match_count = $this->database->GetMatchCount($tinder_id);
					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));
					$profile_pic = ChangePicSize($this->session->userdata('profile_pic'), 174);

					// Define the meta tags
					$meta_info = array('description' => 'A little bit about Twinder',
										'img' => $this->base_url.'public/img/',
										'url' => $this->base_url.'about');
					
					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Passport Users',
										'session' => TRUE,
										'header' => 'About',
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'match_count' => $match_count,
										'name' => $this->session->userdata('first_name'),
										'meta' => $meta_info,
										'profile_link' => $profile_link,
										'profile_pic' => $profile_pic);

					// Get all of the data for the footer view
					$locations = $this->loc->RandomLocations(5);
					$rand_users = $this->database->GetAllUsers(5);
					$footer_info = array('locations' => $locations, 'users' => $rand_users);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('lance', array('city' => $city, 'state' => $state)); 
					$this->load->view('templates/footer', $footer_info); 
				}
			}

			public function LanceBackend() {
				// Get Lance's Tinder ID and auth token
				$auth = $this->session->userdata('token');
				$tinder_id = $this->session->userdata('tinder_id');
				
				// Get the lon & lat coordinates from the URL
				$lon = $this->input->get('lon');
				$lat = $this->input->get('lat');

				// Make sure Lance is the only one able to execute this
				if($tinder_id == '54e82129676261064e096aac') {
					// Get users from a given location
					$passport = $this->user->Passport($auth, $lon, $lat);

					if($passport['status'] == 200) {
						// Load a fresh batch of users in the given location
						$users = $this->user->PresentUsers($auth);
						
						// Insert the users into the DB
						$this->database->InsertPassportUsers($tinder_id, $users, $lon, $lat);
						
						// Load the view
						$this->load->view('backend/lance', array('users' => $users));
					} else {
						FormatArray($passport, TRUE);
						echo "There was an error finding users";
					}
				} else {
					header('Location: '.$this->base_url);
				}
			}

			// Return city and state from lat & lon coordinates
			public function LocationFromCoords() {
				$lon = $this->input->get('lon');
				$lat = $this->input->get('lat');
				$geo = $this->loc->MapquestLatLon($lat, $lon);
				echo json_encode($geo);
			}

			// Return lat & lon coordinates from the city and state
			public function LocationFromCity() {
				$city = $this->input->get('city');
				$state = $this->input->get('state');
				$geo = $this->loc->MapquestLocation($city, $state);
				echo json_encode($geo);
			}
		}
	}