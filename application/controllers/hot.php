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
				// Get the page from the URL
				$page = $this->uri->segment(2, 0);

				// Get the user ID
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					$session = TRUE;
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');

					// Get the user's like count
					$like_count = $this->database_model->GetLikeCount($tinder_id, FALSE);

					// Find out how many matches the user has
					$match_count = $this->database_model->GetMatches($tinder_id);

					// Get the pass count
					$pass_count = NULL;

					// Get the distance filter
					$distance = $this->session->userdata('distance_filter');
				} else {
					$session = FALSE;
					$auth = NULL;
					$tinder_id = NULL;
					$like_count = NULL;
					$match_count = NULL;
					$pass_count = NULL;
					$distance = 50;
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
									'match_count' => $match_count['count'],
									'first_name' => $this->session->userdata('first_name'),
									'last_name' => $this->session->userdata('last_name'),
									'meta' => $meta_info,
									'profile_link' => $profile_link);

				// Define the body info
				$body_info = array('distance' => $distance,
									'meters' => MilesToMeters($distance),
									'page' => $page);

				// Load all of the views
				$this->load->view('header', $header_info); 
				$this->load->view('hot', $body_info); 
				$this->load->view('footer'); 
			}

			public function GetHottest() {
				// Get the page from the URL
				$page = $this->input->get('page');
				$gender = $this->input->get('gender');

				// Get all of the hottest users
				$hot = $this->database_model->GetHottest();
				// FormatArray($hot);
				// die;

				// Load all of the views
				$this->load->view('backend/hot', array('hot' => $hot, 'page' => $page)); 
			}
		}
	}