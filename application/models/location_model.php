<?php 
	class Location_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Define the API key for MapQuest
			$this->mapquest_key = 'Cmjtd|luur2108n1,7w=o5-gz8a';

			// Define the URL for the MapQuest API
			$this->mapquest_url = 'http://www.mapquestapi.com/geocoding/v1/address?';

			// Set the memory limit to unlimited
			ini_set('memory_limit', -1);
		}

		/**
		 * Query the DB to see if a row exists containing a given city/state combo
		 * @param {city} [city] The name of the city
		 * @param {state} [state] The name of the state. Can either be the full name or its abbreviation
		 * @return {int|boolean} The location ID from the DB or FALSE
		 */
		public function CheckCityAndState($city, $state) {
			$sql = "SELECT id FROM locations 
					WHERE city = ? AND (state = ? OR state_abbrev = ?)"; 
			$query = $this->db->query($sql, array($city, $state, $state));
			
			if($query->num_rows() == 1) { 
				foreach($query->result() as $row) {
					return $row->id;
				}
			} else {
				return FALSE;
			}
		}

		/**
		 * Query the DB to see if a row exists contaning the given state
		 * @param {string} [state] The name or two letter abbrevation of the state
		 * @return {int} The number of rows returned from the query
		 */
		public function CheckState($state) {
			$sql = "SELECT id 
					FROM locations 
					WHERE state = ? 
					OR state_abbrev = ?"; 
			$query = $this->db->query($sql, array($state, $state));
			return $query->num_rows();
		}

		/**
		 * Get a state's abbreviation from its full name
		 * @param {string} [key] The full name of the state
		 * @return {string} A two letter abbreviation of the state
		 */
		public function ConvertState($key) {
			$states = $this->States();
		    $array = (strlen($key) == 2 ? $states : array_flip($states));
		    $res = $array[strtoupper($key)];
		    return strtolower($res);
		}

		/**
		 * Get the full name of a state based upon it's abbreviation
		 * @param {string} [state] The two letter abbreviation of the state
		 * @return {string} The full name of the state from its two letter abbreviation
		 */
		public function FullFromAbbrev($state) {
			$states = $this->States();

			foreach($states as $key => $val) {
				if($key == $state) {
					return ucwords(strtolower($val));
					break;
				}
			}
		}

		/**
		 * Query the DB to get matching states from the autocomplete form
		 * @param {string} [state] The name of the state
		 * @return {array} An array containing the number of rows returned and the states
		 */
		public function GetStates($state) {
			$this->db->select('state, state_abbrev');
			$this->db->like('state', $state);
			$this->db->order_by('state', 'asc');
			$this->db->limit(5);
			$this->db->distinct();
			$query = $this->db->get('locations');
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('name' => $row->state, 'abbrev' => strtolower($row->state_abbrev));

				$i++;
			}

			return array('count' => $count, 'states' => $return);
		}

		/**
		 * Query the DB for cities in a given state that match the autocomplete form
		 * @param {string} [state] The full name of the state
		 * @param {string} [city] The name of the city
		 * @return {array} An array containing the number of rows and info about the cities
		 */
		public function GetCities($state, $city) {
			$this->db->select('city, lon, lat');
			$this->db->where('state', $state);
			$this->db->like('city', $city);
			$this->db->order_by('city', 'asc');
			$this->db->limit(5);
			$query = $this->db->get('locations');
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('name' => $row->city, 'lon' => $row->lon, 'lat' => $row->lat);

				$i++;
			}

			return array('count' => $count, 'cities' => $return);
		}

		/**
		 * Calculate the distance between two geographical locations in miles
		 * @param {decimal} [lat_from] The latitude coordinate of the first location
		 * @param {decimal} [lon_from] The longitude coordinate of the first location
		 * @param {decimal} [lat_to] The latitude coordinate of the second location
		 * @param {decimal} [lon_to] The longitude coordinate of the second location
		 * @return {int} The number of miles between two locations
		 */
		public function Haversine($lat_from, $lon_from, $lat_to, $lon_to) {
			$radius = 6371000;
			$delta_lat = deg2rad($lat_to-$lat_from);
			$delta_lon = deg2rad($lon_to-$lon_from);
			
			$a = sin($delta_lat/2) * sin($delta_lat/2) +
				cos(deg2rad($lat_from)) * cos(deg2rad($lat_to)) *
				sin($delta_lon/2) * sin($delta_lon/2);
			$c = 2*atan2(sqrt($a), sqrt(1-$a));

			// Convert the distance from meters to miles
			return ceil(($radius*$c)*0.000621371);
		}

		/**
		 * Make a request to MapQuest's API endpoing to get the lat & lon coordinates from the a city and/or state name/abbreviation
		 * @param {string} [city] The name of the city
		 * @param {string} [state] The name of the state
		 * @return {array} An array containing the results from MapQuest's API
		 */
		public function MapquestLocation($city, $state) {
			// Define the parameter
			$param = (!empty($city) && !empty($city) ? $city.','.$state : $state);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->mapquest_url.'location='.urlencode($param).'&key='.$this->mapquest_key);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$data = curl_exec($ch);
		    curl_close($ch);

		    // Decode the response
		    $decode = @json_decode($data, TRUE);
		    
		    if($decode['info']['statuscode'] == 400) {
		    	return array('lat' => NULL, 'lng' => NULL);
		    } else {
				return $decode['results'][0]['locations'][0]['latLng'];
			}
		}

		/**
		 * Make a request to MapQuest's API endpoing to get the name of the city and the name/abbreviation of the state from lon & lat coordinates
		 * @param {decimal} [lat] The latitude coordinate
		 * @param {decimal} [lon] The longitude coordinate
		 * @return {array} An array containing the country, city and state of a location
		 */
		public function MapquestLatLon($lat, $lon) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->mapquest_url.'location='.urlencode($lat.','.$lon).'&key='.$this->mapquest_key);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$data = curl_exec($ch);
		    curl_close($ch);

		    // Decode the response
		    $decode = @json_decode($data, TRUE);
		    $location = $decode['results'][0]['locations'][0];

		    return array('country' => $location['adminArea1'],
		    			'city' => $location['adminArea5'], 
		    			'state' => $location['adminArea3'],
		    			'full_name' => $this->FullFromAbbrev($location['adminArea3']));
		}

		/**
		 * Query the DB to get a random array of locations
		 * @return An array containing random locations
		 */
		public function RandomLocations($limit = NULL) {
			$this->db->select('city, state_abbrev');

			if($limit) {
				$this->db->limit($limit);
			}

			$query = $this->db->get('locations');
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('city' => $row->city, 'state' => $row->state_abbrev);

				$i++;
			}

			shuffle($return);
			return $return;
		}

		public function FooterPlaces() {
			$sql = "SELECT city, state_abbrev
					FROM locations 
					WHERE id > ?
					GROUP BY state
					LIMIT 5";
			$query = $this->db->query($sql, array(mt_rand(95867, 125976)));
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('city' => $row->city, 'state' => $row->state_abbrev);

				$i++;
			}

			return $return;
		}

		/**
		 * Return an array containing all 50 states
		 * @return {array} An array containing all 50 states
		 */
		public function States() {
			return array('AL' => 'ALABAMA',
						'AK' => 'ALASKA',
						'AZ' => 'ARIZONA',
						'AR' => 'ARKANSAS',
						'CA' => 'CALIFORNIA',
						'CO' => 'COLORADO',
						'CT' => 'CONNECTICUT',
						'DE' => 'DELAWARE',
						'FL' => 'FLORIDA',
						'GA' => 'GEORGIA',
						'HI' => 'HAWAII',
						'ID' => 'IDAHO',
						'IL' => 'ILLINOIS',
						'IN' => 'INDIANA',
						'IA' => 'IOWA',
						'KS' => 'KANSAS',
						'KY' => 'KENTUCKY',
						'LA' => 'LOUISIANA',
						'ME' => 'MAINE',
						'MD' => 'MARYLAND',
						'MA' => 'MASSACHUSETTS',
						'MI' => 'MICHIGAN',
						'MN' => 'MINNESOTA',
						'MS' => 'MISSISSIPPI',
						'MO' => 'MISSOURI',
						'MT' => 'MONTANA',
						'NE' => 'NEBRASKA',
						'NV' => 'NEVADA',
						'NH' => 'NEW HAMPSHIRE',
						'NJ' => 'NEW JERSEY',
						'NM' => 'NEW MEXICO',
						'NY' => 'NEW YORK',
						'NC' => 'NORTH CAROLINA',
						'ND' => 'NORTH DAKOTA',
						'OH' => 'OHIO',
						'OK' => 'OKLAHOMA',
						'OR' => 'OREGON',
						'PA' => 'PENNSYLVANIA',
						'PR' => 'PUERTO RICO',
						'RI' => 'RHODE ISLAND',
						'SC' => 'SOUTH CAROLINA',
						'SD' => 'SOUTH DAKOTA',
						'TN' => 'TENNESSEE',
						'TX' => 'TEXAS',
						'UT' => 'UTAH',
						'VT' => 'VERMONT',
						'VA' => 'VIRGINIA',
						'WA' => 'WASHINGTON',
						'DC' => 'WASHINGTON D.C.',
						'WV' => 'WEST VIRGINIA',
						'WI' => 'WISCONSIN',
						'WY' => 'WYOMING');
		}

		/**
		 * Validate either a latitude or longitude coordinate using regex
		 * @param {decimal} [coordinate] The coordinate to be tested
		 * @return {boolean} 
		 */
		public function ValidateCoordinate($coordinate) {
			return preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $coordinate);
		}
	}