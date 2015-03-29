<?php 
	class Database_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Load the helper file
			$this->load->helper('common_helper');
		}

		public function FlushDB() {
			// Flush all of the tables except the locations one
			$this->db->where('id > "0"');
			$this->db->delete(array('batches', 'last_seen', 'likes', 'msg', 'passes', 'pics', 'pings', 'reports', 'settings')); 

			$this->db->where('tinder_id != "0"');
			$this->db->delete('users'); 
		}

		/**
		 * Get the user that is next in line to be either liked or passed
		 * @param {int} [id] The user ID of the Tinder user 
		 * @return {int} [tinder_id] The Tinder ID of the next batch user
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
				// Insert each batch user accordingly
				$data = array('tinder_id' => $info[$i]['tinder_id'],
							'first_name' => $info[$i]['name'],
							'age' => $info[$i]['age'],
							'dob' => date('M j, Y', strtotime($info[$i]['birth_date'])),
							'gender' => $info[$i]['gender'],
							'bio' => $info[$i]['bio'],
							'profile_pic' => $info[$i]['profile_pic']);
				$this->InsertUser($data);

				// Check to see if there is a record of the user existing in the batches table
				$this->db->select('id');
				$this->db->where(array('tinder_id' => $info[$i]['tinder_id'], 'user_id' => $user_id));
				$query = $this->db->get('batches');

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
				$created_at = (array_key_exists('created_date', $updates[$i]) ? $updates[$i]['created_date'] : NULL);

				// Get the last activity date
				$last_active = (array_key_exists('last_activity_date', $updates[$i]) ? $updates[$i]['last_activity_date'] : NULL);

				// Get the Tinder ID of the other person involed in the match
				if(array_key_exists('person', $updates[$i])) {
					$person = $updates[$i]['person'];

					// Check to see if there is a record of each match participant in the DB
					if($person) {
						// Insert a row into the users table if necessary
						$user_data = array('tinder_id' => $person['_id'],
										'first_name' => $person['name'],
										'dob' => date('M j, Y', strtotime($person['birth_date'])),
										'age' => ReturnAge($person['birth_date']),
										'bio' => $person['bio'],
										'gender' => $person['gender'],
										'last_activity_date' => $person['ping_time'],
										'profile_pic' => ReturnProfilePic($person['photos']));
						$this->InsertUser($user_data);

						// Insert each user's pics
						$this->InsertPics($person['_id'], ReturnPicsArray($person['photos']));
						
						// Define the data that will be used for the insert query
						$this->InsertIntoLikes($my_tinder_id, $person['_id'], $match_id, $last_active, $created_at);

						// Check to see if each user has a row existing in the last_seen table
						$last = $this->GetLastSeen($person['_id']);

						// If there is a record of the user existing, then see if your distance to him/her is closer 
						if(empty($last)) {
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

				// Insert all of the messages into the msg table
				$this->UpdateThread($updates[$i]['messages'], count($updates[$i]['messages']));	
			}
		}

		/**
		 * Return the data from the last seen table belonging to a given user
		 * @param {string} [tinder_id] The Tinder ID of the user who is being targetted
		 * @return {array|boolean} An array containing the the time and location that this user was last seen by or FALSE
		 */
		public function GetLastSeen($tinder_id) {
			$this->db->select('*');
			$this->db->where('seen_id', $tinder_id);
			$query = $this->db->get('last_seen');

			if($query->num_rows() == 1) {
				foreach($query->result() as $row) {
					return array('seen_id' => $row->seen_id,
								'seen_by_id' => $row->seen_by_id,
								'lon' => $row->lon,
								'lat' => $row->lat,
								'city' => $row->city,
								'state' => $row->state,
								'miles_away' => $row->miles_away,
								'datetime' => $row->datetime);
				}
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
		 * @return {array} An array containing info about the user and info about the location
		 */
		public function EditLastSeen($distance, $my_tinder_id, $his_tinder_id, $lon, $lat) {
			// Check to see if each user has a row existing in the last_seen table
			$last = $this->GetLastSeen($his_tinder_id);
			
			// If there is a record of the user existing
			if($last) {
				// Make sure the user isn't updating their own profile and the user is logged in
				if($my_tinder_id && $his_tinder_id != $my_tinder_id) {
					// Make sure the user's last location isn't one from a ping
					if($last['seen_id'] != $last['seen_by_id']) {
						// Check to see if your proximity is closer than the one currently on record
						if($distance < $last['miles_away']) {
							// Get the name of the city and state based upon the lat & lon coordinates
							$loc = $this->loc->MapquestLatLon($lat, $lon);

							// Update the last seen row in the table
							$data = array('seen_id' => $his_tinder_id,
										'seen_by_id' => $my_tinder_id,
										'lon' => $lon,
										'lat' => $lat,
										'city' => $loc['city'],
										'state' => $loc['state'],
										'miles_away' => $distance,
										'datetime' => date('Y-m-d H:i:s'));
							$this->UpdateLastSeen($his_tinder_id, $data);

							// Set the $last variable to the new data
							$last = $data;
						} 
					} 
				} 
			} else {
				// Get the latitude and longitude coordinates
				$loc = $this->loc->MapquestLatLon($lat, $lon);

				// Create a new row in the last_seen table for this user
				$data = array('seen_id' => $his_tinder_id,
							'seen_by_id' => $my_tinder_id,
							'lon' => $lon,
							'lat' => $lat,
							'city' => $loc['city'],
							'state' => $loc['state'],
							'miles_away' => $distance,
							'datetime' => date('Y-m-d H:i:s'));
				$this->CreateLastSeen($data);
				$last = $data;
			}

			// Get info about the person who saw this user
			$user = $this->GetUserInfo($last['seen_by_id']);
			return array('user' => $user, 'data' => $last);
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
		 * @return {int} The number of rows returned from the query
		 */
		public function GetLikeCount($tinder_id, $inverse, $q = NULL) {
			$params = array($tinder_id);

			$sql = "SELECT users.id, likes.id
					FROM users
					JOIN likes";

			if($inverse) {
				$sql .= " ON users.tinder_id = likes.user_one WHERE likes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = likes.user_two WHERE likes.user_one = ?";
			}

			if(!empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
				array_push($params, '%'.trim($q).'%');
			}

			$query = $this->db->query($sql, $params);
			return $query->num_rows();
		}

		/**
		 * Return an array containing all of the info about each of a given user's likes or liked by
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the user's like count or liked_by count. TRUE for liked_by count. FALSE for like_count
		 * @param {string} [q] The query string to search the user's first name
		 * @return {array} An array containing the number of rows and info about all of the users
		 */
		public function GetLikes($tinder_id, $inverse, $q = NULL) {
			$params = array($tinder_id);

			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes";

			if($inverse) {
				$sql .= " ON users.tinder_id = likes.user_one WHERE likes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = likes.user_two WHERE likes.user_one = ?";
			}

			if(!empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
				array_push($params, '%'.trim($q).'%');
			}

			$sql .= " ORDER BY likes.datetime DESC";

			$query = $this->db->query($sql, $params);
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			$result = $query->result();
			// FormatArray($result, TRUE);

			foreach($query->result() as $row) {
				$user_info = array('tinder_id' => $row->tinder_id,
									'first_name' => $row->first_name,
									'username' => $row->username,
									'age' => $row->age,
									'bio' => $row->bio,
									'profile_pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username));
				$return[$i] = array('id' => $row->id,
									'like' => $row->user_two,
									'datetime' => $row->datetime,
									'match' => (!empty($row->match_id) ? TRUE : FALSE),
									'user_info' => $user_info);

				$i++;
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Update/Insert a row into the likes table depending on if one currently exists
		 * @param {string} [my_id] The Tinder ID of the user who is currently logged in
		 * @param {string} [tinder_id] The Tinder ID of the user who is being liked
		 * @param {string} [match_id] The match ID of the liking
		 */
		public function InsertIntoLikes($my_id, $tinder_id, $match_id, $last_active, $created_at) {
			$liked = $this->SeeIfLiked($my_id, $tinder_id, FALSE);

			// Check to see if I have already liked this user
			if($liked['count'] == 0) {
				// Check to see if this user has liked me
				$liked = $this->SeeIfLiked($my_id, $tinder_id, TRUE);

				// If the user has already liked my profile, then update that row with the match ID
				if($liked['count'] == 1) {
					$this->db->where(array('user_one' => $tinder_id, 'user_two' => $my_id));
					$this->db->update('likes', array('match_id' => $match_id));
				} else {
					if($match_id != 'false' && !empty($match_id)) {
						// If there is no record, then create one
						$data = array('user_one' => $tinder_id,
									'user_two' => $my_id,
									'match_id' => $match_id,
									'datetime' => strtotime($created_at),
									'last_active' => strtotime($last_active),
									'created_at' => strtotime($created_at));
						$this->db->insert('likes', $data);
					}
				}

				// If there is no record, then create one
				$data = array('user_one' => $my_id,
							'user_two' => $tinder_id,
							'match_id' => $match_id,
							'datetime' => strtotime($created_at),
							'last_active' => strtotime($last_active),
							'created_at' => strtotime($created_at));
				$this->db->insert('likes', $data);
			}
		}

		/**
		 * See if one user has already liked another user on WeTinder
		 * @param {string} [my_id] The Tinder ID of the user is currently logged in
		 * @param {string} [tinder_id] The Tinder ID of the user who is being liked
		 * @param {boolean} [inverse] Whether or not to do the opposite. 
		 * @return {array} An array containing the number of rows returned, the mactch ID and unmatched status
		 */
		public function SeeIfLiked($my_id, $tinder_id, $inverse) {
			$this->db->select('match_id, unmatched, unmatched_by');

			if($inverse) {
				$this->db->where(array('user_one' => $my_id, 'user_two' => $tinder_id));
			} else {
				$this->db->where(array('user_one' => $tinder_id, 'user_two' => $my_id));
			}

			$query = $this->db->get('likes');
			$result = $query->result();
			$count = $query->num_rows();
		
			$return = array('count' => $count);

			if($count > 0) {
				$return['match_id'] = $result[0]->match_id;
				$return['unmatched'] = $result[0]->unmatched;
			}

			return $return;
		}

		/**
		 * Return a number representing that two users have both liked
		 * @param {string} [my_id] The Tinder ID of the user is currently logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match users' first names with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMutualLikeCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE (likes.user_two = ? OR likes.user_two = ?)";

			if(!empty($q)) {
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
		 * @return {array} An array containing the number of rows and info about all of the users
		 */
		public function GetMutualLikes($my_id, $his_id, $q = NULL) {
			$params = array($my_id, $his_id);

			$sql = "SELECT users.*
					FROM users
					INNER JOIN likes
					ON likes.user_two = users.tinder_id
					WHERE likes.user_one IN (
					    SELECT user_one
					    FROM likes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 2)";

			if(!empty($q)) {
				$sql .= " AND u.first_name LIKE ?";
				array_push($params, '%'.trim($q).'%');
			}
		
			$query = $this->db->query($sql, $params);
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$tinder_id[$i] = $row->tinder_id;
				$profile_pic[$i] = $row->profile_pic;
				$name[$i] = $row->first_name;
				$username[$i] = $row->username;
				$age[$i] = $row->age;
				$bio[$i] = $row->bio;

				if($row->tinder_id != $my_id && $row->tinder_id != $his_id) {
					$new = array('tinder_id' => $row->tinder_id,
								'first_name' => $row->first_name,
								'username' => $row->username,
								'bio' => $row->bio,
								'profile_pic' => $row->profile_pic,
								'link' => FormatUserLink($row->tinder_id, $row->username),
								'age' => $row->age);
					array_push($return, $new);
				}

				$i++;
			}

			return array('count' => count($return), 'users' => $return);
		}

		/**
		 * Get the number of matches that a given user has
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [q] The query string to match the user's first name with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMatchCount($tinder_id, $q = NULL) {
			$sql = "SELECT likes.id, users.id
					FROM likes
					JOIN users
					ON likes.user_two = users.tinder_id
					WHERE likes.match_id != ? 
					AND likes.unmatched IS NULL
					AND likes.user_one = ?";

			if(!empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array(0, $tinder_id, '%'.trim($q).'%'));
			return $query->num_rows();
		}

		/**
		 * Return an array containing all of the users that have matched with a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [q] The query string to match the user's first name with
		 * @return {array} An array containing the number of rows and info about the users
		 */
		public function GetMatches($tinder_id, $q = NULL, $same) {
			$sql = "SELECT likes.*, users.*
					FROM likes
					JOIN users
					ON likes.user_two = users.tinder_id
					WHERE likes.match_id != ?
					AND likes.unmatched IS NULL
					AND likes.user_one = ?";

			if(!empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}
		
			$query = $this->db->query($sql, array('false', $tinder_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				// Set all of the user's info in an array
				$user_info = array('tinder_id' => $row->tinder_id,
								'first_name' => $row->first_name,
								'username' => $row->username,
								'profile_pic' => $row->profile_pic,
								'age' => $row->age,
								'bio' => $row->bio,
								'link' => FormatUserLink($row->tinder_id, $row->username));

				// Get the last message from the msg table
				$msg = $this->GetLastMsg($row->match_id);

				// Determine what the paragraph for each match should read
				if($same) {
					if(!$msg) {
						$msg = array('msg' => NULL, 
									'time' => $row->created_at, 
									'time_format' => FormatTime(@date('F d', $row->last_activity_date)));
						$text = 'Matched on '.@date('n/d', $row->created_at);
					} else {
						$text = $msg['msg'];
					}
				} else {
					$text = 'Matched on '.@date('n/d', $row->created_at);
				}

				$return[$i] = array('id' => $row->match_id,
									'like' => $row->user_two,
									'datetime' => $row->datetime,
									'user_info' => $user_info,
									'text' => $text,
									'last_msg' => $msg);

				$i++;
			}

			// Sort the results by most recent
			usort($return, 'SortMessages');
			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Get the number of matches that two user have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the user's first name with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMutualMatchCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.id, likes.*
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE (likes.user_one = ? OR likes.user_two = ?)
					AND likes.unmatched IS NULL";

			if($q && !empty($q)) {
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
		 * @return {array} An array containing the number of rows and info about the users
		 */
		public function GetMutualMatches($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*
					FROM users
					INNER JOIN likes
					ON likes.user_two = users.tinder_id
					WHERE likes.user_one IN (
					    SELECT user_one
					    FROM likes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 4)
					AND likes.match_id != ?";

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, 'false', '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$user_info = array('tinder_id' => $row->tinder_id,
									'first_name' => $row->first_name,
									'username' => $row->username,
									'age' => $row->age,
									'bio' => $row->bio,
									'profile_pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username));

				$return[$i] = array('id' => $row->id,
									'like' => $row->user_two,
									'datetime' => $row->datetime,
									'match' => TRUE,
									'user_info' => $user_info);

				$i++;
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Query the DB to see if a match with a given match ID exists
		 * @param {string} [match_id] The match ID being targetted
		 * @return {int} The number of rows returned from the query
		 */
		public function MatchExists($match_id) {
			$this->db->select('id');
			$this->db->where('match_id', $match_id);
			$query = $this->db->get('likes');
			return $query->num_rows();
		}

		/**
		 * Get the last message from a message thread
		 * @param {string} [match_id] The match ID of the thread 
		 * @return {array|boolean} An array containing the time and text of the last message
		 */
		public function GetLastMsg($match_id) {
			$this->db->select('msg, datetime');
			$this->db->where('match_id', $match_id);
			$this->db->order_by('datetime', 'DESC');
			$this->db->limit(1);
			$query = $this->db->get('msg');

			if($query->num_rows() == 1) {
				foreach($query->result() as $row) {
					return array('msg' => $row->msg, 
								'time' => $row->datetime, 
								'time_format' => FormatTime(date('F d', $row->datetime)));
				}
			} else {
				return FALSE;
			}
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
		 * @return {array|boolean} An array containing the Tinder ID's of the two users in the match
		 */
		public function GetMatchInfo($id) {
			$sql = "SELECT users.tinder_id, users.first_name, users.age, users.profile_pic, users.username, likes.views, likes.unmatched, likes.unmatched_by
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE likes.match_id = ?";
			$query = $this->db->query($sql, array($id));
			$count = $query->num_rows();
			$i = 0;
			
			if($count == 2) {
				foreach($query->result() as $row) {
					$data[$i] = array('id' => $row->tinder_id,
									'name' => $row->first_name,
									'age' => $row->age,
									'link' => FormatUserLink($row->tinder_id, $row->username),
									'pic' => $row->profile_pic,
									'views' => $row->views,
									'unmatched' => $row->unmatched,
									'unmatched_by' => $row->unmatched_by);

					$i++;
				}

				return array('user_one' => $data[0], 'user_two' => $data[1]);
			} else {
				return FALSE;
			} 
		}

		/**
		 * Update 
		 * @param {string} [id] The match Id
		 * @param {int} [views] The number of views that the match currently has
		 * @return {int} The new number of views
		 */
		public function UpdateMatchViews($id, $views) {
			$this->db->where('match_id', $id);
			$this->db->update('likes', array('views' => $views+1));
			return $views+1;
		}

		/**
		 * Get all of the messages from a given thread
		 * @param {string} [match_id] The Match ID
		 * @return {array|boolean} An array containing all of the messages between two users
		 */
		public function GetThread($match_id) {
			$this->db->select('*');
			$this->db->where('match_id', $match_id);
			$this->db->order_by('datetime', 'ASC');
			$query = $this->db->get('msg');
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				$return[$i] = array('to' => $row->user_from,
									'from' => $row->user_to,
									'message' => $row->msg,
									'datetime' => date('n/j/y g:i a', $row->datetime));

				$i++;
			}

			return ($count > 0 ? $return : FALSE);
		}

		/**
		 * Insert a row into the messages table
		 * @param {array} [data] The array containing the column keys and values
		 */
		public function InsertMessage($data) {
			$this->db->select('id');
			$this->db->where($data);
			$query = $this->db->get('msg');
			
			if($query->num_rows() == 0) {
				$this->db->insert('msg', $data);
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
			$this->db->insert('passes', $data);
		}

		/**
		 * Get the number of passes that a given user has gotten or the number of users that have passed a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the number of passes that a given user has gotten. 
		 * @param {string} [q] The query string to match the users' first names with
		 * @return {int} The number of rows returned from the query
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

			if($q !== NULL && !empty($q)) {
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
		 * @return {array} An array containing the number of rows returned and info about the users
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

			if($q !== NULL && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$user_info = array('tinder_id' => $row->tinder_id,
									'first_name' => $row->first_name,
									'username' => $row->username,
									'age' => $row->age,
									'bio' => $row->bio,
									'profile_pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username));

				$return[$i] = array('id' => $row->id,
									'pass' => $row->user_two,
									'datetime' => $row->datetime,
									'user_info' => $user_info);

				$i++;
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Get the number of passes that two users have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the users' first names with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMutualPassCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*
					FROM users
					INNER JOIN passes
					ON passes.user_two = users.tinder_id
					WHERE passes.user_one IN (
					    SELECT user_one
					    FROM passes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 2)";

			if($q !== NULL && !empty($q)) {
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
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetMutualPasses($my_id, $his_id, $q = NULL) {
			$sql = "SELECT users.*
					FROM users
					INNER JOIN passes
					ON passes.user_two = users.tinder_id
					WHERE passes.user_one IN (
					    SELECT user_one
					    FROM passes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 2)";

			if($q != NULL && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$user_info = array('tinder_id' => $row->tinder_id,
									'first_name' => $row->first_name,
									'username' => $row->username,
									'age' => $row->age,
									'bio' => $row->bio,
									'profile_pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username));

				$return[$i] = array('id' => $row->id,
									'pass' => $row->user_two,
									'datetime' => $row->datetime,
									'user_info' => $user_info);

				$i++;
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
			$location = $this->loc->MapquestLatLon($lat, $lon);
			
			$data = array('tinder_id' => $tinder_id,
						'lon' => $lon,
						'lat' => $lat,
						'city' => $location['city'],
						'state' => $location['state'],
						'country' => $location['country'],
						'datetime' => date('Y-m-d H:i:s'));
			$this->db->insert('pings', $data);
		}

		/**
		 * Return an array containing all of the pings that a given user has made
		 * @param {string} [tinder_id] The Tinder Id of the user being targetted
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetPings($tinder_id) {
			$this->db->select('lon, lat, city, state, country, datetime');
			$this->db->where('tinder_id', $tinder_id);
			$query = $this->db->get('pings');
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				$return[$i] = array('lon' => $row->lon,
									'lat' => $row->lat,
									'city' => $row->city,
									'state' => $row->state,
									'country' => $row->country,
									'datetime' => $row->datetime);

				$i++;
			}

			return array('count' => $count, 'pings' => $return);
		}

		/**
		 * Check to see if there is a record existing of a given user reporting another user
		 * @param {string} [my_id] The Tinder ID of the user who is logged in 
		 * @param {string} [his_id] The Tinder ID of the targetted user
		 * @return {boolean} 
		 */
		public function CheckReport($my_id, $his_id) {
			if($my_id != $his_id) {
				$this->db->select('id');
				$this->db->where(array('reported_by' => $my_id, 'user_reported' => $his_id));
				$query = $this->db->get('reports');
				return ($query->num_rows() == 0 ? TRUE : FALSE);
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
		 * Return an array containing the counts of all of a given user's categories
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetUserStats($tinder_id, $my_id, $twitter_id) {
			// This view if for if the user is logged in
			$params = array(array('key' => 'likes', 'name' => 'likes', 'count' => NULL),
							array('key' => 'matches', 'name' => 'matches', 'count' => NULL),
							array('key' => 'passes', 'name' => 'passes', 'count' => NULL),
							array('key' => 'tweets', 'name' => 'tweets', 'count' => NULL));

			for($i=0;$i<count($params);$i++) {
				switch($params[$i]['key']) {
					case'likes':

						$count = $this->GetLikeCount($tinder_id, FALSE);
						break;

					case'mutual_likes':

						$count = $this->GetMutualLikeCount($tinder_id, $my_id);
						break;

					case'liked_by':

						$count = $this->GetLikeCount($tinder_id, TRUE);
						break;

					case'matches':

						$count = $this->GetMatchCount($tinder_id);
						break;

					case'mutual_matches':

						$count = $this->GetMutualMatchCount($tinder_id, $my_id);
						break;

					case'passes':

						$count = $this->GetPassCount($tinder_id, FALSE);
						break;

					case'mutual_passes':

						$count = $this->GetMutualPassCount($tinder_id, $my_id);
						break;

					case'passed_by':

						$count = $this->GetPassCount($tinder_id, TRUE);
						break;

					case'tweets':

						$count = $this->GetTweetCount($twitter_id);
						break;
				}

				// Set the count key to each element in the array
				$params[$i]['count'] = $count;
			}

			return $params;
		}

		/**
		 * Query the DB to get all of the hottest user who fit the specfied criteria except for the distance which cannot be done with MySQL
		 * @param {int} [gender] The gender filter. 0 for men. 1 for women. -1 for both
		 * @param {int} [min] The age minimum
		 * @param {int} [max] The age maximum
		 * @param {string} [q] The query string to match each user's first name with
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function HotQuery($gender, $min, $max, $q) {
			$params = [];

			$sql = "SELECT users.tinder_id, users.first_name, users.age, users.username, users.profile_pic, last_seen.*
					FROM users 
					JOIN last_seen
					ON users.tinder_id = last_seen.seen_id ";

			if($gender != 'both' || is_numeric($min) || is_numeric($max)) {
				$sql .= "WHERE";
			}

			// Filter the age
			if($gender != 'both' && $gender != -1) {
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
			if(!empty($q)) {
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
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetHottest($sql, $lon, $lat, $distance) {
			$i = 0;
			$return = [];

			foreach($sql['result'] as $row) {
				// Get each user's match count
				$like_count = $this->GetLikeCount($row->tinder_id, TRUE);

				// Get the distance between the client and the users
				$between = $this->loc->Haversine($row->lat, $row->lon, $lat, $lon);

				// If the distance is within the user's settings limit, then push it into the greater array
				if($between < $distance) {
					$new_data = array('tinder_id' => $row->tinder_id,
									'name' => $row->first_name,
									'age' => $row->age,
									'profile_pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username),
									'distance' => $between,
									'like_count' => $like_count);
					array_push($return, $new_data);
				}

				$i++;
			}

			// Sort the results by like count
			usort($return, 'SortByLikes');
			return array('count' => count($return), 'users' => $return);
		}

		/**
		 * Get the hottest male or female in a given state
		 * @param {int} [sex] The gender code. 0 for men. 1 for women
		 * @param {string} [state] The state's two letter abbreviation to target
		 * @return {array|boolean} An array containing the number of rows returned and info about the users
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

			if($count == 1) {
				foreach($query->result() as $row) {
					// Get the user's match count
					$match_count = $this->GetMatchCount($row->tinder_id);

					$return[$i] = array('tinder_id' => $row->tinder_id,
										'name' => $row->first_name,
										'username' => $row->username,
										'gender' => $row->gender,
										'age' => $row->age,
										'bio' => $row->bio,
										'pic' => $row->profile_pic,
										'seen_id' => $row->seen_id,
										'seen_by_id' => $row->seen_by_id,
										'lon' => $row->lon,
										'lat' => $row->lat,
										'city' => $row->city,
										'state' => $row->state,
										'miles_away' => $row->miles_away,
										'datetime' => $row->datetime,
										'match_count' => $match_count);

					$i++;
				}

				return array('count' => $count, 'hot' => $return);
			} else {
				return FALSE;
			}
		}

		/**
		 * Query the DB to get info about a given user
		 * @param {string} [id] The Tinder ID of the targetted user
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetUserInfo($id) {
			$sql = "SELECT users.tinder_id, users.first_name, users.username, users.dob, users.age, users.bio, users.gender, users.profile_pic, users.last_activity_date, users.twitter_username, users.twitter_id, pics.*
					FROM users
					JOIN pics 
					ON users.tinder_id = pics.tinder_id
					WHERE users.tinder_id = ?
					OR users.username = ?
					ORDER BY pic_order ASC";
			$query = $this->db->query($sql, array($id, $id));
			$count = $query->num_rows();
			$i = 0;

			if($count > 0) {
				$return = [];

				foreach($query->result() as $row) {
					if($i == 0) {
						$return = array('tinder_id' => $row->tinder_id,
										'distance' => $this->GetLastSeen($row->tinder_id),
										'username' => $row->username,
										'link' => FormatUserLink($id, $row->username),
										'name' => $row->first_name,
										'gender' => $row->gender,
										'gender_format' => FormatGender($row->gender),
										'dob' => $row->dob,
										'age' => $row->age,
										'bio' => BioLinks(BioDefault($row->bio, $row->first_name)),
										'last_activity_date' => $row->last_activity_date,
										'last_active_format' => FormatTime($row->last_activity_date),
										'profile_pic' => $row->profile_pic,
										'twitter_handle' => $row->twitter_username,
										'twitter_id' => $row->twitter_id);
					}

					$return['pics'][$i] = array('file' => $row->filename, 'order' => $row->pic_order);
		
					$i++;
				}

				return $return;
			} else {
				return FALSE;
			}
		}

		/**
		 * Update/Insert rows into the users and/or settings table
		 * @param {array} [user_data] An array containing info about a given user. Contains info that will be inserted into the users table 
		 * @param {array} [settings_data] An array containing info about a given user. Contains info that will be inserted into the settings table 
		 * @return {array} An array containing the user ID and username of the user
		 */
		public function InsertUser($user_data, $settings_data = NULL) {
			$this->db->select('id, username');
			$this->db->where('tinder_id', $user_data['tinder_id']);
			$query = $this->db->get('users');
			
			// Insert
			if($query->num_rows() == 0) {
				$this->db->insert('users', $user_data);

				if($settings_data) {
					$settings_data['tinder_id'] = $user_data['tinder_id'];
					$this->db->insert('settings', $settings_data);
				}
				return array('user_id' => $this->db->insert_id(), 'username' => NULL);
			} else {
				// Update
				$row = $query->row_array();
				$this->db->where('tinder_id', $user_data['tinder_id']);
				$this->db->update('users', $user_data);

				if($settings_data) {
					$this->db->where('tinder_id', $user_data['tinder_id']);
					$this->db->update('settings', $settings_data);
				}
				return array('user_id' => $row['id'], 'username' => $row['username']);
			}
		}

		/**
		 * Query the DB to get all the users from the users table
		 * @return {array|boolean} An array containing the links, names and ages of the users
		 */
		public function GetAllUsers() {
			$sql = "SELECT username, tinder_id, first_name, age, profile_pic 
					FROM users";
			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			if($count > 0) {
				foreach($query->result() as $row) {
					$return[$i] = array('id' => $row->tinder_id,
										'link' => FormatUserLink($row->tinder_id, $row->username),
										'name' => $row->first_name,
										'age' => $row->age,
										'pic' => $row->profile_pic);

					$i++;
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
		 * @return {array} An array containing the number of rows returned and the avg age
		 */
		public function GetUsersInState($state, $gender = NULL) {
			$data = array($state);

			$sql = "SELECT AVG(users.age) AS age, COUNT(users.id) AS count
					FROM users
					JOIN last_seen
					ON users.tinder_id = last_seen.seen_id
					WHERE last_seen.state = ?";

			if($gender) {
				$sql .= " AND users.gender = ?";
				array_push($data, $gender);
			}

			$query = $this->db->query($sql, $data);
			$result = $query->result();
			$count = $result[0]->count;
			return array('count' => $count, 'avg_age' => ceil($result[0]->age));
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
		 * @return {int} The number of rows returned
		 */
		public function CheckUsername($username, $tinder_id) {
			$sql = "SELECT id FROM users
					WHERE username = ? AND tinder_id != ?";
			$query = $this->db->query($sql, array($username, $tinder_id));
			return $query->num_rows();
		}

		/**
		 * Query the DB to see if a user with a given Tinder ID exist in the users table
		 * @param {string} [id] The Tinder ID of the targetted user
		 * @return {int} The number of rows returned
		 */
		public function UserExists($id) {
			$this->db->select('id');
			$this->db->where('tinder_id', $id);
			$query = $this->db->get('users');
			return $query->num_rows();
		}

		/**
		 * Unmatch a relationship
		 * @param {string} [id] The match ID
		 */
		public function UnmatchUser($by, $id) {
			$data = array('unmatched' => 1, 'unmatched_by' => $by);
			$this->db->where('match_id', $id);
			$this->db->update('likes', $data);	
		}

		/**
		 * Sync all of the messages from a given thread with Twinder's DB
		 * @param {array} [messages] An array from Tinder's 'matches' API endpoint
		 */
		public function UpdateThread($messages, $count) {
			// Loop thru each message
			for($i=0;$i<$count;$i++) {
				if(array_key_exists($i, $messages)) {
					$msg = trim($messages[$i]['message']);

					if(!empty($messages[$i]['match_id']) && !empty($msg)) {
						$id = $messages[$i]['match_id'];
						$to = $messages[$i]['to'];
						$from = $messages[$i]['from'];
						$time = $messages[$i]['sent_date'];
						$raw = $messages[$i]['message'];

						// See if there is a record of each message existing in the DB
						$params = array('match_id' => $id, 'msg' => $msg, 'user_to' => $to, 'user_from' => $from);
						$this->db->select('id');
						$this->db->where($params);
						$query = $this->db->get('msg');
						$num = $query->num_rows();

						if($num == 0) {
							$data = array('match_id' => $id,
										'msg' => $raw,
										'user_from' => $from,
										'user_to' => $to,
										'datetime' => strtotime($time));
							$this->db->insert('msg', $data);
							// FormatArray($data);
						}
					}
				}
			}
		}

		/**
		 * Loop thru an array of Tweets and insert them into the DB
		 * @param {string} [username] The Twitter handle of the user
		 * @param {string} [tweets] An array containing Tweets from a given user
		 */
		public function SyncTweets($twitter_id, $tweets) {
			for($i=0;$i<count($tweets);$i++) {
				$data = array('twitter_id' => $twitter_id,
							'tweet_id' => $tweets[$i]['id'],
							'tweet' => $tweets[$i]['text'],
							'retweet_count' => $tweets[$i]['retweet_count'],
							'favorite_count' => $tweets[$i]['favorite_count'],
							'datetime' => strtotime($tweets[$i]['created_at']),
							'pic' => $tweets[$i]['user']['profile_image_url_https'],
							'username' => $tweets[$i]['user']['screen_name'],
							'name' => $tweets[$i]['user']['name']);

				// Check to see if the tweet already exists
				$this->db->select('id');
				$this->db->where('tweet_id', $tweets[$i]['id']);
				$query = $this->db->get('tweets');

				if($query->num_rows() == 0) {
					// Check to see if a status was retweeted
					if(array_key_exists('retweeted_status', $tweets[$i])) {
						$rt = $tweets[$i]['retweeted_status'];
						$data['retweet_status'] = 1;
						$data['retweet_id'] = $rt['id'];
						$data['retweet'] = $rt['text'];
						$data['retweet_date'] = strtotime($rt['created_at']);
						$data['pic'] = $rt['user']['profile_image_url_https'];
						$data['retweet_username'] = $rt['user']['screen_name'];
						$data['retweet_name'] = $rt['user']['name'];
						$data['retweet_count'] = $rt['retweet_count'];
						$data['favorite_count'] = $rt['favorite_count'];
					} 

					// Get the media elements
					$entities = $tweets[$i]['entities'];
					
					if(array_key_exists('media', $entities)) {
						if(count($entities['media']) > 0) {
							$data['media'] = 1;
							$data['url'] = $entities['media'][0]['media_url_https'];
						} else {
							$data['media'] = 0;
							$data['url'] = NULL;
						}
					}

					// Determine if there were any replies
					$data['reply'] = (!empty($tweets[$i]['in_reply_to_status_id']) ? 1 : NULL);

					// Insert the row into the DB
					$this->db->insert('tweets', $data);
				}
			}
		}

		/**
		 * Query the DB to find out how many tweets a user has
		 * @param {int} [twitter_id] The user's Twitter ID
		 * @return {int} The number of rows returned
		 */
		public function GetTweetCount($twitter_id) {
			$this->db->select('id');
			$this->db->where('twitter_id', $twitter_id);
			$query = $this->db->get('tweets');
			return $query->num_rows();
		}

		/**
		 * Query the DB for a user's tweets
		 * @param {int} [twitter_id] The user's Twitter ID
		 * @param {boolean} [reply] Whether to include replies or not
		 * @param {boolean} [media] Whether to include pics and videos or not
		 * @param {string} [q] The query string
		 */
		public function GetTweets($twitter_id, $reply, $media, $q = NULL) {
			$params = array('twitter_id' => $twitter_id);

			$sql = "SELECT *
					FROM tweets
					WHERE twitter_id = ?";

			if(!$reply) {
				$sql .= " AND reply IS NULL";
			}

			if($media) {
				$sql .= " AND media = ?";
				array_push($params, 1);
			}

			if($q) {
				$sql .= " AND tweet LIKE ?";
				array_push($params, '%'.$q.'%');
			}

			$query = $this->db->query($sql, $params);
			$count = $query->num_rows();
			$i = 0;

			$return = [];

			foreach($query->result() as $row) {
				if($row->retweet_status == 1) {
						$retweet = array('tweet' => $row->retweet,
										'time' => date('', $row->retweet_date),
										'username' => $row->retweet_username,
										'name' => $row->retweet_name);
				} else {
					$retweet = FALSE;
				}

				$return[$i] = array('tweet' => $row->tweet,
									'time' => date('', $row->datetime),
									'name' => $row->name,
									'username' => $row->username,
									'pic' => $row->pic,
									'retweet' => $retweet,
									'fav_count' => $row->favorite_count,
									'rt_count' => $row->retweet_count,
									'reply' => (!empty($row->reply) ? TRUE : FALSE),
									'media' => ($row->media == 1 ? array('url' => $row->url) : FALSE));

				$i++;
			}

			return array('count' => $count, 'users' => $return);
		}

		/**
		 * Adjust a set of matches as unmatched
		 * @param {string} [my_tinder_id] The Tinder ID of the user who is logged in
		 * @param {array} [blocks] An array containing the match ID of each unmatched match
		 */
		public function UpdateBlocks($my_tinder_id, $blocks) {
			for($i=0;$i<count($blocks);$i++) {
				// Get the Tinder ID of the person who started the block
				$this->db->select('user_one, user_two');
				$this->db->where('match_id', $blocks[$i]);
				$query = $this->db->get('likes');
				$num = $query->num_rows();

				if($num > 0) {
					foreach($query->result() as $row) {
						$user_one = $row->user_one;
						$user_two = $row->user_two;
					}
				
					// Determine who unmatched who
					if($user_one == $my_tinder_id) {
						$unmatched_by = $user_two;
					} else {
						$unmatched_by = $user_one;
					}

					// Update the row in the DB
					$this->UnmatchUser($unmatched_by, $blocks[$i]);
				}
			}
		}
	}