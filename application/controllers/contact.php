<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Contact extends CI_Controller {
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

				if($user_id) {
					$session = TRUE;
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');
					$username = $this->session->userdata('username');
				} else {
					$session = FALSE;
					$auth = NULL;
					$tinder_id = NULL;
					$username = NULL;
				}

				$link = FormatUserLink($tinder_id, $username);
				$pic = ChangePicSize($this->session->userdata('profile_pic'), 174);

				// Define the meta tags
				$meta_info = array('description' => "Contact Twinder. We'll get back as soon as we can.",
									'img' => 'http://twinder.io/public/img/logo.jpg',
									'url' => 'http://twinder.io/contact',
									'type' => 'article');

				// Set all of the info that needs to be passed to the header view
				$header = array('title' => 'Contact Us',
								'type' => 'article',
								'session' => $session,
								'header' => 'Contact Us',
								'auth' => $auth,
								'tinder_id' => $tinder_id,
								'name' => $this->session->userdata('first_name'),
								'meta' => $meta_info,
								'link' => $link,
								'pic' => $pic);

				// Get all of the data for the footer view
				$places = $this->loc->FooterPlaces();
				$users = $this->database->GetAllUsers(5);
				$footer = array('locations' => $places, 'users' => $users);

				// Load all of the views
				$this->load->view('templates/header', $header); 
				$this->load->view('contact'); 
				$this->load->view('templates/footer', $footer); 
			}

			public function Send() {
				// Load the email library
				$this->load->library('email');

				// Get the value of what was entered in the textarea field
				$msg = $this->input->post('msg');

				if(!empty($msg)) {
					$this->email->from('Twinder', 'contact@twinder.com'); 
					$this->email->to('lnlance09@gmail.com'); 
					$this->email->subject('Twinder Message');
					$this->email->message($msg);	
					$this->email->send();
					echo 'done';
				}
			}
		}
	}