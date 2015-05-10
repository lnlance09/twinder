<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Hot extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				// Get the base URL
				$this->base_url = $this->config->base_url();
				$this->load->library('session');
				$this->load->model('users_model', 'user');
			}

			public function Index() {
				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				// Get all of the URL parameters
				$default = array('gender', 'lat', 'lon', 'distance', 'min', 'max', 'page');
				$params = $this->uri->uri_to_assoc(2, $default);
				// echo "<br><br><br>";
				// FormatArray($params);

				// Get the validated query parameters
				$valids = $this->user->ValidateParams($params);
				$gender = $valids['gender'];
				$place = $valids['place'];
				$distance = $valids['distance'];
				$min = $valids['min'];
				$max = $valids['max'];
				$page = $valids['page'];
				$all = ($params['lat'] == 'all' || empty($params['lat']) ? 'true' : 'false');

				// Get the search parameter from the URL
				$q = $this->input->get('q');

				// Get the full URL
				$array = array('gender' => $gender,
								'lat' => ($all == 'true' ? 41.387917 : $params['lat']),
								'lon' => ($all == 'true' ? 2.169919 : $params['lon']),
								'distance' => $distance,
								'min' => $min,
								'max' => $max,
								'page' => $page);

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
				$link = FormatUserLink($tinder_id, $this->session->userdata('username'));
				$pic = ChangePicSize($this->session->userdata('profile_pic'), 174);

				// Store all of the gender filters in an array
				$genders = array(array('num' => 0, 'name' => 'men'), array('num' => 1, 'name' => 'women'), array('num' => -1, 'name' => 'both'));

				// Define the title of the document based upon the query parameters
				$title = DefineTitle($gender, $place['city'], $place['state'], $distance, $min, $max, $all);

				// Define the full URL with all of the parameters
				$url = 'hot/'.$this->uri->assoc_to_uri($array);
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
				$header = array('title' => $title,
								'type' => 'article',
								'session' => $session,
								'header' => 'Browse',
								'auth' => $auth,
								'tinder_id' => $tinder_id,
								'name' => $this->session->userdata('first_name'),
								'meta' => $meta,
								'q' => $q,
								'link' => $link,
								'pic' => $pic);

				// Define the body info
				$body = array('genders' => $genders,
							'gender' => strtolower($gender),
							'city' => $place['city'],
							'state' => $place['state'],
							'location' => ($all == 'true' ? 'Everywhere' : $place['city'].', '.$place['state']),
							'lon' => ($all == 'true' ? 2.169919 : $params['lon']),
							'lat' => ($all == 'true' ? 41.387917 : $params['lat']),
							'distance' => $distance,
							'min' => $min,
							'max' => $max,
							'q' => $q,
							'page' => $page,
							'all' => $all);

				// Get all of the data for the footer view
				$places = $this->loc->FooterPlaces();
				$users = $this->database->GetAllUsers(5);
				$footer = array('locations' => $places, 'users' => $users);

				// Load all of the views
				$this->load->view('templates/header', $header); 
				$this->load->view('hot', $body); 
				$this->load->view('templates/footer', $footer); 
			}

			public function GetHottest() {
				// Get all of the query string parameters
				$param = $this->input->get();		
				foreach($param as $key => $val) {
					$$key = $val;
				}

				if(!isset($q)) {
					$q = NULL;
				}

				// FormatArray($param);
				// Set to all if necessary
				if($all == 'true') {
					$lon = NULL;
					$lat = NULL;
				}

				// Get the total number of results
				$count = $this->database->GetHottest($gender, $min, $max, $q, $lon, $lat, $distance, NULL);
				
				// Calculate all of the info for the pagination in the view
				$per_page = 10;
				$pages = ceil($count/$per_page);

				if($page >= ($pages-1)) {
					$end = $count;
				} else {
					$end = ($page*$per_page)+$per_page;
				}

				$left_over = $count-(($page+1)*$per_page);
				$point = (($page+1)*$per_page)+1;

				// Get the hottest
				if($count > 0) {
					$hot = $this->database->GetHottest($gender, $min, $max, $q, $lon, $lat, $distance, $point);
					$places = NULL;
				} else {
					$hot = NULL;

					// Get some places that are close by
					if(!empty($lon) && !empty($lat)) {
						$places = $this->loc->GetCloseBy($lon, $lat);
					} else {
						$places = NULL;
					}
				}

				// Define all of the parameters
				$params = array('gender' => $gender,
								'lon' => $lon,
								'lat' => $lat,
								'distance' => $distance,
								'min' => $min,
								'max' => $max,
								'q' => $q,
								'all' => $all);

				// Define all of the info that will be passed to the view
				$info = array('query' => http_build_query($params), 
							'hot' => $hot, 
							'places' => $places,
							'end' => $end,
							'q' => $q,
							'count' => $count,
							'left_over' => $left_over,
							'per_page' => $per_page, 
							'page' => $page,
							'pages' => $pages,
							'new_page' => $page+1);
				// FormatArray(array_slice($info, 2));

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