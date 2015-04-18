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
				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				// Get all of the URL parameters
				$default = array('gender', 'city', 'state', 'distance', 'min', 'max', 'page');
				$params = $this->uri->uri_to_assoc(2, $default);
				// FormatArray($params);
				// die;

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

				// Check to see if the client is logged in
				if($user_id) {
					$session = TRUE;
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');
				} else {
					$session = FALSE;
					$auth = NULL;
					$tinder_id = NULL;
				}

				// Format the user's profile link
				$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));
				$profile_pic = ChangePicSize($this->session->userdata('profile_pic'), 174);

				// Store all of the gender filters in an array
				$genders = array(array('num' => 0, 'name' => 'men'),
		                    	array('num' => 1, 'name' => 'women'),
		                    	array('num' => -1, 'name' => 'both'));

				// Define the title of the document based upon the query parameters
				$title = DefineTitle($gender, $city['name'], $state['name'], $distance, $min, $max);

				// Define the full URL with all of the parameters
				$url = 'hot/'.$this->uri->assoc_to_uri($array);

				// Add the search query parameter to the URL if necessary
				if(!empty($q)) {
					$url .= '?q='.$q;
				}

				// Define the meta tags
				$meta = array('title' => $title,
							'description' => 'Twinder is an online directory of Tinder users. Browse by location, age, gender and name. Tinder for Web. Trusted Tinder client',
							'img' => 'http://twinder.io/public/img/logo.jpg',
							'url' => 'http://twinder.io/'.$url,
							'type' => 'website');

				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => $title,
									'type' => 'article',
									'session' => $session,
									'header' => 'Browse',
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'name' => $this->session->userdata('first_name'),
									'meta' => $meta,
									'q' => $q,
									'profile_link' => $profile_link,
									'profile_pic' => $profile_pic);

				// Define the body info
				$body_info = array('genders' => $genders,
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

				// Get all of the data for the footer view
				$locations = $this->loc->FooterPlaces();
				$rand_users = $this->database->GetAllUsers(5);
				$footer_info = array('locations' => $locations, 'users' => $rand_users);

				// Load all of the views
				$this->load->view('templates/header', $header_info); 
				$this->load->view('hot', $body_info); 
				$this->load->view('templates/footer', $footer_info); 
			}

			public function GetHottest() {
				// Get all of the query string parameters
				$params = $this->input->get();		
				foreach($params as $key => $value) {
					$$key = $value;
				}
				
				// Get all of the hottest users
				$hot = $this->database->GetHottest(FALSE, $gender, $min, $max, $q, $lon, $lat, $distance);
				$count = $hot['count'];

				// Get the city and state
				$location = $this->loc->MapquestLatLon($lat, $lon);

				// Calculate all of the info for the pagination in the view
				$per_page = 20;
				$pages = ceil($count/$per_page);
				// var_dump($page);

				// Validate the page
				$page = ($page < $pages ? $page : 0);
				$start = $page*$per_page;

				if($page == ($pages-1)) {
					if($page == 0) {
						$end = $count;
					} else {
						$mod = $count%$per_page;
						$end = ($mod > 0 ? $start+$mod : $start+$per_page);
					}
				} else {
					$end = $start+$per_page;
				}

				// Define all of the parameters
				$params = array('gender' => $gender,
								'lon' => $lon,
								'lat' => $lat,
								'distance' => $distance,
								'min' => $min,
								'max' => $max,
								'q' => $q,
								'page' => $page,
								'count' => $count);
				// FormatArray($params);

				// Define all of the info that will be passed to the view
				$info = array('q_string' => http_build_query($params), 
							'hot' => $hot, 
							'state' => $this->loc->FullFromAbbrev($location['state']),
							'abbrev' => $location['state'],
							'states' => $this->loc->States(),
							'count' => $count,
							'left_over' => $count-(($page+1)*$per_page),
							'end' => $end,
							'page' => $page,
							'pages' => $pages,
							'new_page' => $page+1);
				// FormatArray(array_slice($info, 5));

				// Load the view
				$this->load->view('backend/hot', $info); 
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