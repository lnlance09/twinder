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
				if(is_numeric($this->session->userdata('distance_filter'))) {
					$dist = $this->session->userdata('distance_filter');
				} else {
					$dist = 50;
				}

				// Get the URL parameters
				$gender = $this->uri->segment(2, 'both');
				$city = $this->uri->segment(3, 0);
				$state = $this->uri->segment(4, 0);
				$distance = $this->uri->segment(5, $dist);
				$min = $this->uri->segment(6, 18);
				$max = $this->uri->segment(7, 50);
				$page = $this->uri->segment(8, 0);

				// Get the user ID
				$user_id = $this->session->userdata('user_id');

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

				$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

				// Define the meta tags
				$meta_info = array('description' => 'See who the hottest, most popular users on WeTinder are. Find the hottest men and women. Narrow your searches to specific areas and ages.',
									'img' => $this->base_url.'public/img/',
									'url' => $this->base_url.'hot');

				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => 'The Hottest',
									'session' => $session,
									'header' => 'The hottest',
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'like_count' => $like_count,
									'pass_count' => $pass_count,
									'match_count' => $match_count,
									'first_name' => $this->session->userdata('first_name'),
									'last_name' => $this->session->userdata('last_name'),
									'meta' => $meta_info,
									'profile_link' => $profile_link);

				// Define the body info
				$body_info = array('gender' => $gender,
									'city' => $city,
									'state' => $state,
									'distance' => $distance,
									'meters' => MilesToMeters($distance),
									'min' => $min,
									'max' => $max,
									'page' => $page);

				//FormatArray($body_info);
				//die;

				// Load all of the views
				$this->load->view('header', $header_info); 
				$this->load->view('hot', $body_info); 
				$this->load->view('footer'); 
			}

			public function GetHottest() {
				// Get all of the query string parameters
				$params = $this->input->get();
						
				foreach($params as $key => $value) {
					$$key = $value;
				}

				// Get all of the hottest users
				$hot = $this->database_model->GetHottest($gender, $city, $state, $distance, $min, $max, $page);

				// Load all of the views
				$this->load->view('backend/hot', array('hot' => $hot, 'page' => $page)); 
			}
		}
	}