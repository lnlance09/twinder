<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Search extends CI_Controller {
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
				// Get the query parameter from the URL
				$q = $this->input->get('q');
				$gender = $this->input->get('gender');
				$min = $this->input->get('min');
				$max = $this->input->get('max');

				// Get the results from the users model
				$users = $this->database_model->SearchUsers($q, 'both');

				// Find out if the user is logged in or not
				$user_id = $this->session->userdata('user_id');

				if(is_numeric($user_id)) {
					$session = TRUE;
					$tinder_id = $this->session->userdata('tinder_id');
					$auth = $this->session->userdata('token');

					// Get the like count
					$like_count = $this->database_model->GetLikeCount($tinder_id, FALSE);

					// Find out how many matches the user has
					$match_count = $this->database_model->GetMatches($tinder_id);

					// Get the pass count
					$pass_count = NULL;
				} else {
					$session = FALSE;
					$tinder_id = NULL;
					$auth = FALSE;
					$like_count = NULL;
					$match_count = NULL;
					$pass_count = NULL;
				}

				if($users['count'] == 1) {
					$term = 'result';
				} else {
					$term = 'results';
				}

				if($q == '') {
					$title = 'Find a user on Tinder';
					$description = '';
					$url = $this->base_url.'search';
				} else {
					$title = "Results for '".$q."'";
					$description = '';
					$url = $this->base_url.'search?q='.urlencode($q);
				}

				$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));

				// Define the meta tags
				$meta_info = array('description' => $description,
									'img' => $this->base_url.'public/img/',
									'url' => $url);

				// Set all of the info that needs to be passed to the header view
				$header_info = array('title' => $title,
									'session' => $session,
									'header' => '<span id="count_num">'.number_format($users['count'])."</span> ".$term,
									'auth' => $auth,
									'tinder_id' => $tinder_id,
									'first_name' => $this->session->userdata('first_name'),
									'last_name' => $this->session->userdata('last_name'),
									'like_count' => $like_count,
									'match_count' => $match_count['count'],
									'pass_count' => $pass_count,
									'q' => $q,
									'meta' => $meta_info,
									'profile_link' => $profile_link);

				// Set all of the info that needs to be passed to the dashboard view
				$body_info = array('count' => $users['count']);

				// Load all of the views
				$this->load->view('header', $header_info); 
				$this->load->view('search', $body_info); 
				$this->load->view('footer'); 
			}

			public function Autocomplete() {
				// Get the query parameter from the URL
				$q = $this->input->get('q');
				$gender = $this->input->get('gender');
				$min = $this->input->get('min');
				$max = $this->input->get('max');

				// Get the results from the users model
				$users = $this->database_model->SearchUsers($q, $gender);

				// Load the autocomplete view
				$this->load->view('backend/autocomplete', array('users' => $users, 'q' => $q));
			}

			public function Backend() {
				// Get the query parameter from the URL
				$q = $this->input->get('q');
				$gender = $this->input->get('gender');
				$page = $this->input->get('page');
				$min = $this->input->get('min');
				$max = $this->input->get('max');

				// Get the results from the users model
				$users = $this->database_model->SearchUsers($q, $gender, $min, $max);

				$data = array('users' => $users, 
							'q' => $q, 
							'page' => $page, 
							'gender' => $gender);

				// Load the autocomplete view
				$this->load->view('backend/search', $data);
			}
		}
	}