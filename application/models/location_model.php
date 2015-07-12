<?php 
	class Location_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Define the API key for MapQuest
			$this->mapquest_key = 'Cmjtd|luur2108n1,7w=o5-gz8a';

			// Define the URL for the MapQuest API
			$this->mapquest_url = 'http://www.mapquestapi.com/geocoding/v1/address?';
		}

		/**
		 * Query the DB to see if a row exists containing a given city/state combo
		 * @param {city} [city] The name of the city
		 * @param {state} [state] The name of the state. Can either be the full name or its abbreviation
		 * @return {int|boolean} The location ID from the DB or FALSE
		 */
		public function CheckCityAndState($city, $state) {
			$sql = "SELECT COUNT(*) AS count FROM locations 
					WHERE city = ? AND (state = ? OR state_abbrev = ?)"; 
			$query = $this->db->query($sql, array($city, $state, $state));
			$result = $query->result();
			return ($result[0]->count == 1 ? TRUE : FALSE);
		}

		/**
		 * Query the DB to see if a row exists contaning the given state
		 * @param {string} [state] The name or two letter abbrevation of the state
		 * @return {int} The number of rows returned from the query
		 */
		public function CheckState($state) {
			$sql = "SELECT COUNT(*) AS count
					FROM locations 
					WHERE state = ? 
					OR state_abbrev = ?"; 
			$query = $this->db->query($sql, array($state, $state))->result();
			return $query[0]->count;
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
		 * Return a semi-random list of locations for the footer
		 * @return {array} An array containing 5 locations
		 */
		public function FooterPlaces() {
			$this->db->select('city, state, lat, lon');
			$this->db->order_by('id', 'RANDOM');
			$this->db->limit(5);
			$query = $this->db->get('locations');
			$return = [];
			$i = 0;

			foreach($query->result() as $row) {
				$return[$i] = array('city' => $row->city, 
									'state' => $row->state,
									'lat' => $row->lat,
									'lon' => $row->lon);

				$i++;
			}

			return $return;
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
			$i = 0;
			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('name' => $row->city, 'lon' => $row->lon, 'lat' => $row->lat);

				$i++;
			}

			return array('count' => $query->num_rows(), 'cities' => $return);
		}

		/**
		 * Find places that are close to a given place
		 * @param {decimal} [lon] The longitude coordinate
		 * @param {decimal} [lat] The latitude coordinate
		 * @return {array} An array containing info about the list of places
		 */
		public function GetCloseBy($lon, $lat) {
			$sql = "SELECT lon, lat, city, state,
					(3959 * acos(cos(radians(".$lat.")) * cos(radians(last_seen.lat)) * cos(radians(last_seen.lon) - radians(".$lon.")) + sin(radians(".$lat.")) * sin(radians(last_seen.lat)))) AS distance
					FROM last_seen
					GROUP BY city, state
					ORDER BY distance ASC
					LIMIT 5";
			$query = $this->db->query($sql);
			$data = [];
			$i = 0;

			foreach($query->result() as $row) {
				if(strlen($row->state) == 2) {
					$flag = ($row->state == 'SP' ? 'Brazil' : 'United States');
				} else {
					$flag = $row->state;
				}

				$data[$i] = array('lon' => $row->lon,
								'lat' => $row->lat,
								'city' => $row->city,
								'state' => $row->state,
								'distance' => ceil($row->distance),
								'flag' => str_replace(' ', '-', $flag));
				$i++;
			}

			return array('count' => $query->num_rows(), 'places' => $data);
		}

		/**
		 * Query the DB to get matching states from the autocomplete form
		 * @param {string} [q] The query string
		 * @return {array} An array containing the number of rows returned and the cities and states
		 */
		public function GetLocations($q) {
			$exp = explode(',', $q);
			$this->db->select('city, state');

			if(count($exp) > 1) {
				$this->db->like('city', trim($exp[0]));
				$this->db->like('state', trim(end($exp)));
			} else {
				$this->db->like('city', $q);
			}

			$this->db->order_by('city', 'ASC');
			$this->db->limit(5);
			$this->db->group_by('city, state');
			$query = $this->db->get('last_seen');
			$i = 0;
			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('city' => $row->city, 'state' => $row->state);

				$i++;
			}

			return array('count' => $query->num_rows(), 'places' => $return);
		}

		/**
		 * Query the DB to get matching states from the autocomplete form
		 * @param {string} [state] The name of the state
		 * @return {array} An array containing the number of rows returned and the states
		 */
		public function GetStates($state) {
			$this->db->distinct();
			$this->db->select('state, state_abbrev');
			$this->db->like('state', $state);
			$this->db->order_by('state', 'asc');
			$this->db->limit(5);
			$query = $this->db->get('locations');
			$i = 0;
			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('name' => $row->state, 'abbrev' => strtolower($row->state_abbrev));

				$i++;
			}

			return array('count' => $query->num_rows(), 'states' => $return);
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
			$delta_lat = deg2rad($lat_to-$lat_from);
			$delta_lon = deg2rad($lon_to-$lon_from);
			$a = sin($delta_lat/2) * sin($delta_lat/2) + cos(deg2rad($lat_from)) * cos(deg2rad($lat_to)) * sin($delta_lon/2) * sin($delta_lon/2);
			$c = 2*atan2(sqrt($a), sqrt(1-$a));
			return ceil((6371000*$c)*0.000621371);
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
		    
		    $decode = @json_decode($data, TRUE);
		    return ($decode['info']['statuscode'] == 400 ? FALSE : $decode['results'][0]['locations'][0]);
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
		    
		    $decode = @json_decode($data, TRUE);
		    $loc = $decode['results'][0]['locations'][0];
		    // return($loc);
		    return array('country' => $loc['adminArea1'],
		    			'city' => $loc['adminArea5'], 
		    			'state' => $loc['adminArea3'],
		    			'full_name' => $this->FullFromAbbrev($loc['adminArea3']));
		}

		/**
		 * Query the DB to get a random array of locations
		 * @return An array containing random locations
		 */
		public function SeoLocations() {
			$sql = "SELECT lat, lon
					FROM last_seen
					GROUP BY lat, lon";
			$query = $this->db->query($sql);
			$data = [];
			$i = 0;

			foreach($query->result() as $row) {
				$data[$i] = array('lat' => $row->lat, 'lon' => $row->lon);
				
				$i++;
			}

			return array('count' => $query->num_rows(), 'data' => $data);
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
	}