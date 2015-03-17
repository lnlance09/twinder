<?php 
	class Admin_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Set the base URL
			$this->base_url = $this->config->base_url();

			// Load the DB and the helper
			$this->load->database();
			$this->load->helper('common_helper');
		}

		public function Login() {

		}
	}