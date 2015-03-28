<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Hot extends CI_Controller {
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
					// Get the user ID
					$user_id = $this->session->userdata('user_id');

					// Get all of the URL parameters
					$params = $this->uri->uri_to_assoc(2);

					// Get the validated query parameters
					$valids = $this->user->ValidateParams($params);
					$gender = $valids['gender'];
					$city = $valids['city'];
					$state = $valids['state'];
					$distance = $valids['distance'];
					$min = $valids['min'];
					$max = $valids['max'];
					$page = $valids['page'];

					// Get the search parameter from the URL
					$q = $this->input->get('q');

					// Get the full URL
					$array = array('gender' => $gender,
									'city' => $city['name'],
									'state' => $state['name'],
									'distance' => $distance,
									'min' => $min,
									'max' => $max,
									'page' => $page);

					// Define the full URL with all of the parameters
					$url = $this->base_url.'hot/'.$this->uri->assoc_to_uri($array);

					// Add the search query parameter to the URL if necessary
					if(!empty($q)) {
						$url .= '?q='.$q;
					}
					
					// Determine whether to use the coordinates of the city or the state
					if($state['lon'] != NULL && $state['lat'] != NULL) {
						if($city['lon'] != NULL && $city['lat'] != NULL) {
							$lon = $city['lon'];
							$lat = $city['lat'];
						} else {
							$lon = $state['lon'];
							$lat = $state['lat'];
						}

						$set = 'true';
					} else {
						$lon = $this->session->userdata('lon');
						$lat = $this->session->userdata('lat');
						$set = 'false';
					}

					// The number of user that meet this criteria
					$query = $this->database->HotQuery($gender, $min, $max, $q);
					$hot = $this->database->GetHottest($query, $lon, $lat, $distance);

					// Check to see if the client is logged in
					if($user_id) {
						$session = TRUE;
						$auth = $this->session->userdata('token');
						$tinder_id = $this->session->userdata('tinder_id');

						// Get the mactch count of the user who is currently logged in
						$match_count = $this->database->GetMatchCount($tinder_id);
					} else {
						$session = FALSE;
						$auth = NULL;
						$tinder_id = NULL;
						$match_count = NULL;
					}

					// Format the user's profile link
					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));
					$profile_pic = ChangePicSize($this->session->userdata('profile_pic'), 174);
					
					// Get all of the state data for the pie chart
					$all_chart = $this->database->GetUsersInState($state['abbrev']);
					$male_chart = $this->database->GetUsersInState($state['abbrev'], 0);
					$female_chart = $this->database->GetUsersInState($state['abbrev'], 1);

					// Store all of the gender filters in an array
					$genders = array(array('num' => 0, 'name' => 'men'),
			                    	array('num' => 1, 'name' => 'women'),
			                    	array('num' => -1, 'name' => 'both'));

					// Define the title of the document based upon the query parameters
					$title = DefineTitle($gender, $city['name'], $state['name'], $distance, $min, $max);

					// Define the meta tags
					$meta_info = array('description' => 'See who the hottest, most popular users on Twinder are. Find the hottest men and women. Narrow your searches to specific areas and ages.',
										'img' => $this->base_url.'public/img/',
										'url' => $url);

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => $title,
										'session' => $session,
										'header' => 'The hottest',
										'auth' => $auth,
										'tinder_id' => $tinder_id,
										'match_count' => $match_count,
										'name' => $this->session->userdata('first_name'),
										'meta' => $meta_info,
										'q' => $q,
										'profile_link' => $profile_link,
										'profile_pic' => $profile_pic);

					// Define the body info
					$body_info = array('hot_count' => $hot['count'],
										'genders' => $genders,
										'gender' => strtolower($gender),
										'city' => $city['name'],
										'state' => $state['name'],
										'abbrev' => $state['abbrev'],
										'lon' => $lon,
										'lat' => $lat,
										'distance' => $distance,
										'min' => $min,
										'max' => $max,
										'q' => $q,
										'page' => $page,
										'set' => $set,
										'chart_data' => $all_chart,
										'male_percentage' => $male_chart['count'],
										'female_percentage' => $female_chart['count']);

					// Get all of the data for the footer view
					$locations = $this->loc->RandomLocations();
					$rand_users = $this->database->GetAllUsers();
					$footer_info = array('locations' => $locations, 'users' => $rand_users);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('hot', $body_info); 
					$this->load->view('templates/footer', $footer_info); 
				} else {
					header('Location: '.$this->base_url.'admin');
				}
			}

			public function GetHottest() {
				// Get all of the query string parameters
				$params = $this->input->get();		
				foreach($params as $key => $value) {
					$$key = $value;
				}
				// var_dump($page);

				// Get all of the hottest users
				$query = $this->database->HotQuery($gender, $min, $max, $q);
				$hot = $this->database->GetHottest($query, $lon, $lat, $distance);
				$count = $hot['count'];

				// Get the city and state
				$location = $this->loc->MapquestLatLon($lat, $lon);
				$state = $location['state'];

				$params = array('gender' => $gender,
								'lon' => $lon,
								'lat' => $lat,
								'distance' => $distance,
								'min' => $min,
								'max' => $max,
								'q' => $q,
								'page' => $page,
								'count' => $count);

				// Calculate all of the info for the pagination in the view
				$per_page = 30;
				$per_row = 6;
				$pages = ceil($count/$per_page);
				$start = $page*$per_page;

				if($count > 0) {
					$points = RowPagination($count, $per_row, $per_page, $page, $pages, $start);
					$end = $points['end'];
					$num_rows = $points['num_rows'];
					$end_col = $points['end_col'];
				} else {
					$end = 0;
					$num_rows = 0;
					$end_col = 0;
				}

				$view_info = array('q_string' => http_build_query($params), 
								'hot' => $hot, 
								'state' => $this->loc->FullFromAbbrev($state),
								'abbrev' => $state,
								'states' => $this->loc->States(),
								'count' => $count,
								'left_over' => $count-(($page+1)*$per_page),
								'end_col' => $end_col,
								'per_row' => $per_row,
								'num_rows' => $num_rows,
								'pages' => $pages,
								'page' => $page,
								'new_page' => $page+1);
				// FormatArray(array_slice($view_info, 5));

				// Load the view
				$this->load->view('backend/hot', $view_info); 
			}

			public function HottestUser() {
				$gender = $this->input->get('gender');
				$state = $this->input->get('state');

				// Get the hottest user
				$user = $this->database->HottestByState($gender, $state);
				echo json_encode($user);
			}
		}
	}