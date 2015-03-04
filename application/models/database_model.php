<?php 
	class Database_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Load the database
			$this->load->database();

			// Load the helpers file
			$this->load->helper('common_helper');
		}

		/**
		 * Clear the database
		 */
		public function FlushDB() {
			// Flush all of the DBs
			$tinder_id = '5495df819983685e07f138f2';

			$this->db->where('id > "0"');
			$this->db->delete(array('batches', 'last_seen', 'likes', 'passes', 'pics', 'pings', 'reports', 'settings')); 

			$this->db->where('tinder_id != "0"');
			$this->db->delete('users'); 
		}

		/**
		 * Get the user that is next in line to be either liked or passed
		 * @param {int} id The user ID of the Tinder user 
		 */
		public function GetBatchUser($id) {
			$this->db->select('tinder_id');
			$this->db->where('user_id', $id);
			$this->db->limit(1);
			$query = $this->db->get('batches');

			foreach($query->result() as $row) {
				$tinder_id = $row->tinder_id;
			}

			return $tinder_id;
		}

		/**
		 * Insert a batch of new users from the discovery process into the DB
		 * @param {int} [user_id] The user ID of the user who is logged in
		 * @param {string} [my_tinder_id] The tinder ID of the user who is logged in
		 * @param {array} [info] An array of users that was obtained from Tinder's API
		 * @param {decimal} [lon] The longitude coordinate of the user who is logged in
		 * @param {decimal} [lat] The latitude coordinate of the user who is logged in
		 */
		public function InsertBatch($user_id, $my_tinder_id, $info, $lon, $lat) {
			for($i=0;$i<count($info);$i++) {
				$this->db->select('id');
				$this->db->where(array('tinder_id' => $info[$i]['tinder_id'], 'user_id' => $user_id));
				$query = $this->db->get('batches');
				
				// Check to see if there is a record of the user existing in the users table
				$data = array('tinder_id' => $info[$i]['tinder_id'],
							'first_name' => $info[$i]['name'],
							'age' => $info[$i]['age'],
							'dob' => date('M j, Y', strtotime($info[$i]['birth_date'])),
							'gender' => $info[$i]['gender'],
							'bio' => $info[$i]['bio'],
							'profile_pic' => $info[$i]['profile_pic']);
				// If there isn't, then put one in
				$this->InsertUser($data);

				// Check to see if there is a record of the user existing in the batches table
				if($query->num_rows() == 0) {
					$data = array('user_id' => $user_id, 'tinder_id' => $info[$i]['tinder_id']);
					$this->db->insert('batches', $data);
				}

				// Update the last seen location
				$this->EditLastSeen($info[$i]['distance'], $my_tinder_id, $info[$i]['tinder_id'], $lon, $lat);

				// Insert the user's pics
				$this->InsertPics($info[$i]['tinder_id'], $info[$i]['pics']);	
			}
		}

		/**
		 * Remove a batch user from the batches table
		 * @param {string} [id] The Tinder ID of the user who is to be removed
		 * @param {string} [my_id] The user ID of the user who is currently logged in
		 */
		public function RemoveBatchUser($id, $my_id) {
			$this->db->delete('batches', array('tinder_id' => $id, 'user_id' => $my_id)); 
		}

		/**
		 * Remove all batch users from the DB
		 * @param {int} [id] The user ID of the user who is currently logged in
		 */
		public function RemoveAllBatch($id) {
			$this->db->delete('batches', array('user_id' => $id)); 
		}


		/**
		 * Sync all of the user's messages from Tinder with WeTinder.
		 * This function also records matches between the user logging in and other Tinder users.
		 * A new row will be inserted/updated into the users table for each person in the updates array
		 * @param {array} [updates] An array that was fetched from Tinder's API containing all of the 
		 * user's matches since their Tinder account was created
		 * @param {string} [my_tinder_id] The Tinder ID of the user who is logged in
		 * @param {int} [distance] The distance in miles
		 * @param {decimal} [lon] The longitude coordinate of the user who is currently logged in
		 * @param {decimal} [lat] The latitude coordinate of the user who is currently logged in
		 * @param {string} [city] The city of the user who is currently logged in
		 * @param {string} [state] The state of the user who is currently logged in
		 */
		public function SyncMessages($updates, $my_tinder_id, $distance, $lon, $lat, $city, $state) {
			for($i=0;$i<count($updates);$i++) {
				// Get the match ID for each
				$match_id = $updates[$i]['_id'];

				// Get the time that the match was created
				if(array_key_exists('created_date', $updates[$i])) {
					$created_at = $updates[$i]['created_date'];
				} else {
					$created_at = NULL;
				}

				// Get the Tinder ID of the other person involed in the match
				if(array_key_exists('person', $updates[$i])) {
					$person = $updates[$i]['person'];

					// Check to see if there is a record of each match participant in the DB
					if($person !== NULL) {
						// Insert a row into the users table if necessary
						$user_data = array('tinder_id' => $person['_id'],
										'first_name' => $person['name'],
										'dob' => date('M j, Y', strtotime($person['birth_date'])),
										'age' => ReturnAge($person['birth_date']),
										'bio' => $person['bio'],
										'gender' => $person['gender'],
										'last_activity_date' => $person['ping_time'],
										'profile_pic' => ReturnProfilePic($person['photos']));
						// FormatArray($user_data);
						$this->InsertUser($user_data);

						// Insert each user's pics
						$this->InsertPics($person['_id'], ReturnPicsArray($person['photos']));
						
						// Define the data that will be used for the insert query
						$data = array('match_id' => $match_id,
									'user_one' => $my_tinder_id,
									'user_two' => $person['_id'],
									'datetime' => $created_at);
						$this->InsertIntoLikes($my_tinder_id, $person['_id'], $match_id);

						// Check to see if each user has a row existing in the last_seen table
						$last = $this->GetLastSeen($person['_id']);

						// If there is a record of the user existing, then see if your distance to him/her is closer 
						if($last === FALSE) {
							$data = array('seen_id' => $person['_id'],
										'seen_by_id' => $my_tinder_id,
										'lon' => $lon,
										'lat' => $lat,
										'city' => $city,
										'state' => $state,
										'miles_away' => $distance,
										'datetime' => date('Y-m-d H:i:s'));
							$this->CreateLastSeen($data);
						}
					}
				}		
			}
		}


		/**
		 * Return the data from the last seen table belonging to a given user
		 * @param {string} [tinder_id] The Tinder ID of the user who is being targetted
		 */
		public function GetLastSeen($tinder_id) {
			$this->db->select('*');
			$this->db->where('seen_id', $tinder_id);
			$query = $this->db->get('last_seen');

			if($query->num_rows() == 1) {
				foreach($query->result() as $row) {
					$seen_id = $row->seen_id;
					$by_id = $row->seen_by_id;
					$lon = $row->lon;
					$lat = $row->lat;
					$city = $row->city;
					$state = $row->state;
					$miles = $row->miles_away;
					$datetime = $row->datetime;
				}

				return array('data' => array('seen_id' => $seen_id,
											'seen_by_id' => $by_id,
											'lon' => $lon,
											'lat' => $lat,
											'city' => $city,
											'state' => $state,
											'miles_away' => $miles,
											'datetime' => $datetime));
			} else {
				return FALSE;
			}
		}

		/**
		 * Update/Insert row into last_seen table based upon what's currently in that table for a given user
		 * @param {int} [my_distance] The distance in miles between the user who is logged in and the user defined as 'his_tinder_id'
		 * @param {string} [my_tinder_id] The Tinder ID of the user who is currently logged in
		 * @param {string} [his_tinder_id] The Tinder ID of the other user
		 * @param {decimal} [lon] The longitude coordinate of the user who is logged in 
		 * @param {decimal} [lat] The latitude coordinate of the user who is logged in 
		 */
		public function EditLastSeen($my_distance, $my_tinder_id, $his_tinder_id, $lon, $lat) {
			// Check to see if each user has a row existing in the last_seen table
			$last = $this->GetLastSeen($his_tinder_id);
			// FormatArray($last);

			// If there is a record of the user existing, then see if your distance to him/her is closer 
			if($last) {
				// Get the latitude and longitude coordinates
				$loc = $this->location_model->MapquestLatLon($lat, $lon);

				// In this case, the user is updating his own location
				if($his_tinder_id == $my_tinder_id) {
					$data = array('seen_id' => $my_tinder_id,
								'seen_by_id' => $my_tinder_id,
								'lon' => $lon,
								'lat' => $lat,
								'city' => $loc['city'],
								'state' => $loc['state'],
								'miles_away' => $my_distance,
								'datetime' => date('Y-m-d H:i:s'));
					$this->UpdateLastSeen($my_tinder_id, $data);
					$return_data['data'] = $data;
				} else {
					// Make sure the user's last location isn't one from a ping
					if($last['data']['seen_id'] != $last['data']['seen_by_id']) {
						// Check to see if your proximity is closer than the one currently on record
						if($my_distance < $last['data']['miles_away']) {
							// Get the latitude and longitude coordinates
							$loc = $this->location_model->MapquestLatLon($lat, $lon);

							$data = array('seen_id' => $his_tinder_id,
										'seen_by_id' => $my_tinder_id,
										'lon' => $lon,
										'lat' => $lat,
										'city' => $loc['city'],
										'state' => $loc['state'],
										'miles_away' => $my_distance,
										'datetime' => date('Y-m-d H:i:s'));
							$this->UpdateLastSeen($his_tinder_id, $data);
							$return_data['data'] = $data;
						} else {
							$return_data = $last;
						}
					} else {
						$return_data = $last;
					}
				}
			} else {
				// Get the latitude and longitude coordinates
				$loc = $this->location_model->MapquestLatLon($lat, $lon);
				// FormatArray($loc);

				// Create a new row in the last_seen table for this user
				$data = array('seen_id' => $his_tinder_id,
							'seen_by_id' => $my_tinder_id,
							'lon' => $lon,
							'lat' => $lat,
							'city' => $loc['city'],
							'state' => $loc['state'],
							'miles_away' => $my_distance,
							'datetime' => date('Y-m-d H:i:s'));

				if($his_tinder_id == $my_tinder_id) {
					$data['seen_id'] = $my_tinder_id;
				}

				$this->CreateLastSeen($data);
				$return_data['data'] = $data;
			}

			// FormatArray($return_data);
			// Get info about the person who saw this user
			$user = $this->GetUserInfo($return_data['data']['seen_by_id']);

			return array('user' => $user, $return_data);
		}

		/**
		 * Update a row existing in the last_seen table belonging to a user with the given Tinder ID
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {array} [data] The columans and values for the query
		 */
		public function UpdateLastSeen($tinder_id, $data) {
			$this->db->where('seen_id', $tinder_id);
			$this->db->update('last_seen', $data);
		}

		/**
		 * Create a row in the last_seen table
		 * @param {array} [data] The values for the columns
		 */
		public function CreateLastSeen($data) {
			$this->db->insert('last_seen', $data);
		}

		/**
		 * Get the number of likes a given user has or the number of users that like a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the user's like count or liked_by count. TRUE for liked_by count. FALSE for like_count
		 * @param {string} [q] The query string to search the user's first name
		 */
		public function GetLikeCount($tinder_id, $inverse, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes";

			if($inverse) {
				$sql .= " ON users.tinder_id = likes.user_one WHERE likes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = likes.user_two WHERE likes.user_one = ?";
			}

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
			return $query->num_rows();
		}

		/**
		 * Return an array containing all of the info about each of a given user's likes or liked by
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the user's like count or liked_by count. TRUE for liked_by count. FALSE for like_count
		 * @param {string} [q] The query string to search the user's first name
		 */
		public function GetLikes($tinder_id, $inverse, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes";

			if($inverse) {
				$sql .= " ON users.tinder_id = likes.user_one WHERE likes.user_two = '".$this->db->escape($tinder_id)."'";
			} else {
				$sql .= " ON users.tinder_id = likes.user_two WHERE likes.user_one = '".$this->db->escape($tinder_id)."'";
			}

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE '%".trim($q)."%'";
			}

			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				// Columns from the passes table
				$id[$i] = $row->id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$match_id[$i] = $row->match_id;
				$unmatched[$i] = $row->unmatched;
				$datetime[$i] = $row->datetime;

				// Columns from the users table
				$p_tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $p_tinder_id[$i],
									'first_name' => $name[$i],
									'username' => $username[$i],
									'age' => $age[$i],
									'profile_pic' => $profile_pic[$i],
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				// Find out if there is a mutual like between the two users
				if($match_id[$i] != '') {
					$match = TRUE;
				} else {
					$match = FALSE;
				}

				$return[$i] = array('id' => $id[$i],
									'like' => $user_two[$i],
									'datetime' => $datetime[$i],
									'match' => $match,
									'user_info' => $user_info);
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Update/Insert a row into the likes table depending on if one currently exists
		 * @param {string} [my_id] The Tinder ID of the user who is currently logged in
		 * @param {string} [tinder_id] The Tinder ID of the user who is being liked
		 * @param {string} [match_id] The match ID of the liking
		 */
		public function InsertIntoLikes($my_id, $tinder_id, $match_id) {
			$liked = $this->SeeIfLiked($my_id, $tinder_id, FALSE);

			// Check to see if I have already liked this user
			if($liked == 0) {
				// Check to see if this user has liked me
				$liked = $this->SeeIfLiked($my_id, $tinder_id, TRUE);

				// If the user has already liked my profile, then update that row with the match ID
				if($liked == 1) {
					$data = array('match_id' => $match_id);
					$this->db->where(array('user_one' => $tinder_id, 'user_two' => $my_id));
					$this->db->update('likes', $data);
				} else {
					// If there is no record, then create one
					$data = array('user_one' => $tinder_id,
								'user_two' => $my_id,
								'match_id' => $match_id,
								'datetime' => date('Y-m-d H:i:s'));
					$this->db->insert('likes', $data);
				}

				// If there is no record, then create one
				$data = array('user_one' => $my_id,
							'user_two' => $tinder_id,
							'match_id' => $match_id,
							'datetime' => date('Y-m-d H:i:s'));
				$this->db->insert('likes', $data);
			}
		}

		/**
		 * See if one user has already liked another user on WeTinder
		 * @param {string} [my_id] The Tinder ID of the user is currently logged in
		 * @param {string} [tinder_id] The Tinder ID of the user who is being liked
		 * @param {boolean} [inverse] Whether or not to do the opposite. 
		 */
		public function SeeIfLiked($my_id, $tinder_id, $inverse) {
			$this->db->select('match_id');

			if($inverse) {
				$this->db->where(array('user_one' => $my_id, 'user_two' => $tinder_id));
			} else {
				$this->db->where(array('user_one' => $tinder_id, 'user_two' => $my_id));
			}

			$query = $this->db->get('likes');
			return $query->num_rows();
		}

		/**
		 * Return a number representing that two users have both liked
		 * @param {string} [my_id] The Tinder ID of the user is currently logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match users' first names with
		 */
		public function GetMutualLikeCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE (likes.user_two = ? OR likes.user_two = ?)";

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			return $query->num_rows();
		}

		/**
		 * Return an array containing the users that two users have both liked
		 * @param {string} [my_id] The Tinder ID of the user is currently logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match users' first names with
		 */
		public function GetMutualLikes($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE (likes.user_two = ? OR likes.user_two = ?)";

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				// Columns from the likes table
				$id[$i] = $row->id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$match_id[$i] = $row->match_id;
				$unmatched[$i] = $row->unmatched;
				$datetime[$i] = $row->datetime;

				// Columns from the users table
				$p_tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $p_tinder_id[$i],
									'first_name' => $name[$i],
									'username' => $username[$i],
									'age' => $age[$i],
									'profile_pic' => $profile_pic[$i],
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				// Find out if there is a mutual like between the two users
				if($match_id[$i] != '') {
					$match = TRUE;
				} else {
					$match = FALSE;
				}

				$return[$i] = array('id' => $id[$i],
									'like' => $user_two[$i],
									'datetime' => $datetime[$i],
									'match' => $match,
									'user_info' => $user_info);
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Get the number of matches that a given user has
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [q] The query string to match the user's first name with
		 */
		public function GetMatchCount($tinder_id, $q = NULL) {
			$sql = "SELECT likes.id, users.id
					FROM likes
					JOIN users
					ON likes.user_two = users.tinder_id
					WHERE likes.match_id != '0' 
					AND (likes.user_two = '".$tinder_id."')";

			if($q !== NULL) {
				$sql .= " AND users.first_name LIKE '%".trim($q)."%'";
			}

			$query = $this->db->query($sql);
			return $query->num_rows();
		}

		/**
		 * Return an array containing all of the users that have matched with a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [q] The query string to match the user's first name with
		 */
		public function GetMatches($tinder_id, $q = NULL) {
			$sql = "SELECT likes.*, users.*
					FROM likes
					JOIN users
					ON likes.user_two = users.tinder_id
					WHERE likes.match_id != ?
					AND likes.user_one = ?";

			if($q !== NULL) {
				$sql .= " AND users.first_name LIKE ?";
			}
			
			$query = $this->db->query($sql, array(0, $tinder_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				// Columns from the matches table
				$match_id[$i] = $row->match_id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$datetime[$i] = $row->datetime;
				$unmatched[$i] = $row->unmatched;

				// Columns from the users table
				$p_tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $p_tinder_id[$i],
									'first_name' => $name[$i],
									'username' => $username[$i],
									'age' => $age[$i],
									'profile_pic' => $profile_pic[$i],
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				$return[$i] = array('id' => $match_id[$i],
									'like' => $user_two[$i],
									'datetime' => $datetime[$i],
									'user_info' => $user_info);
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Get the number of matches that two user have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the user's first name with
		 */
		public function GetMutualMatchCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE (likes.user_one = ? OR likes.user_two = ?)";

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			return $query->num_rows();
		}	

		/**
		 * Return an array contaning the users that two user have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the user's first name with
		 */
		public function GetMutualMatches($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE (likes.user_one = ? OR likes.user_two = ?)";

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				// Columns from the likes table
				$id[$i] = $row->id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$match_id[$i] = $row->match_id;
				$unmatched[$i] = $row->unmatched;
				$datetime[$i] = $row->datetime;

				// Columns from the users table
				$p_tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $p_tinder_id[$i],
									'first_name' => $name[$i],
									'username' => $username[$i],
									'age' => $age[$i],
									'profile_pic' => $profile_pic[$i],
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				// Find out if there is a mutual like between the two users
				if($match_id[$i] != '') {
					$match = TRUE;
				} else {
					$match = FALSE;
				}

				$return[$i] = array('id' => $id[$i],
									'like' => $user_two[$i],
									'datetime' => $datetime[$i],
									'match' => $match,
									'user_info' => $user_info);
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Query the DB to see if a match with a given match ID exists
		 * @param {string} [match_id] The match ID being targetted
		 */
		public function MatchExists($match_id) {
			$this->db->select('id');
			$this->db->where('match_id', $match_id);
			$query = $this->db->get('likes');
			return $query->num_rows();
		}

		/**
		 * Insert a match into the DB if a row doesn't already exist in there with the same match ID
		 * @param {string} [match_id] The match ID being targetted
		 * @param {array} [data] An array containing the values for the columns
		 */
		public function InsertMatch($match_id, $data) {
			if($this->MatchExists($match_id) == 0) {
				$this->db->insert('likes', $data);
			}
		}

		/**
		 * Querty the DB to get info about a given match
		 * @param {string} [id] The match ID being targetted
		 */
		public function GetMatchInfo($id) {
			$this->db->select('user_one, user_two');
			$query = $this->db->where('match_id', $id);
			$query = $this->db->get('likes');
			
			if($query->num_rows() == 1) {
				foreach($query->result() as $row) {
					$user_one = $row->user_one;
					$user_two = $row->user_two;
				}

				return array('user_one' => $user_one, 'user_two' => $user_two);
			} else {
				return FALSE;
			} 
		}

		/**
		 * Insert a row into the passes table
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [tinder_id] The Tinder ID of the user who is being passes
		 */
		public function InsertIntoPasses($my_id, $tinder_id) {
			$data = array('user_one' => $my_id,
						'user_two' => $tinder_id,
						'datetime' => date('Y-m-d H:i:s'));
			$query = $this->db->insert('passes', $data);
		}

		/**
		 * Get the number of passes that a given user has gotten or the number of users that have passed a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the number of passes that a given user has gotten. 
		 * @param {string} [q] The query string to match the users' first names with
		 */
		public function GetPassCount($tinder_id, $inverse, $q = NULL) {
			$sql = "SELECT users.*, passes.*
					FROM users
					JOIN passes";

			if($inverse) {
				$sql .= " ON users.tinder_id = passes.user_one WHERE passes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = passes.user_two WHERE passes.user_one = ?";
			}

			if($q !== NULL
			&& $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
			return $query->num_rows();
		}

		/**
		 * Return an array containing the users that have passed a given user or the inverse
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the number of passes that a given user has gotten. 
		 * @param {string} [q] The query string to match the users' first names with
		 */
		public function GetPasses($tinder_id, $inverse, $q = NULL) {
			$sql = "SELECT users.*, passes.*
					FROM users
					JOIN passes";

			if($inverse) {
				$sql .= " ON users.tinder_id = passes.user_one WHERE passes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = passes.user_two WHERE passes.user_one = ?";
			}

			if($q !== NULL
			&& $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				// Columns from the passes table
				$id[$i] = $row->id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$datetime[$i] = $row->datetime;

				// Columns from the users table
				$p_tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $p_tinder_id[$i],
									'first_name' => $name[$i],
									'username' => $username[$i],
									'age' => $age[$i],
									'profile_pic' => $profile_pic[$i],
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				$return[$i] = array('id' => $id[$i],
									'like' => $user_two[$i],
									'datetime' => $datetime[$i],
									'user_info' => $user_info);
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Get the number of passes that two users have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the users' first names with
		 */
		public function GetMutualPassCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, passes.*
					FROM users
					JOIN passes
					ON users.tinder_id = passes.user_one
					WHERE (passes.user_two = ? OR passes.user_two = ?)";

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			return $query->num_rows();
		}

		/**
		 * Return an array containing the passes that two users have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the users' first names with
		 */
		public function GetMutualPasses($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, passes.*
					FROM users
					JOIN passes
					ON users.tinder_id = passes.user_one
					WHERE (passes.user_two = ? OR passes.user_two = ?)";

			if($q !== NULL && $q != '') {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				// Columns from the passes table
				$id[$i] = $row->id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$datetime[$i] = $row->datetime;

				// Columns from the users table
				$p_tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $p_tinder_id[$i],
									'first_name' => $name[$i],
									'username' => $username[$i],
									'age' => $age[$i],
									'profile_pic' => $profile_pic[$i],
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				$return[$i] = array('id' => $id[$i],
									'pass' => $user_two[$i],
									'datetime' => $datetime[$i],
									'user_info' => $user_info);
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Insert a user's picture into the pics table
		 * @param {string} [id] The Tinder ID of the user whose pics are being inserted
		 * @param {array} [pics] An array containing all the user's pictures
		 */
		public function InsertPics($id, $pics) {
			for($i=0;$i<count($pics);$i++) {
				$this->db->select('tinder_id');
				$this->db->where(array('tinder_id' => $id, 'filename' => $pics[$i]));
				$query = $this->db->get('pics');

				if($query->num_rows() == 0) {
					$this->db->insert('pics', array('tinder_id' => $id, 'filename' => $pics[$i], 'pic_order' => $i));
				}
			}
		}

		/**
		 * Insert a row into the pings table
		 * @param {decimal} [lon] The longitude coordinate
		 * @param {decimal} [lat] The latitude coordinate
		 * @param {string} [tinder_id] The Tinder ID of the user who is pinging
		 */
		public function InsertPing($lon, $lat, $tinder_id) {
			// Call the Google Maps function to find out the city, state and country
			$location = $this->location_model->MapquestLatLon($lat, $lon);
			$results = $location['results'][0]['address_components'];
			$city = $results[4]['short_name'];
			$state = $results[6]['short_name'];
			$country = $results[7]['long_name'];

			$data = array('tinder_id' => $tinder_id,
						'lon' => $lon,
						'lat' => $lat,
						'city' => $city,
						'state' => $state,
						'country' => $country,
						'datetime' => date('Y-m-d H:i:s'));
			$this->db->insert('pings', $data);
		}

		/**
		 * Return an array containing all of the pings that a given user has made
		 * @param {string} [tinder_id] The Tinder Id of the user being targetted
		 */
		public function GetPings($tinder_id) {
			$this->db->select('lon, lat, city, state, country, datetime');
			$this->db->where('tinder_id', $tinder_id);
			$query = $this->db->get('pings');
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				$lon[$i] = $row->lon;
				$lat[$i] = $row->lat;
				$city[$i] = $row->city;
				$state[$i] = $row->state;
				$country[$i] = $row->country;
				$time[$i] = $row->datetime;

				$i++;
			}

			$return = [];

			for($i=0;$i<$count;$i++) {
				$return[$i] = array('lon' => $lon[$i],
									'lat' => $lat[$i],
									'city' => $city[$i],
									'state' => $state[$i],
									'country' => $country[$i],
									'datetime' => $time[$i]);
			}

			return array('count' => $count, 'pings' => $return);
		}

		/**
		 * Check to see if there is a record existing of a given user reporting another user
		 * @param {string} [my_id] The Tinder ID of the user who is logged in 
		 * @param {string} [his_id] The Tinder ID of the targetted user
		 */
		public function CheckReport($my_id, $his_id) {
			if($my_id != $his_id) {
				$this->db->select('id');
				$this->db->where(array('reported_by' => $my_id, 'user_reported' => $his_id));
				$query = $this->db->get('reports');
				
				if($query->num_rows() == 0) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}

		/**
		 * Insert a row into the reports table. This reflects that one user has reported another
		 * @param {string} [my_id] The Tinder ID of the user who is logged in 
		 * @param {string} [his_id] The Tinder ID of the targetted user
		 */
		public function InsertReport($my_id, $his_id) {
			$data = array('reported_by' => $my_id, 'user_reported' => $his_id, 'datetime' => date('Y-m-d H:i:s'));
			$this->db->insert('reports', $data);
		}

		/**
		 * Get the like, match and pass counts of a given user
		 * @param {string} [id] The Tinder ID of a given user
		 */
		public function GetThreeStats($id) {
			// Get the like count
			$like_count = $this->database_model->GetLikeCount($id, FALSE);

			// Find out how many matches the user has
			$match_count = $this->database_model->GetMatchCount($id);

			// Return an array containing all three stats
			return array('like_count' => $like_count, 
						'match_count' => $match_count, 
						'pass_count' => NULL);
		}

		/**
		 * Return an array containing the counts of all of a given user's categories
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 */
		public function GetUserStats($tinder_id, $my_id) {
			// This view if for if the user is logged in
			$params = array(
							array('key' => 'tweets', 'name' => 'tweets', 'count' => NULL),
							array('key' => 'likes', 'name' => 'likes', 'count' => NULL),
							array('key' => 'matches', 'name' => 'matches', 'count' => NULL),
							array('key' => 'passes', 'name' => 'passes', 'count' => NULL)
						);

			foreach($params as $key) {
				switch($key['key']) {
					case'likes';

						$count = $this->GetLikeCount($tinder_id, FALSE);
						break;

					case'mutual_likes';

						$count = $this->GetMutualLikeCount($tinder_id, $my_id);
						break;

					case'liked_by';

						$count = $this->GetLikeCount($tinder_id, TRUE);
						break;

					case'matches';

						$count = $this->GetMatchCount($tinder_id);
						break;

					case'mutual_matches';

						$count = $this->GetMutualMatchCount($tinder_id, $my_id);
						break;

					case'passes';

						$count = $this->GetPassCount($tinder_id, FALSE);
						break;

					case'mutual_passes';

						$count = $this->GetMutualPassCount($tinder_id, $my_id);
						break;

					case'passed_by';

						$count = $this->GetPassCount($tinder_id, TRUE);
						break;

					case'tweets';

						$count = $this->twitter_model->GetTweets();
						break;
				}

				// Set the count key to each element in the array
				$params['results'][$i]['count'] = $count;
			}

			return $params;
		}

		/**
		 * Query the DB to get all of the hottest user who fit the specfied criteria except for the distance which cannot be done with MySQL
		 * @param {int} [gender] The gender filter. 0 for men. 1 for women. -1 for both
		 * @param {int} [min] The age minimum
		 * @param {int} [max] The age maximum
		 * @param {string} [q] The query string to match each user's first name with
		 */
		public function HotQuery($gender, $min, $max, $q) {
			$params = [];

			$sql = "SELECT users.tinder_id, users.first_name, users.age, users.username, users.profile_pic, last_seen.*
					FROM users 
					JOIN last_seen
					ON users.tinder_id = last_seen.seen_id ";

			if($gender != 'both'
			|| is_numeric($min)
			|| is_numeric($max)) {
				$sql .= "WHERE";
			}

			// Filter the age
			if($gender != 'both' && $gender != -1) {
				// echo $gender;
				array_push($params, $gender);
				$sql .= " users.gender = ? AND ";
			}

			// Filter the minimum age
			if(is_numeric($min)) {
				array_push($params, $min);
				$sql .= " users.age >= ? AND ";
			}

			// Filter the maximum age
			if(is_numeric($max)) {
				array_push($params, $max);
				$sql .= " users.age <= ? ";
			}

			// Filter the search term
			if($q != '') {
				array_push($params, '%'.trim($q).'%');
				$sql .= " AND users.first_name LIKE ? ";
			}

			$query = $this->db->query($sql, $params);
		
			return array('count' => $query->num_rows(), 'result' => $query->result());
		}

		/**
		 * Return an array contaning users that have been filtered by their location
		 * @param {string} [sql] The results from the query
		 * @param {decimal} [lon] The longitude coordinate
		 * @param {decimal} [lat] The latitude coordinate
		 * @param {int} [distance] The distance filter value in miles
		 */
		public function GetHottest($sql, $lon, $lat, $distance) {
			$i = 0;

			foreach($sql['result'] as $row) {
				// The columns from the users table
				$tinder_id[$i] = $row->tinder_id;
				$name[$i] = $row->first_name;
				$age[$i] = $row->age;
				$username[$i] = $row->username;
				$pic[$i] = $row->profile_pic;

				// The columns from the last seen column
				$last_lat[$i] = $row->lat;
				$last_lon[$i] = $row->lon;

				$i++;
			}

			// Create the master array that will store everything
			$return = [];

			// Loop thru each user
			for($i=0;$i<$sql['count'];$i++) {
				// Get each user's match count
				$like_count = $this->GetLikeCount($tinder_id[$i], TRUE);

				// Get the distance between the client and the users
				$between = $this->location_model->Haversine($last_lat[$i], $last_lon[$i], $lat, $lon);
				// var_dump($between);

				// If the distance is within the user's settings limit, then push it into the greater array
				if($between < $distance) {
					$new_data = array('tinder_id' => $tinder_id[$i],
									'name' => $name[$i],
									'age' => $age[$i],
									'profile_pic' => $pic[$i],
									'link' => FormatUserLink($tinder_id[$i], $username[$i]),
									'distance' => $between,
									'like_count' => $like_count);
					array_push($return, $new_data);
				}
			}

			// Sort the results by like count
			function SortResults($a, $b) {    
				return $b['like_count'] - $a['like_count'];
			}

			usort($return, 'SortResults');

			return array('count' => count($return), 'users' => $return);
		}

		/**
		 * Get the hottest male or female in a given state
		 * @param {int} [sex] The gender code. 0 for men. 1 for women
		 * @param {string} [state] The state's two letter abbreviation to target
		 */
		public function HottestByState($sex, $state) {
			$sql = "SELECT users.*, last_seen.*
					FROM likes 
					LEFT JOIN users ON likes.user_one = users.tinder_id
					RIGHT JOIN last_seen ON likes.user_one = last_seen.seen_id
					WHERE likes.match_id != ? AND users.gender = ? AND last_seen.state = ?
					GROUP BY likes.user_one
					ORDER BY COUNT(*) DESC
					LIMIT 1";
			$query = $this->db->query($sql, array('', $sex, $state));
			$count = $query->num_rows();
			$i = 0;

			// echo 'Count: '.$count;

			if($count == 1) {
				foreach($query->result() as $row) {
					// Get the data from the users table
					$id[$i] = $row->tinder_id;
					$name[$i] = $row->first_name;
					$gender[$i] = $row->gender;
					$username[$i] = $row->username;
					$dob[$i] = $row->dob;
					$age[$i] = $row->age;
					$bio[$i] = $row->bio;
					$activity[$i] = $row->last_activity_date;
					$pic[$i] = $row->profile_pic;

					// Get the data from the last_seen table
					$seen_id[$i] = $row->seen_id;
					$by_id[$i] = $row->seen_by_id;
					$lon[$i] = $row->lon;
					$lat[$i] = $row->lat;
					$city[$i] = $row->city;
					$n_state[$i] = $row->state;
					$miles[$i] = $row->miles_away;
					$datetime[$i] = $row->datetime;

					$i++;
				}

				$return = [];

				for($i=0;$i<$count;$i++) {
					$return[$i] = array('tinder_id' => $id[$i],
										'name' => $name[$i],
										'username' => $username[$i],
										'gender' => $gender[$i],
										'age' => $age[$i],
										'bio' => $bio[$i],
										'pic' => $pic[$i],
										'seen_id' => $seen_id[$i],
										'seen_by_id' => $by_id[$i],
										'lon' => $lon[$i],
										'lat' => $lat[$i],
										'city' => $city[$i],
										'state' => $n_state[$i],
										'miles_away' => $miles[$i],
										'datetime' => $datetime[$i]);
				}

				return array('count' => $count, 'hot' => $return);
			} else {
				return FALSE;
			}
		}

		/**
		 * Query the DB to get info about a given user
		 * @param {string} [id] The Tinder ID of the targetted user
		 */
		public function GetUserInfo($id) {
			$sql = "SELECT users.tinder_id, users.first_name, users.username, users.dob, users.age, users.bio, users.gender, users.profile_pic, users.last_activity_date, pics.*
					FROM users
					JOIN pics 
					ON users.tinder_id = pics.tinder_id
					WHERE users.tinder_id = ?
					OR users.username = ?
					ORDER BY pic_order ASC";
			$query = $this->db->query($sql, array($id, $id));
			$count = $query->num_rows();
			$i = 0;

			// echo $count;
			// FormatArray($query->result());
			// die;

			if($count > 0) {
				foreach($query->result() as $row) {
					// Get the data from the users table
					if($i == 0) {
						$tinder_id = $row->tinder_id;
						$name = $row->first_name;
						$gender = $row->gender;
						$username = $row->username;
						$dob = $row->dob;
						$age = $row->age;
						$bio = $row->bio;
						$activity = $row->last_activity_date;
						$pic = $row->profile_pic;
					}

					// Get the data from the pics table
					$filename[$i] = $row->filename;
					$order[$i] = $row->pic_order;

					$i++;
				}

				return array('tinder_id' => $tinder_id,
							'distance' => $this->GetLastSeen($tinder_id),
							'username' => $username,
							'link' => FormatUserLink($id, $username),
							'name' => $name,
							'gender' => $gender,
							'gender_format' => FormatGender($gender),
							'dob' => $dob,
							'age' => $age,
							'bio' => BioLinks(BioDefault($bio, $name)),
							'last_activity_date' => $activity,
							'last_active_format' => FormatTime($activity),
							'profile_pic' => $pic,
							'pics' => array('file' => $filename, 'order' => $order));
			} else {
				return FALSE;
			}
		}

		/**
		 * Update/Insert rows into the users and/or settings table
		 * @param {array} [user_data] An array containing info about a given user. Contains info that will be inserted into the users table 
		 * @param {array} [settings_data] An array containing info about a given user. Contains info that will be inserted into the settings table 
		 */
		public function InsertUser($user_data, $settings_data = NULL) {
			$this->db->select('id, username');
			$this->db->where('tinder_id', $user_data['tinder_id']);
			$query = $this->db->get('users');
			$count = $query->num_rows();
			// FormatArray($settings_data);

			if($count == 0) {
				// FormatArray($user_data);

				// Insert a row into the users table
				$this->db->insert('users', $user_data);

				if($settings_data !== NULL) {
					// Insert a row into the settings table
					$settings_data['tinder_id'] = $user_data['tinder_id'];
					$this->db->insert('settings', $settings_data);
				}

				return array('user_id' => $this->db->insert_id(), 'username' => NULL);
			} else {
				$row = $query->row_array();

				// Update the users table
				$this->db->where('tinder_id', $user_data['tinder_id']);
				$this->db->update('users', $user_data);

				if($settings_data !== NULL) {
					// Update the settings table
					$this->db->where('tinder_id', $user_data['tinder_id']);
					$this->db->update('settings', $settings_data);
				}

				return array('user_id' => $row['id'], 'username' => $row['username']);
			}
		}

		/**
		 * Query the DB to get all the users from the users table
		 */
		public function GetAllUsers() {
			$sql = "SELECT username, tinder_id, first_name, age 
					FROM users";
			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			if($count > 0) {
				foreach($query->result() as $row) {
					$id[$i] = $row->tinder_id;
					$username[$i] = $row->username;
					$name[$i] = $row->first_name;
					$age[$i] = $row->age;

					$i++;
				}

				$return = [];

				for($i=0;$i<$count;$i++) {
					$return[$i] = array('link' => FormatUserLink($id[$i], $username[$i]),
										'name' => $name[$i],
										'age' => $age[$i]);
				}
				
				shuffle($return);
				return $return;
			} else {
				return FALSE;
			}
		}

		/**
		 * Return the users that were last seen in a given state
		 * @param {string} [$state] The two letter abbreviation code of the given state
		 */
		public function GetUsersInState($state, $gender = NULL) {
			$data = array($state);

			$sql = "SELECT AVG(users.age) AS age, COUNT(users.id) AS count
					FROM users
					JOIN last_seen
					ON users.tinder_id = last_seen.seen_id
					WHERE last_seen.state = ?";

			if($gender !== NULL) {
				$sql .= " AND users.gender = ?";
				array_push($data, $gender);
			}

			$query = $this->db->query($sql, $data);
			$result = $query->result();
			$count = $result[0]->count;

			return array('count' => $count, 'avg_age' => ceil($result[0]->age));
		}

		/**
		 * Find out where a given state stands in an array containing the most popular states in descending order 
		 * @param {string} [$state] The two letter abbreviation of the given state
		 */
		public function GetMostPopularState($state) {

		}

		/**
		 * Update the users table
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {array} [data] An array containing the column values
		 */
		public function UpdateUser($tinder_id, $data) {
			$this->db->where('tinder_id', $tinder_id);
			$this->db->update('users', $data);
		}

		/**
		 * Check to see if a given username is available
		 * @param {string} [username] The username
		 */
		public function CheckUsername($username) {
			$this->db->select('id');
			$this->db->where('username', $username);
			$query = $this->db->get('users');
			return $query->num_rows();
		}

		/**
		 * Query the DB to see if a user with a given Tinder ID exist in the users table
		 * @param {string} [id] The Tinder ID of the targetted user
		 */
		public function UserExists($id) {
			$this->db->select('id');
			$this->db->where('tinder_id', $id);
			$query = $this->db->get('users');
			return $query->num_rows();
		}
	}