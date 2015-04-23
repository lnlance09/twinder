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
			}

			public function DrawPieChart() {
				// Get the state abbreviation from the URL
				$state = $this->input->get('state');
				
				// Query the DB to see if the state is an acceptable value
				$valid = $this->loc->CheckState($state);

				if($valid) {
					// Get info about the state for each gender
					$total = $this->database->GetUsersInState($state);
					// FormatArray($total);

					// Get the hottest user from the given state
					$hot = $this->database->HottestByState($state);

					$data = array('total_count' => FormatNumber($total['total']['count']),
								'male_count' => $total['male']['count'],
								'female_count' => $total['female']['count'],
								'avg' => $total['total']['avg_age'],
								'mr_link' => FormatUserLink($hot['mr']['tinder_id'], $hot['mr']['username']),
								'mrs_link' => FormatUserLink($hot['mrs']['tinder_id'], $hot['mr']['username']),
								'mr_pic' => ChangePicSize($hot['mr']['pic'], 172),
								'mrs_pic' => ChangePicSize($hot['mrs']['pic'], 172),
								'mr_name' => $hot['mr']['name'],
								'mrs_name' => $hot['mrs']['name'],
								'mr_count' => $hot['mr']['votes'],
								'mrs_count' => $hot['mrs']['votes'],
								'mr_age' => $hot['mr']['age'],
								'mrs_age' => $hot['mrs']['age'],
								'state' => $this->loc->FullFromAbbrev(strtoupper($state)),
								'abbrev' => $state);
					$this->load->view('backend/chart', $data); 
				}
			}

			public function GetLocations() {
				// Get the query string from the URL
				$q = $this->input->get('q');

				// Get the places from the location model
				$places = $this->loc->GetLocations(trim($q));

				// Load the view
				$this->load->view('autocomplete/places', $places);
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