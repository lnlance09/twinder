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
				$this->load->model('users_model');
			}

			public function Index() {
				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				// Get all of the URL parameters
				$params = $this->uri->uri_to_assoc(2);
				// FormatArray($params);
				// die;

				// Get the validated query parameters
				$valids = $this->users_model->ValidateParams($params);

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
				if($q != '') {
					$url .= '?q='.$q;
				}
				
				// Determine whether to use the coordinates of the city or the state
				if($state['lon'] !== NULL && $state['lat'] !== NULL) {
					if($city['lon'] !== NULL && $city['lat'] !== NULL) {
						$lon = $city['lon'];
						$lat = $city['lat'];
					} else {
						$lon = $state['lon'];
						$lat = $state['lat'];
					}

					$set = 'true';
				} else {
					$set = 'false';
				}

				// echo $lon.', '.$lat.'<br>';
				// die;

				// The number of user that meet this criteria
				$query = $this->database_model->HotQuery($gender, $min, $max, $q);
				$hot = $this->database_model->GetHottest($query, $this->session->userdata('lon'), $this->session->userdata('lat'), $distance);

				// Check to see if the client is logged in
				if(is_numeric($user_id)) {
					$session = TRUE;
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get all of the stats for the header if the client is logged in
					$stats = $this->database_model->GetThreeStats($tinder_id);
					$like_count = $stats['like_count'];
					$match_count = $stats['match_count'];
					$pass_count = $stats['pass_count'];
				} else {
					$session = FALSE;
					$auth = NULL;
					$tinder_id = NULL;
					$like_count = NULL;
					$match_count = NULL;
					$pass_count = NULL;
				}

				// Format the user's profile link
				$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

				// Store all of the gender filters in an array
				$genders = array(array('num' => 0, 'name' => 'men'),
		                    	array('num' => 1, 'name' => 'women'),
		                    	array('num' => -1, 'name' => 'both'));

				// Define the title of the document based upon the query parameters
				$title = DefineTitle($gender, $city['name'], $state['name'], $distance, $min, $max);

				// Define the meta tags
				$meta_info = array('description' => 'See who the hottest, most popular users on WeTinder are. Find the hottest men and women. Narrow your searches to specific areas and ages.',
									'img' => $this->base_url.'public/img/',
									'url' => $url);

				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => $title,
									'session' => $session,
									'header' => 'The hottest',
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'like_count' => $like_count,
									'pass_count' => $pass_count,
									'match_count' => $match_count,
									'name' => $this->session->userdata('first_name'),
									'meta' => $meta_info,
									'profile_link' => $profile_link,
									'q' => $q);

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
									'set' => $set);
				// FormatArray($body_info);
				// die;

				// Load all of the views
				$this->load->view('templates/header', $header_info); 
				$this->load->view('hot', $body_info); 
				$this->load->view('templates/footer'); 
			}

			public function GetHottest() {
				// Get all of the query string parameters
				$params = $this->input->get();		
				// FormatArray($params);

				foreach($params as $key => $value) {
					$$key = $value;
				}

				// Get all of the hottest users
				$query = $this->database_model->HotQuery($gender, $min, $max, $q);
				$hot = $this->database_model->GetHottest($query, $lon, $lat, $distance);
				// FormatArray($hot);

				// Get the city and state
				$location = $this->location_model->BingLocation($lon, $lat);
				$state = $location['state'];

				$params = array('gender' => $gender,
								'lon' => $lon,
								'lat' => $lat,
								'distance' => $distance,
								'min' => $min,
								'max' => $max,
								'q' => $q,
								'page' => $page,
								'count' => $hot['count']);
				// FormatArray($params);

				// Calculate all of the info for the pagination in the view
				$count = $hot['count'];
				$per_page = 30;
				$per_row = 6;
				$pages = ceil($count/$per_page);
				$start = $page*$per_page;

				if($page == ($pages-1)) {
					$mod = $count/$per_page;

					if($mod > 0) {
						$end = $start+$mod;
						$end_col = ($end-$start)%$per_row;
					} else {
						$end = $start+$per_page;
						$end_col = $end;
					}
				} else {
					$end = $start+$per_page;
					$end_col = $end;
				}

				$num_rows = ceil($end/$per_row);

				$view_info = array('q_string' => http_build_query($params), 
									'hot' => $hot, 
									'state' => $this->location_model->FullFromAbbrev($state),
									'abbrev' => $state,
									'states' => $this->location_model->States(),
									'count' => $count,
									'left_over' => $count-(($page+1)*$per_page),
									'end_col' => $end_col,
									'per_row' => $per_row,
									'num_rows' => $num_rows,
									'pages' => $pages,
									'page' => $page,
									'new_page' => $page+1);
				// FormatArray($view_info);

				$this->load->view('backend/hot', $view_info); 
			}
		}
	}