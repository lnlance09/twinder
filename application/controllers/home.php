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
								'mr_link' => FormatUserLink($mr['hot']['tinder_id'], $mr['hot']['username']),
								'mrs_link' => FormatUserLink($mrs['hot']['tinder_id'], $mr['hot']['username']),
								'mr_pic' => ChangePicSize($mr['hot']['pic'], 172),
								'mrs_pic' => ChangePicSize($mrs['hot']['pic'], 172),
								'mr_name' => $mr['hot']['name'],
								'mrs_name' => $mrs['hot']['name'],
								'mr_count' => $mr['hot']['match_count'],
								'mrs_count' => $mrs['hot']['match_count'],
								'mr_age' => $mr['hot']['age'],
								'mrs_age' => $mrs['hot']['age'],
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
				// Get the user ID
				$user_id = $this->session->userdata('user_id');
			
				if($user_id) {
					$tinder_id = $this->session->userdata('tinder_id');

					// Load the Twitter model
					$this->load->model('twitter_model', 'twitter');

					// Get the OAuth token from the URL
					$token = $this->input->get('oauth_token');
					$verifier = $this->input->get('oauth_verifier');

					// Get an access token and save the result as an array
					$info = $this->twitter->AccessToken($token, $verifier);
					parse_str($info, $data);
					foreach($data as $key => $value) {
						$$key = $value;
					}
					
					// $screen_name = 'shelbyjsapp';
					// Update the user's Twitter handle and user ID
					$this->database->UpdateUser($tinder_id, array('twitter_username' => $screen_name, 'twitter_id' => $user_id));

					// Get all of the user's Tweets
					$tweets = $this->twitter->FetchTweets($screen_name, 200);
					// FormatArray($tweets);

					// Sync the user's Tweets with the DB
					$this->database->SyncTweets($user_id, $tweets);

					// Get the link to the user's profile
					$link = FormatUserLink($tinder_id, $this->session->userdata('username'));

					// Go back to the user's home page
					header('Location: '.$this->base_url.$link.'/tweets');
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function TwitterRedirect() {
				// Load the Twitter model
				$this->load->model('twitter_model', 'twitter');

				// Authenticate the user and redirect them to http://twitter.io/home/Twitter
				$this->twitter->Authenticate();
			}
		}
	}