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
				$this->load->model('users_model');
			}

			public function Index() {
				redirect('hot', 'location');
				die;
				
				// Get the user ID
				$user_id = $this->session->userdata('user_id');
			
				if(!is_numeric($user_id)) {
					// header('Location: '.$this->base_url.'users/'.$this->session->userdata('tinder_id'));
					redirect('hot', 'location');
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

					// Set all of the info that needs to be passed to the dashboard view
					$body_info = array();

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('main'); 
					$this->load->view('templates/footer'); 
				}
			}

			// Return city and state from lat & lon coordinates
			public function LocationFromCoords() {
				$lon = $this->input->get('lon');
				$lat = $this->input->get('lat');
				$geo = $this->location_model->MapquestLatLon($lat, $lon);
				echo json_encode($geo);
			}

			// Return lat & lon coordinates from the city and state
			public function LocationFromCity() {
				$city = $this->input->get('city');
				$state = $this->input->get('state');
				$geo = $this->location_model->MapquestLocation($city, $state);
				echo json_encode($geo);
			}

			// Autocomplete for states
			public function GetStates() {
				// Get the state from the URL
				$state = $this->input->get('state');

				// Call this method to query the DB for matching states
				$states = $this->location_model->GetStates($state);
				// FormatArray($states);

				// Load the autocomplete view
				$this->load->view('autocomplete/states', $states); 
			}

			// Autocomplete for cities
			public function GetCities() {
				// Get the city and state from the URL
				$state = $this->input->get('state');
				$city = $this->input->get('city');

				// Call this method to query the DB for matching states
				$cities = $this->location_model->GetCities($state, $city);
				// FormatArray($cities);

				// Load the autocomplete view
				$this->load->view('autocomplete/cities', $cities); 
			}
		}
	}