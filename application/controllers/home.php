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

				// Load the URL helper
				$this->load->helper('url');

				// Load all of the models
				$this->load->model('users_model', 'user');
			}

			public function Index() {
				redirect('hot', 'location');
			
				// Get the user ID
				$user_id = $this->session->userdata('user_id');
			
				if(!$user_id) {
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
					$body_info = [];

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
					$mr = $this->database->HottestByState(0, $state);
					$mrs = $this->database->HottestByState(1, $state);

					// Get the state's rank
					$rank = 1;
					
					$data = array('total_count' => FormatNumber($all['count']),
								'male_count' => $male['count'],
								'female_count' => $female['count'],
								'avg' => $all['avg_age'],

								'mr_link' => FormatUserLink($mr['hot'][0]['tinder_id'], $mr['hot'][0]['username']),
								'mrs_link' => FormatUserLink($mrs['hot'][0]['tinder_id'], $mr['hot'][0]['username']),
								'mr_pic' => 'http://images.gotinder.com/'.$mr['hot'][0]['tinder_id'].'/172x172_'.$mr['hot'][0]['pic'],
								'mrs_pic' => 'http://images.gotinder.com/'.$mrs['hot'][0]['tinder_id'].'/172x172_'.$mrs['hot'][0]['pic'],
								'mr_name' => $mr['hot'][0]['name'],
								'mrs_name' => $mrs['hot'][0]['name'],

								'state' => $this->loc->FullFromAbbrev(strtoupper($state)),
								'abbrev' => $state,
								'state_rank' => $rank);
					// FormatArray($data);
					$this->load->view('backend/chart', $data); 
				}
			}

			// Autocomplete for states
			public function GetStates() {
				// Get the state from the URL
				$state = $this->input->get('state');

				// Call this method to query the DB for matching states
				$states = $this->loc->GetStates($state);
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
				$cities = $this->loc->GetCities($state, $city);
				// FormatArray($cities);

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

			public function Twitter() {
				// Get the OAuth token from the URL
				$token = $this->input->get('oauth_token');

				$info = $this->twitter->Verify($token);
				FormatArray($info);
				die;
				$this->twitter->FetchTweets($username);
			}

			public function TwitterRedirect() {
				// Load the Twitter model
				$this->load->model('twitter_model', 'twitter');

				// Authenticate the user and redirect them to http://twitter.io/home/Twitter
				$this->twitter->Authenticate();
			}
		}
	}