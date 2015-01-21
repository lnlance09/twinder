<?php 
	class Database_model extends CI_Model {
		public function __construct() {       
			parent:: __construct();

			// Load the database
			$this->load->database();

			// Load the helpers file
			$this->load->helper('common_helper');
		}

		// CLEAR THE DB
		public function FlushDB() {
			// Flush all of the DBs
			$tinder_id = '5495df819983685e07f138f2';

			$this->db->where('id > "0"');
			$this->db->delete(array('batches', 'last_seen', 'likes', 'msg', 'passes', 'pics', 'pings', 'settings'));

			$this->db->where('tinder_id != "0"');
			$this->db->delete('users');  
		}



		/* BATCH USERS */
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

		public function InsertBatch($user_id, $my_tinder_id, $info, $lon, $lat) {
			for($i=0;$i<count($info);$i++) {
				$this->db->select('id');
				$this->db->where(array('tinder_id' => $info[$i]['tinder_id'], 'user_id' => $user_id));
				$query = $this->db->get('batches');
				$count = $query->num_rows();

				// Check to see if there is a record of the user existing in the batches table
				if($count == 0) {
					// If there isn't then insert a row into the batches table
					$data = array('user_id' => $user_id,
								'tinder_id' => $info[$i]['tinder_id']);
					$this->db->insert('batches', $data);
				}

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

				// Update the last seen location
				$this->EditLastSeen($info[$i]['distance'], $my_tinder_id, $info[$i]['tinder_id'], $lon, $lat);

				// Insert the user's pics
				$this->InsertPics($info[$i]['tinder_id'], $info[$i]['pics']);	
			}
		}

		public function RemoveBatchUser($id, $my_id) {
			$this->db->delete('batches', array('tinder_id' => $id, 'user_id' => $my_id)); 
		}

		public function RemoveAllBatch($id) {
			$this->db->delete('batches', array('user_id' => $id)); 
		}



		/* MESSAGES */
		public function GetMessages($match_id) {
			$this->db->select('*');
			$this->db->where('match_id', $match_id);
			$query = $this->db->get('msg');
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				$id[$i] = $row->id;
				$match_id[$i] = $row->match_id;
				$message[$i] = $row->msg;
				$sent_at[$i] = $row->sent_at;

				$i++;
			}

			$return = array();

			for($i=0;$i<$count;$i++) {
				$return[$i] = array('id' => $id[$i],
									'match_id' => $match_id[$i],
									'msg' => $message[$i],
									'sent_at' => $sent_at[$i]);
			}

			return $return;
		}

		public function GetLastMessage($id) {
			$this->db->select('message, sent_by, is_read, datetime');
			$this->db->where('match_id', $id);
			$this->db->limit(1);
			$query = $this->db->get('msg');
			$count = $query->num_rows();

			if($count == 1) {
				foreach($query->result() as $row) {
					$message = $row->message;
					$sent_by = $row->sent_by;
					$is_read = $row->is_read;
					$datetime = $row->datetime;
				}

				$msg = array('message' => $message,
							'sent_by' => $sent_by,
							'is_read' => $is_read,
							'datetime' => $datetime);
			} else {
				$msg = array();
			}

			return array('count' => $count, 'msg' => $msg);
		}

		public function InsertMessage($match_id, $msg, $tinder_id) {
			$data = array('match_id' => $match_id,
						'message' => $msg,
						'sent_by' => $tinder_id,
						'datetime' => date('Y-m-d H:i:s'));
			$this->db->insert('msg', $data);
		}

		public function SyncMessages($updates, $my_tinder_id, $auth, $distance, $lon, $lat) {
			$count = count($updates);
			// $count = 1;

			for($i=0;$i<$count;$i++) {
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
					// If there isn't, then create a row in the DB for each user
					if($person !== NULL) {
						$user_data = array('tinder_id' => $person['_id'],
										'first_name' => $person['name'],
										'dob' => date('M j, Y', strtotime($person['birth_date'])),
										'age' => ReturnAge($person['birth_date']),
										'bio' => $person['bio'],
										'gender' => $person['gender'],
										'last_activity_date' => $person['ping_time'],
										'profile_pic' => ReturnProfilePic($person['photos']));
						$this->InsertUser($user_data);

						// Define the data that will be used for the insert query
						$data = array('match_id' => $match_id,
									'user_one' => $my_tinder_id,
									'user_two' => $person['_id'],
									'datetime' => $created_at);
						$this->InsertIntoLikes($my_tinder_id, $person['_id'], $match_id);

						// Insert a row into the last_seen table
						$this->EditLastSeen($distance, $my_tinder_id, $person['_id'], $lon, $lat);
					}
				} else {
					$person = NULL;
				}

				// $closed = $updates[$i]['closed'];
				// $dead = $updates[$i]['dead'];
				// $participants = $updates[$i]['participants'];

				// Insert all of the messages
				$messages = $updates[$i]['messages'];

				for($x=0;$x<count($messages);$x++) {
					$from = $messages[$x]['from'];
					$to = $messages[$x]['to'];
					$msg = $messages[$x]['message'];
					$time = $messages[$x]['timestamp'];

					// Query the DB to see if there is already a record of this message existing in the DB
					$where = array('sent_by' => $from,
									'sent_to' => $to,
									'message' => $msg,
									'timestamp' => $time);

					$this->db->select('id');
					$this->db->where($where);
					$query = $this->db->get('msg');
					$num = $query->num_rows();

					// If not, then insert each message
					if($num == 0) {
						$data = array('match_id' => $match_id,
									'message' => $msg,
									'sent_by' => $from,
									'sent_to' => $to,
									'timestamp' => $time);
						$this->db->insert('msg', $data);
					}
				}
			}
		}


		/* LAST SEEN */
		public function GetLastSeen($tinder_id) {
			$this->db->select('*');
			$this->db->where('seen_id', $tinder_id);
			$query = $this->db->get('last_seen');
			$count = $query->num_rows();

			if($count == 1) {
				foreach($query->result() as $row) {
					$id = $row->id;
					$seen_id = $row->seen_id;
					$by_id = $row->seen_by_id;
					$lon = $row->lon;
					$lat = $row->lat;
					$city = $row->city;
					$state = $row->state;
					$miles = $row->miles_away;
					$datetime = $row->datetime;
				}

				return array('id' => $id,
							'seen_id' => $seen_id,
							'seen_by_id' => $by_id,
							'lon' => $lon,
							'lat' => $lat,
							'city' => $city,
							'state' => $state,
							'miles_away' => $miles,
							'datetime' => $datetime);
			} else {
				return FALSE;
			}
		}

		public function EditLastSeen($my_distance, $my_tinder_id, $his_tinder_id, $lon, $lat) {
			// Check to see if each user has a row existing in the last_seen table
			$last = $this->GetLastSeen($his_tinder_id);

			// If there is a record of the user existing, then see if your distance to him/her is closer 
			if($last !== FALSE) {
				// Make sure the user's last location ins't one from a ping
				if($last['seen_id'] != $last['seen_by_id']) {
					// Check to see if your proximity is closer than the one currently on record
					if($my_distance < $last['miles_away']) {
						// Get the latitude and longitude coordinates
						$loc = GeoLocation($lon, $lat);
						$city = $loc['results'][0]['address_components'][3]['long_name'];
						$state = $loc['results'][0]['address_components'][5]['short_name'];

						$data = array('seen_id' => $his_tinder_id,
									'seen_by_id' => $my_tinder_id,
									'lon' => $lon,
									'lat' => $lat,
									'city' => $city,
									'state' => $state,
									'miles_away' => $my_distance,
									'datetime' => date('Y-m-d H:i:s'));
						$this->UpdateLastSeen($his_tinder_id, $data);
						$return_data = $data;
					} else {
						$return_data = $last;
					}
				} else {
					$return_data = $last;
				}
			} else {
				// Get the latitude and longitude coordinates
				$loc = GeoLocation($lon, $lat);
				FormatArray($loc);
				$city = $loc['results'][0]['address_components'][3]['long_name'];
				$state = $loc['results'][0]['address_components'][5]['short_name'];

				// Create a new row in the last_seen table for this user
				$data = array('seen_id' => $his_tinder_id,
							'seen_by_id' => $my_tinder_id,
							'lon' => $lon,
							'lat' => $lat,
							'city' => $city,
							'state' => $state,
							'miles_away' => $my_distance,
							'datetime' => date('Y-m-d H:i:s'));
				$this->CreateLastSeen($data);
				$return_data = $data;
			}

			//FormatArray($return_data);
			// Get info about the person who saw this user
			$user = $this->GetUserInfo($return_data['seen_by_id']);
			return array('user' => $user, 'data' => $return_data);
		}

		public function UpdateLastSeen($tinder_id, $data) {
			// Update the last seen table with new information
			$this->db->where('seen_id', $tinder_id);
			$this->db->update('last_seen', $data);
		}

		public function CreateLastSeen($data) {
			$this->db->insert('last_seen', $data);
		}



		/* LIKES */
		public function GetLikeCount($tinder_id, $inverse) {
			$this->db->select('id');

			if($inverse) {
				$this->db->where('user_two', $tinder_id);
			} else {
				$this->db->where('user_one', $tinder_id);
			}

			$query = $this->db->get('likes');
			$count = $query->num_rows();
			return $count;
		}

		public function GetLikes($tinder_id, $inverse, $limit, $per_page, $q = NULL) {
			$sql = "SELECT users.*, likes.*
					FROM users
					JOIN likes";

			if($inverse) {
				$sql .= " ON users.tinder_id = likes.user_one WHERE likes.user_two = '".$tinder_id."'";
			} else {
				$sql .= " ON users.tinder_id = likes.user_two WHERE likes.user_one = '".$tinder_id."'";
			}

			if($q !== NULL
			&& $q != '') {
				$sql .= " AND users.first_name LIKE '%".$q."%'";
			}

			$sql .= " LIMIT ".$limit.", ".$per_page;

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

			$return = array();

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

			return $return;
		}

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

				$data = array('user_one' => $my_id,
							'user_two' => $tinder_id,
							'match_id' => $match_id,
							'datetime' => date('Y-m-d H:i:s'));
				$this->db->insert('likes', $data);
			}
		}

		public function SeeIfLiked($my_id, $tinder_id, $inverse) {
			$this->db->select('id');

			if($inverse) {
				$this->db->where('user_one', $my_id);
				$this->db->where('user_two', $tinder_id);
			} else {
				$this->db->where('user_one', $tinder_id);
				$this->db->where('user_two', $my_id);
			}

			$query = $this->db->get('likes');
			$count = $query->num_rows();
			return $count;
		}


		/* MATCHES */
		public function GetMatchCount($tinder_id) {
			$this->db->select('id');
			$this->db->where('match_id != "0" AND user_one = "'.$tinder_id.'"');
			$query = $this->db->get('likes');
			$count = $query->num_rows();
			return $count;
		}

		public function GetMatches($tinder_id, $inverse, $limit, $per_page, $q = NULL) {
			$sql = "SELECT likes.*, users.*
					FROM likes
					JOIN users
					ON likes.user_two = users.tinder_id
					WHERE likes.match_id != '0' 
					AND (likes.user_one = '".$tinder_id."') 
					LIMIT ".$limit.", ".$per_page;
			$query = $this->db->query($sql);
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

			$return = array();

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

		public function MatchExists($match_id) {
			$this->db->select('id');
			$this->db->where('match_id', $match_id);
			$query = $this->db->get('likes');
			return $query->num_rows();
		}

		public function InsertMatch($match_id, $data) {
			$check = $this->MatchExists($match_id);

			if($check == 0) {
				$this->db->insert('likes', $data);
			}
		}

		public function GetMatchInfo($id) {
			$this->db->select('user_one, user_two');
			$query = $this->db->where('match_id', $id);
			$query = $this->db->get('likes');
			$count = $query->num_rows();
			
			if($count == 1) {
				foreach($query->result() as $row) {
					$user_one = $row->user_one;
					$user_two = $row->user_two;
				}

				return array('user_one' => $user_one, 'user_two' => $user_two);
			} else {
				return FALSE;
			} 
		}



		/* PASSES */
		public function InsertIntoPasses($my_id, $tinder_id) {
			$data = array('user_one' => $my_id,
						'user_two' => $tinder_id,
						'datetime' => date('Y-m-d H:i:s'));
			$query = $this->db->insert('passes', $data);
		}

		public function GetPassCount($tinder_id, $inverse) {
			$this->db->select('id');

			if($inverse) {
				$this->db->where('user_two', $tinder_id);
			} else {
				$this->db->where('user_one', $tinder_id);
			}

			$query = $this->db->get('passes');
			$count = $query->num_rows();
			return $count;
		}

		public function GetPasses($tinder_id, $inverse, $limit, $per_page, $q = NULL) {
			$sql = "SELECT users.*, passes.*
					FROM users
					JOIN passes";

			if($inverse) {
				$sql .= " ON users.tinder_id = passes.user_one WHERE passes.user_two = '".$tinder_id."'";
			} else {
				$sql .= " ON users.tinder_id = passes.user_two WHERE passes.user_one = '".$tinder_id."'";
			}

			if($q !== NULL
			&& $q != '') {
				$sql .= " AND users.first_name LIKE '%".$q."%'";
			}

			$sql .= " LIMIT ".$limit.", ".$per_page;

			$query = $this->db->query($sql);
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

			$return = array();

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

			return $return;
		}



		/* PICS */
		public function InsertPics($id, $pics) {
			for($i=0;$i<count($pics);$i++) {
				$this->db->select('id');
				$query = $this->db->where(array('tinder_id' => $id, 'filename' => $pics[$i]));
				$query = $this->db->get('pics');
				$count = $query->num_rows();

				if($count == 0) {
					$this->db->insert('pics', array('tinder_id' => $id, 'filename' => $pics[$i], 'pic_order' => $i));
				}
			}
		}



		/* PINGS */
		public function InsertPing($lon, $lat, $tinder_id) {
			// Call the Google Maps function to find out the city, state and country
			$location = GeoLocation($lon, $lat);
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

			$return = array();

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


		/* STATS */
		public function GetThreeStats($id) {
			// Get the like count
			$like_count = $this->database_model->GetLikeCount($id, FALSE);

			// Find out how many matches the user has
			$match_count = $this->database_model->GetMatchCount($id);

			// Get the pass count
			$pass_count = NULL;

			// Return an array containing all three stats
			return array('like_count' => $like_count, 'match_count' => $match_count, 'pass_count' => $pass_count);
		}


		/* USERS */
		public function GetHottest($gender, $city, $state, $distance, $min, $max, $q, $page, $lon, $lat) {
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
			if($gender != 'both') {
				// echo $gender;
				$sql .= " users.gender = '".$gender."' AND ";
			}

			if(is_numeric($min)) {
				$sql .= " users.age >= '".$min."' AND ";
			}

			if(is_numeric($max)) {
				$sql .= " users.age <= '".$max."' ";
			}

			if($q != '') {
				$sql .= " AND users.first_name LIKE '%".trim($q)."%' ";
			}

			if($page > 0) {
				$per_page = 10;
				$limit = $page*$per_page;
				$sql .= "LIMIT ".$limit.", ".$per_page;
			}

			// echo $sql;
			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
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

			//echo 'Count: '.$count.'<br>';
			$return = array();

			for($i=0;$i<$count;$i++) {
				// Get each user's match count
				$like_count = $this->GetLikeCount($tinder_id[$i], TRUE);

				// Filter the location
				if($city != ''
				&& $state != '') {
					// Get the distance between the cliend and the users
					$between = Haversine($last_lat[$i], $last_lon[$i], $lat, $lon);

					//echo $between.'<br>';

					if($between < $distance) {
						$return[$i] = array('tinder_id' => $tinder_id[$i],
											'name' => $name[$i],
											'age' => $age[$i],
											'profile_pic' => $pic[$i],
											'link' => FormatUserLink($tinder_id[$i], $username[$i]),
											'like_count' => $like_count);
					}
				} else {
					$return[$i] = array('tinder_id' => $tinder_id[$i],
										'name' => $name[$i],
										'age' => $age[$i],
										'profile_pic' => $pic[$i],
										'link' => FormatUserLink($tinder_id[$i], $username[$i]),
										'like_count' => $like_count);
				}
			}

			function SortTest($a, $b){    
				return $b['like_count'] - $a['like_count'];
			}

			usort($return, 'SortTest');


			return array('count' => $count, 'users' => $return);
		}

		// Gets all of the user's pics
		public function GetUserInfo($id) {
			$sql = "SELECT users.tinder_id, users.first_name, users.username, users.dob, users.age, users.bio, users.gender, users.profile_pic, users.last_activity_date, pics.*
					FROM users
					JOIN pics 
					ON users.tinder_id = pics.tinder_id
					WHERE users.tinder_id = '".$id."'
					OR users.username = '".$id."'
					ORDER BY pic_order ASC";
			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			//echo $count;
			//FormatArray($query->result());
			//die;

			if($count > 0) {
				foreach($query->result() as $row) {
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

					$filename[$i] = $row->filename;
					$order[$i] = $row->pic_order;

					$i++;
				}

				// Give the bio a default
				if($bio == '') {
					$bio = $name." doesnt't have a bio";
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
							'bio' => BioLinks($bio),
							'last_activity_date' => $activity,
							'last_active_format' => FormatTime($activity),
							'profile_pic' => $pic,
							'pics' => array('file' => $filename, 'order' => $order));
			} else {
				return FALSE;
			}
		}

		public function InsertUser($data) {
			$this->db->select('id');
			$this->db->where('tinder_id', $data['tinder_id']);
			$query = $this->db->get('users');
			$count = $query->num_rows();

			if($count == 0) {
				$this->db->insert('users', $data);
				return 0;
			} else {
				$this->db->where('tinder_id', $data['tinder_id']);
				$this->db->update('users', $data);
				return 1;
			}
		}

		public function UpdateUser($tinder_id, $data) {
			$this->db->where('tinder_id', $tinder_id);
			$this->db->update('users', $data);
		}

		public function CheckUsername($username) {
			$this->db->select('id');
			$this->db->where('username', $username);
			$query = $this->db->get('users');
			return $query->num_rows();
		}

		public function UserExists($id) {
			$this->db->select('id');
			$this->db->where('tinder_id', $id);
			$query = $this->db->get('users');
			return $query->num_rows();
		}
	}