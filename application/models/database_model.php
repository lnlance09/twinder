<?php 
	class Database_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();
		}

		/**
		 * Check to see if there is a record existing of a given user reporting another user
		 * @param {string} [my_id] The Tinder ID of the user who is logged in 
		 * @param {string} [his_id] The Tinder ID of the targetted user
		 * @return {boolean} 
		 */
		public function CheckReport($my_id, $his_id) {
			if($my_id != $his_id) {
				$this->db->select('COUNT(*) AS count');
				$this->db->where(array('reported_by' => $my_id, 'user_reported' => $his_id));
				$query = $this->db->get('reports')->result();
				return ($query[0]->count == 0 ? TRUE : FALSE);
			} else {
				return FALSE;
			}
		}

		/**
		 * Check to see if a given username is available
		 * @param {string} [username] The username
		 * @return {int} The number of rows returned
		 */
		public function CheckUsername($username, $tinder_id) {
			$sql = "SELECT COUNT(*) AS count FROM users WHERE username = ? AND tinder_id != ?";
			$query = $this->db->query($sql, array($username, $tinder_id))->result();
			return $query[0]->count;
		}

		/**
		 * Check to see if the user voted for this user
		 * @param {string} [my_id] My Tinder ID
		 * @param {string} [his_id] His Tinder ID
		 * @return {int} The number of rows returned
		 */
		public function CheckVote($my_id, $his_id) {
			$this->db->select('action');
			$this->db->where(array('user_one' => $my_id, 'user_two' => $his_id));
			$query = $this->db->get('votes');
			// echo $count;

			if($query->num_rows() == 1) {
				$result = $query->result();
				return (int)$result[0]->action;
			} else {
				return FALSE;
			}
		}

		/**
		 * Create a row in the last_seen table
		 * @param {array} [data] The values for the columns
		 */
		public function CreateLastSeen($data) {
			$this->db->insert('last_seen', $data);
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
		public function EditLastSeen($my_tinder_id, $his_tinder_id, $distance, $lon, $lat) {
			// Check to see if each user has a row existing in the last_seen table
			$last = $this->GetLastSeen($his_tinder_id);
			
			if($lon && $lat) {
				if($last) {
					// Make sure the user isn't updating their own profile and the user is logged in
					if(!empty($my_tinder_id) && $his_tinder_id != $my_tinder_id) {
						// Make sure the user's last location isn't one from a ping
						if($last['seen_id'] != $last['seen_by_id']) {
							// Check to see if your proximity is closer than the one currently on record
							if($distance < $last['miles_away']) {
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
			}

			// Get info about the person who saw this user
			$user = $this->GetUserInfo($last['seen_by_id']);
			return array('user' => $user, 'data' => $last);
		}

		/**
		 * Query the DB to get all the users from the users table
		 * @return {array|boolean} An array containing the links, names and ages of the users
		 */
		public function GetAllUsers($limit = NULL) {
			$this->db->select('username,tinder_id,first_name,age');
			$this->db->order_by('id', 'RANDOM');
			
			if($limit) {
				$this->db->limit($limit);
			}
			
			$query = $this->db->get('users');
			$i = 0;

			if($query->num_rows() > 0) {
				foreach($query->result() as $row) {
					$return[$i] = array('id' => $row->tinder_id,
										'link' => FormatUserLink($row->tinder_id, $row->username),
										'name' => $row->first_name,
										'age' => $row->age);
					$i++;
				}
				
				return $return;
			} else {
				return FALSE;
			}
		}

		/**
		 * Get the user that is next in line to be either liked or passed
		 * @param {int} [id] The Tinder ID of the Tinder user 
		 * @return {int} [tinder_id] The Tinder ID of the next batch user
		 */
		public function GetBatchUser($id) {
			$this->db->select('tinder_id');
			$this->db->where('my_tinder_id', $id);
			$this->db->limit(1);
			$query = $this->db->get('batches');
			$result = $query->result();
			return $result[0]->tinder_id;
		}

		/**
		 * Return an array contaning users that have been filtered by their location
		 * @param {int} [sex] The gender of the search
		 * @param {int} [min] The minimum age
		 * @param {int} [max] The maximum age
		 * @param {string} [q] The query string
		 * @param {decimal} [lon] The longitude coordinate
		 * @param {decimal} [lat] The latitude coordinate
		 * @param {int} [distance] The distance filter value in miles
		 * @param {int} [end] The ending point
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetHottest($sex, $min, $max, $q, $lon, $lat, $distance, $end) {
			if($end) {
				$this->db->select('tinder_id,first_name,age,username,profile_pic,bio');
			} else {
				$this->db->select('users.id');
			}
			
			if(!empty($lon) && !empty($lat)) {
				$this->db->select(", (3959 * acos(cos(radians(".$lat.")) * cos(radians(lat)) * cos(radians(lon) - radians(".$lon.")) + sin(radians(".$lat.")) * sin(radians(lat)))) AS distance");
			}

			$this->db->join('last_seen', 'users.tinder_id = last_seen.seen_id');

			if($sex != -1) {
				$this->db->where('gender', $sex);
			}

			if($min > 18) {
				$this->db->where('age >=', $min);
			}

			if($max < 50) {
				$this->db->where('age <=', $max);
			}

			if(!empty($q)) {
				$this->db->like('first_name', $q);
				$this->db->or_like('bio', $q);
			}

			if(!empty($lon) && !empty($lat)) {
				$this->db->where('lat BETWEEN '.$lat.' -2 AND '.$lat.' +2');
				$this->db->where('lon BETWEEN '.$lon.' -2 AND '.$lon.' +2');
				$this->db->having('distance <=', $distance);
			}

			if($end) {
				$this->db->limit($end);
			}

			$query = $this->db->get('users');

			if($end) {
				$data = [];
				$i = 0;

				foreach($query->result() as $row) {
					$data[$i] = array('tinder_id' => $row->tinder_id,
									'name' => $row->first_name,
									'age' => $row->age,
									'bio' => BioDefault($row->bio, $row->first_name),
									'pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username),
									'distance' => (!empty($lon) && !empty($lat) ? $row->distance : NULL));
					$i++;
				}

				return array('count' => $query->num_rows(), 'users' => $data);
			} else {
				return $query->num_rows();
			}
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
				$row = $query->result();
				return array('msg' => $row[0]->msg, 
							'time' => $row[0]->datetime, 
							'time_format' => FormatTime(date('F d', $row[0]->datetime)));
			} else {
				return FALSE;
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
		 * Get the number of likes a given user has or the number of users that like a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the user's like count or liked_by count. TRUE for liked_by count. FALSE for like_count
		 * @param {string} [q] The query string to search the user's first name
		 * @return {int} The number of rows returned from the query
		 */
		public function GetLikeCount($tinder_id, $inverse, $q = NULL) {
			$params = array($tinder_id);
			$sql = "SELECT users.id
					FROM users
					JOIN likes";

			if($inverse) {
				$sql .= " ON users.tinder_id = likes.user_one WHERE likes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = likes.user_two WHERE likes.user_one = ?";
			}

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
				array_push($params, '%'.trim($q).'%');
			}

			$sql .= " GROUP BY users.tinder_id";
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

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
				array_push($params, '%'.trim($q).'%');
			}

			$sql .= " GROUP BY users.tinder_id ORDER BY likes.datetime DESC";
			$query = $this->db->query($sql, $params);
			$return = [];
			$i = 0;

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

			return array('count' => $query->num_rows(), 'users' => $return);
		}

		/**
		 * Get the number of matches that a given user has
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [q] The query string to match the user's first name with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMatchCount($tinder_id, $q = NULL) {
			$sql = "SELECT users.id
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_two
					WHERE likes.match_id IS NOT NULL 
					AND likes.unmatched IS NULL
					AND likes.user_one = ?";

			if(!empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$sql .= " GROUP BY users.tinder_id";
			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
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
					WHERE likes.match_id IS NOT NULL 
					AND likes.unmatched IS NULL
					AND likes.user_one = ?";

			if(!empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$sql .= "GROUP BY users.tinder_id";
			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
			$count = $query->num_rows();
			$return = [];
			$i = 0;

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
		 * Querty the DB to get info about a given match
		 * @param {string} [id] The match ID being targetted
		 * @return {array|boolean} An array containing the Tinder ID's of the two users in the match
		 */
		public function GetMatchInfo($id) {
			$sql = "SELECT tinder_id, first_name, age, profile_pic, username, users.views, unmatched, unmatched_by, datetime
					FROM users
					JOIN likes
					ON users.tinder_id = likes.user_one
					WHERE likes.match_id = ?";
			$query = $this->db->query($sql, array($id));
			
			if($query->num_rows() == 2) {
				$i = 0;
				foreach($query->result() as $row) {
					$data[$i] = array('id' => $row->tinder_id,
									'name' => $row->first_name,
									'age' => $row->age,
									'link' => FormatUserLink($row->tinder_id, $row->username),
									'pic' => $row->profile_pic,
									'views' => $row->views,
									'unmatched' => $row->unmatched,
									'unmatched_by' => $row->unmatched_by,
									'created_at' => FormatTime(date('Y/m/d', $row->datetime)));

					$i++;
				}

				return array('user_one' => $data[0], 'user_two' => $data[1], 'created_at' => $data[1]['created_at']);
			} else {
				return FALSE;
			} 
		}

		/**
		 * Return a number representing that two users have both liked
		 * @param {string} [my_id] The Tinder ID of the user is currently logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match users' first names with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMutualLikeCount($my_id, $his_id, $q = NULL) {
			$params = array($my_id, $his_id);
			$sql = "SELECT COUNT(*) AS count
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

			$sql .= " GROUP BY users.tinder_id";
			$query = $this->db->query($sql, $params);
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
			$sql = "SELECT *
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

			$sql .= " GROUP BY users.tinder_id";
			$query = $this->db->query($sql, $params);
			$return = [];
			$i = 0;

			foreach($query->result() as $row) {
				$return[$i] = array('tinder_id' => $row->tinder_id,
									'first_name' => $row->first_name,
									'username' => $row->username,
									'bio' => $row->bio,
									'profile_pic' => $row->profile_pic,
									'link' => FormatUserLink($row->tinder_id, $row->username),
									'age' => $row->age);

				$i++;
			}

			return array('count' => $query->num_rows(), 'users' => $return);
		}

		/**
		 * Get the number of matches that two user have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the user's first name with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMutualMatchCount($my_id, $his_id, $q = NULL) {
			$params = array($my_id, $his_id);
			$sql = "SELECT id
					FROM users
					INNER JOIN likes
					ON likes.user_two = users.tinder_id
					WHERE likes.user_one IN (
					    SELECT user_one
					    FROM likes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 2)
					AND likes.match_id IS NOT NULL
					GROUP BY users.tinder_id";

			if(!empty($q)) {
				$sql .= " AND u.first_name LIKE ?";
				array_push($params, '%'.trim($q).'%');
			}

			$query = $this->db->query($sql, $params);
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
			$sql = "SELECT *
					FROM users
					INNER JOIN likes
					ON likes.user_two = users.tinder_id
					WHERE likes.user_one IN (
					    SELECT user_one
					    FROM likes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 4)";

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$sql .= " GROUP BY users.tinder_id";
			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			$return = [];
			$i = 0;

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

			return array('count' => $query->num_rows(), 'users' => $return);
		}

		/**
		 * Get the number of passes that two users have in common
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [his_id] The Tinder ID of the other user
		 * @param {string} [q] The query string to match the users' first names with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetMutualPassCount($my_id, $his_id, $q = NULL) {
			$sql = "SELECT id
					FROM users
					INNER JOIN passes
					ON passes.user_two = users.tinder_id
					WHERE passes.user_one IN (
					    SELECT user_one
					    FROM passes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 2)";

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$sql .= " GROUP BY users.tinder_id";
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
			$sql = "SELECT *
					FROM users
					INNER JOIN passes
					ON passes.user_two = users.tinder_id
					WHERE passes.user_one IN (
					    SELECT user_one
					    FROM passes
					    WHERE user_one IN (?, ?)
					    GROUP BY user_one
					    HAVING COUNT(*) = 2)";

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$sql .= " GROUP BY users.tinder_id";
			$query = $this->db->query($sql, array($my_id, $his_id, '%'.trim($q).'%'));
			$return = [];
			$i = 0;

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

			return array('count' => $query->num_rows(), 'users' => $return);
		}

		/**
		 * Get the number of passes that a given user has gotten or the number of users that have passed a given user
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {boolean} [inverse] Whether or not to get the number of passes that a given user has gotten. 
		 * @param {string} [q] The query string to match the users' first names with
		 * @return {int} The number of rows returned from the query
		 */
		public function GetPassCount($tinder_id, $inverse, $q = NULL) {
			$sql = "SELECT COUNT(*) AS count
					FROM users
					JOIN passes";

			if($inverse) {
				$sql .= " ON users.tinder_id = passes.user_one WHERE passes.user_two = ?";
			} else {
				$sql .= " ON users.tinder_id = passes.user_two WHERE passes.user_one = ?";
			}

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'))->result();
			return $query[0]->count;
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

			if($q && !empty($q)) {
				$sql .= " AND users.first_name LIKE ?";
			}

			$sql .= " GROUP BY users.tinder_id";
			$query = $this->db->query($sql, array($tinder_id, '%'.trim($q).'%'));
			$return = [];
			$i = 0;

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

			return array('count' => $query->num_rows(), 'users' => $return);
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
			$return = [];
			$i = 0;

			foreach($query->result() as $row) {
				$return[$i] = array('lon' => $row->lon,
									'lat' => $row->lat,
									'city' => $row->city,
									'state' => $row->state,
									'country' => $row->country,
									'datetime' => $row->datetime);

				$i++;
			}

			return array('count' => $query->num_rows(), 'pings' => $return);
		}

		/**
		 * Get all of the messages from a given thread
		 * @param {string} [match_id] The thread ID
		 * @return {array|boolean} An array containing all of the messages between two users
		 */
		public function GetThread($match_id) {
			$this->db->select('*');
			$this->db->where('match_id', $match_id);
			$this->db->order_by('datetime', 'ASC');
			$query = $this->db->get('msg');
			$return = [];
			$i = 0;

			foreach($query->result() as $row) {
				$return[$i] = array('to' => $row->user_from,
									'from' => $row->user_to,
									'message' => $row->msg,
									'datetime' => date('n/j/y g:i a', $row->datetime));

				$i++;
			}

			return array('count' => $query->num_rows(), 'data' => $return);
		}

		/**
		 * Query the DB to get info about a given user
		 * @param {string} [id] The Tinder ID of the targetted user
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetUserInfo($id) {
			$sql = "SELECT users.tinder_id, first_name, username, dob, age, bio, gender, profile_pic, last_activity_date, ig_username, views, pics.filename
					FROM users
					JOIN pics 
					ON users.tinder_id = pics.tinder_id
					WHERE users.tinder_id = ?
					OR users.username = ?
					ORDER BY pic_order ASC";
			$query = $this->db->query($sql, array($id, $id));

			if($query->num_rows() > 0) {
				$i = 0;
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
										'bio' => BioDefault($row->bio, $row->first_name),
										'last_activity_date' => $row->last_activity_date,
										'last_active_format' => FormatTime($row->last_activity_date),
										'profile_pic' => $row->profile_pic,
										'ig_username' => $row->ig_username,
										'views' => $row->views);
					}

					$return['pics'][$i] = array('file' => $row->filename);
		
					$i++;
				}

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
		public function GetUsersInState($state) {
			$sql = "SELECT AVG(users.age) AS age, COUNT(users.id) AS count, users.gender
					FROM users
					JOIN last_seen
					ON users.tinder_id = last_seen.seen_id
					WHERE last_seen.state = ?
					GROUP BY users.gender";
			$query = $this->db->query($sql, array($state));

			foreach($query->result() as $row) {
				$key = ($row->gender == 0 ? 'female' : 'male');
				$data[$key] = array('count' => $row->count, 'avg_age' => ceil($row->age));
			}
			
			$data['total'] = array('count' => $data['male']['count']+$data['female']['count'], 'avg_age' => ceil(($data['male']['avg_age']+$data['female']['avg_age'])/2));
			return $data;
		}

		/**
		 * Return an array containing the counts of all of a given user's categories
		 * @param {string} [tinder_id] The Tinder ID of the targetted user
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @return {array} An array containing the number of rows returned and info about the users
		 */
		public function GetUserStats($tinder_id, $my_id) {
			// This view if for if the user is logged in
			$params = array(array('key' => 'likes', 'name' => 'likes', 'count' => NULL),
							array('key' => 'matches', 'name' => 'matches', 'count' => NULL),
							array('key' => 'passes', 'name' => 'passes', 'count' => NULL),);

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
				}

				// Set the count key to each element in the array
				$params[$i]['count'] = $count;
			}

			return $params;
		}

		/**
		 * Query the DB to get the number of users that a given user has favorited
		 * @param {str} [id] The Tinder ID of the user
		 * @return {array} An array containing the up, down vote counts and total
		 */
		public function GetVoteStats($id) {
			// Get the downvote count
			$this->db->select('COUNT(*) AS count');
			$this->db->where(array('user_two' => $id, 'action' => 1));
			$query = $this->db->get('votes')->result();
			$up = $query[0]->count;

			// Get the upvote count
			$this->db->select('COUNT(*) AS count');
			$this->db->where(array('user_two' => $id, 'action' => 0));
			$query = $this->db->get('votes')->result();
			$down = $query[0]->count;
			$total = $up+$down;

			return array('up' => $up, 
						'down' => $down, 
						'total' => $total,
						'up_pct' => ($total == 0 ? 0 : ceil(($up/$total)*100)),
						'down_pct' => ($total == 0 ? 0 : ceil(($down/$total)*100)));
		}

		/**
		 * Get the hottest male or female in a given state
		 * @param {string} [state] The state's two letter abbreviation to target
		 * @return {array|boolean} An array containing the number of rows returned and info about the users
		 */
		public function HottestByState($state) {
			$sql = "SELECT users.tinder_id, users.first_name, users.age, users.profile_pic, users.username, users.gender, COUNT(votes.*) AS count
					FROM votes 
					LEFT JOIN users ON votes.user_two = users.tinder_id
					RIGHT JOIN last_seen ON votes.user_two = last_seen.seen_id
					WHERE last_seen.state = ?
					GROUP BY users.gender
					ORDER BY count LIMIT 2";
			$query = $this->db->query($sql, array($state));

			if($query->num_rows() > 0) {
				$i = 0;
				foreach($query->result() as $row) {
					$key = ($row->gender == 0 ? 'mr' : 'mrs');
					$data[$key] = array('tinder_id' => $row->tinder_id,
										'name' => $row->first_name,
										'username' => $row->username,
										'age' => $row->age,
										'pic' => $row->profile_pic,
										'votes' => $row->count);

					$i++;
				}

				// FormatArray($data);
				return($data);
			} else {
				return FALSE;
			}
		}

		/**
		 * Insert a batch of new users from the discovery process into the DB
		 * @param {string} [tinder_id] The Tinder ID of the user who is logged in
		 * @param {array} [info] An array of users that was obtained from Tinder's API
		 * @param {decimal} [lon] The longitude coordinate of the user who is logged in
		 * @param {decimal} [lat] The latitude coordinate of the user who is logged in
		 */
		public function InsertBatch($tinder_id, $info, $lon, $lat) {
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
				$this->db->select('COUNT(*) AS count');
				$this->db->where(array('tinder_id' => $info[$i]['tinder_id'], 'my_tinder_id' => $tinder_id));
				$result = $this->db->get('batches')->result();
			
				if($result[0]->count == 0) {
					$data = array('my_tinder_id' => $tinder_id, 'tinder_id' => $info[$i]['tinder_id']);
					$this->db->insert('batches', $data);
				}

				// Update the last seen location
				$this->EditLastSeen($tinder_id, $info[$i]['tinder_id'], $info[$i]['distance'], $lon, $lat);

				// Insert the user's pics
				$this->InsertPics($info[$i]['tinder_id'], $info[$i]['pics']);	
			}
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
					// If there is no record, then create one
					if($match_id != 'false' && !empty($match_id)) {
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
		 * Insert a row into the passes table
		 * @param {string} [my_id] The Tinder ID of the user who is logged in
		 * @param {string} [tinder_id] The Tinder ID of the user who is being passes
		 */
		public function InsertIntoPasses($my_id, $tinder_id) {
			$data = array('user_one' => $my_id, 'user_two' => $tinder_id, 'datetime' => date('Y-m-d H:i:s'));
			$this->db->insert('passes', $data);
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
		 * Insert a row into the messages table
		 * @param {array} [data] The array containing the column keys and values
		 */
		public function InsertMessage($data) {
			$info = $data;
			if(array_key_exists('datetime', $data)) {
				unset($data['datetime']);
			} 

			$this->db->select('COUNT(*) AS count');
			$this->db->where($data);
			$query = $this->db->get('msg')->result();
			
			if($query[0]->count == 0) {
				$this->db->insert('msg', $info);
			}
		}
		
		/**
		 * Insert a user's picture into the pics table
		 * @param {string} [id] The Tinder ID of the user whose pics are being inserted
		 * @param {array} [pics] An array containing all the user's pictures
		 */
		public function InsertPics($id, $pics) {
			for($i=0;$i<count($pics);$i++) {
				$this->db->select('COUNT(*) AS count');
				$this->db->where(array('tinder_id' => $id, 'filename' => $pics[$i]));
				$query = $this->db->get('pics')->result();

				if($query[0]->count == 0) {
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
		 * Insert a row into the reports table. This reflects that one user has reported another
		 * @param {string} [my_id] The Tinder ID of the user who is logged in 
		 * @param {string} [his_id] The Tinder ID of the targetted user
		 */
		public function InsertReport($my_id, $his_id) {
			$data = array('reported_by' => $my_id, 'user_reported' => $his_id, 'datetime' => date('Y-m-d H:i:s'));
			$this->db->insert('reports', $data);
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
				$id = $this->db->insert_id();

				if($settings_data) {
					$settings_data['tinder_id'] = $user_data['tinder_id'];
					$this->db->insert('settings', $settings_data);
				}
				return array('user_id' => $id, 'username' => NULL);
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
		 * Insert a row into the favorites table. First check to see if a row already exists
		 * @param {str} [my_id] My Tinder ID
		 * @param {str} [his_id] The other user's Tinder ID
		 * @param {int} [action] Either 0 for pass or 1 for like
		 */
		public function InsertVote($my_id, $his_id, $action) {
			if(!empty($my_id) && !empty($his_id)) {
				$check = $this->CheckVote($my_id, $his_id);
				
				if($check === FALSE) {
					$data = array('user_one' => $my_id, 'user_two' => $his_id, 'action' => $action, 'datetime' => date('Y-m-d H:i:s'));
					$this->db->insert('votes', $data);
					return 'true';
				} else {
					return 'false';
				}
			} else {
				return 'false';
			}
		}

		/**
		 * Query the DB to see if a match with a given match ID exists
		 * @param {string} [match_id] The match ID being targetted
		 * @return {int} The number of rows returned from the query
		 */
		public function MatchExists($match_id) {
			$this->db->select('COUNT(*) AS count');
			$this->db->where('match_id', $match_id);
			$query = $this->db->get('likes')->result();
			return $query[0]->count;
		}

		/**
		 * Remove all batch users from the DB
		 * @param {int} [id] The Tinder ID of the user who is currently logged in
		 */
		public function RemoveAllBatch($id) {
			$this->db->delete('batches', array('my_tinder_id' => $id)); 
		}

		/**
		 * Remove a batch user from the batches table
		 * @param {string} [my_id] The Tinder ID of the user who is currently logged in
		 * @param {string} [id] The Tinder ID of the user who is to be removed
		 */
		public function RemoveBatchUser($my_id, $id) {
			$this->db->delete('batches', array('my_tinder_id' => $my_id, 'tinder_id' => $id)); 
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
		 * Sync all of the user's messages from Tinder with WeTinder.
		 * This function also records matches between the user logging in and other Tinder users.
		 * A new row will be inserted/updated into the users table for each person in the updates array
		 * @param {array} [updates] An array that was fetched from Tinder's API containing all of the 
		 * @param {string} [my_tinder_id] The Tinder ID of the user who is logged in
		 * @param {int} [distance] The distance in miles
		 * @param {decimal} [lon] The longitude coordinate of the user who is currently logged in
		 * @param {decimal} [lat] The latitude coordinate of the user who is currently logged in
		 * @param {string} [city] The city of the user who is currently logged in
		 * @param {string} [state] The state of the user who is currently logged in
		 */
		public function SyncMessages($updates, $tinder_id, $distance, $lon, $lat, $city, $state) {
			for($i=0;$i<count($updates);$i++) {
				// Get the match ID for each
				$match_id = $updates[$i]['_id'];

				// Get the info about the other user in the match
				if(array_key_exists('person', $updates[$i])) {
					// Save each person as a variable
					$person = $updates[$i]['person'];
					$created_at = (array_key_exists('created_date', $updates[$i]) ? $updates[$i]['created_date'] : NULL);
					$last_active = (array_key_exists('last_activity_date', $updates[$i]) ? $updates[$i]['last_activity_date'] : NULL);

					// Check to see if there is a record of each match participant in the DB
					if($person) {
						// Insert a row into the users table if necessary
						$user = array('tinder_id' => $person['_id'],
									'first_name' => $person['name'],
									'dob' => date('M j, Y', strtotime($person['birth_date'])),
									'age' => ReturnAge($person['birth_date']),
									'bio' => $person['bio'],
									'gender' => $person['gender'],
									'last_activity_date' => $person['ping_time'],
									'profile_pic' => ReturnProfilePic($person['photos']));
						$this->InsertUser($user);

						// Insert each user's pics
						$this->InsertPics($person['_id'], ReturnPicsArray($person['photos']));
						
						// Insert each user's likes
						$this->InsertIntoLikes($tinder_id, $person['_id'], $match_id, $last_active, $created_at);

						// Check to see if each user has a row existing in the last_seen table
						$last = $this->GetLastSeen($person['_id']);

						// If there is a record of the user existing, then see if your distance to him/her is closer 
						if(empty($last)) {
							$data = array('seen_id' => $person['_id'],
										'seen_by_id' => $tinder_id,
										'lon' => $lon,
										'lat' => $lat,
										'city' => $city,
										'state' => $state,
										'miles_away' => $distance,
										'datetime' => date('Y-m-d H:i:s'));
							$this->CreateLastSeen($data);
						}
					}

					// Insert all of the messages into the msg table
					if(array_key_exists('messages', $updates[$i])) {
						$this->UpdateThread($updates[$i]['messages'], count($updates[$i]['messages']));	
					}
				}	
			}
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

				if($query->num_rows() > 0) {
					foreach($query->result() as $row) {
						$user_one = $row->user_one;
						$user_two = $row->user_two;
					}
				
					// Determine who unmatched who
					$unmatched_by = ($user_one == $my_tinder_id ? $user_two : $user_one);
					
					// Update the row in the DB
					$this->UnmatchUser($unmatched_by, $blocks[$i]);
				}
			}
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
		 * Update a given profile's number of views
		 * @param {int} [views] The number of views a profile currently has
		 * @param {string} [id] The Tinder ID of the targetted user
		 * @return {int} The new number of views
		 */
		public function UpdateProfileViews($views, $id) {
			$new_views = $views+1;
			$this->db->where('tinder_id', $id);
			$this->db->update('users', array('views' => $new_views));
			return $new_views;
		}

		/**
		 * Sync all of the messages from a given thread with Twinder's DB
		 * @param {array} [msgs] An array from Tinder's 'matches' API endpoint
		 */
		public function UpdateThread($msgs, $count) {
			for($i=0;$i<$count;$i++) {
				if(array_key_exists($i, $msgs)) {
					$msg = trim($msgs[$i]['message']);

					if(!empty($msgs[$i]['match_id']) && !empty($msg)) {
						$id = $msgs[$i]['match_id'];
						$to = $msgs[$i]['to'];
						$from = $msgs[$i]['from'];
						$time = $msgs[$i]['sent_date'];
						$raw = $msgs[$i]['message'];

						// Insert the message
						$data = array('match_id' => $id,
									'msg' => $raw,
									'user_from' => $from,
									'user_to' => $to,
									'datetime' => strtotime($time));
						$this->InsertMessage($data);
					}
				}
			}
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
		 * Query the DB to see if a user with a given Tinder ID exist in the users table
		 * @param {string} [id] The Tinder ID of the targetted user
		 * @return {int} The number of rows returned
		 */
		public function UserExists($id) {
			$this->db->select('COUNT(*) AS count');
			$this->db->where('tinder_id', $id);
			$query = $this->db->get('users')->result();
			return $query[0]->count;
		}
	}