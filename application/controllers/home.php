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
				$this->load->model('users_model');
			}

			public function Index() {
				// Get the user ID
				$user_id = $this->session->userdata('user_id');
				//echo $user_id;
				
				if(is_numeric($user_id)) {
					header('Location: '.$this->base_url.'users/'.$this->session->userdata('tinder_id'));
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
					$body_info = array();

					// Load all of the views
					$this->load->view('header', $header_info); 
					$this->load->view('main'); 
					$this->load->view('footer'); 
				}
			}

			public function LocationNameFromCoords() {
				$lon = $this->input->get('lon');
				$lat = $this->input->get('lat');
				$geo = GeoLocation($lon, $lat);

				FormatArray($geo);
				$city = $geo['results'][3]['formatted_address'];
				//$state = $geo['results'][4]['address_components'][0]['short_name'];
				//echo $city;
			}
		}
	}