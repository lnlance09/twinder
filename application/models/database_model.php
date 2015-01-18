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
			$this->db->delete(array('batches', 'likes', 'msg', 'passes', 'pics', 'pings', 'settings'));

			$this->db->where('tinder_id != "'.$tinder_id.'"');
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

		public function InsertBatch($user_id, $info) {
			for($i=0;$i<count($info);$i++) {
				$this->db->select('id');
				$this->db->where('tinder_id', $info[$i]['tinder_id']);
				$this->db->or_where('user_id', $user_id);
				$query = $this->db->get('batches');
				$count = $query->num_rows();

				// Check to see if there is a record of the user existing in the batches table
				if($count == 0) {
					// Insert the batch users in the DB
					$data = array('user_id' => $user_id,
								'tinder_id' => $info[$i]['tinder_id']);
					$this->db->insert('batches', $data);
				}

				// Get all of each user's pics
				$pics = $info[$i]['pics'];
				// FormatArray($pics);

				// Insert the user's pics
				$this->InsertPics($info[$i]['tinder_id'], $pics);	

				// Check to see if there is a record of the user existing in the users table
				$this->db->select('id');
				$this->db->where('tinder_id', $info[$i]['tinder_id']);
				$query = $this->db->get('users');
				$count = $query->num_rows();

				if($count == 0) {
					// Insert each user into the DB
					$data = array('tinder_id' => $info[$i]['tinder_id'],
								'first_name' => $info[$i]['name'],
								'age' => $info[$i]['age'],
								'dob' => $info[$i]['birth_date'],
								'gender' => $info[$i]['gender'],
								'bio' => $info[$i]['bio'],
								'profile_pic_tiny' => StripPic($pics[0]['tiny']),
								'profile_pic_medium' => StripPic($pics[0]['medium']),
								'profile_pic_large' => StripPic($pics[0]['big']));
					$this->InsertUser($data);
				}
			}
		}

		public function RemoveBatchUser($id) {
			$this->db->delete('batches', array('tinder_id' => $id)); 
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

		public function SyncMessages($updates) {
			$count = count($updates);
			// $count = 1;

			for($i=0;$i<$count;$i++) {
				$match_id = $updates[$i]['_id'];
				$created_at = $updates[$i]['created_date'];
				//$closed = $updates[$i]['closed'];
				//$dead = $updates[$i]['dead'];

				if(!array_key_exists('created_date', $updates[$i])) {
					FormatArray($updates[$i]);
				}

				//$participants = $updates[$i]['participants'];
				$messages = $updates[$i]['messages'];
				$person = $updates[$i]['person']['_id'];

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

				//echo $i.') '.$match_id.'<br>';
				//FormatArray($updates[$i]);
			}
		}



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
					$miles = $row->miles;
					$datetime = $row->datetime;
				}

				return array('id' => $id,
							'seen_id' => $seen_id,
							'by_id' => $by_id,
							'lon' => $lon,
							'lat' => $lat,
							'city' => $city,
							'state' => $state,
							'miles' => $miles,
							'datetime' => $datetime);
			} else {
				return FALSE;
			}
		}

		public function UpdateLastSeen($tinder_id, $data) {
			// Update the last seen table with new information
			$this->db->where('seen_id', $tinder_id);
			$this->db->update('last_seen', $data);
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
				$profile_pic[$i] = $row->profile_pic_tiny;
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
									'pics' => array(array('tiny' => $profile_pic[$i])),
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
			$data = array('user_one' => $my_id,
						'user_two' => $tinder_id,
						'match_id' => $match_id,
						'datetime' => date('Y-m-d H:i:s'));
			$query = $this->db->insert('likes', $data);
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
			$this->db->where('match_id != "0" AND (user_one = "'.$tinder_id.'" OR user_two = "'.$tinder_id.'")');
			$query = $this->db->get('likes');
			$count = $query->num_rows();
			return $count;
		}

		public function GetMatches($tinder_id, $inverse, $limit, $per_page, $q = NULL) {
			$sql = "SELECT users.*, likes.*,
					FROM users, likes 
					JOIN likes 
					ON users.tinder_id = likes.user_one
					WHERE likes.match_id != '0' 
					AND (likes.user_one = '".$tinder_id."' OR likes.user_two = '".$tinder_id."') 
					LIMIT ".$limit.", ".$per_page;
			//$query = $this->db->query($sql);

			$this->db->select('*');
			$this->db->from('users');
			$this->db->join('likes', 'likes.user_one = users.tinder_id');
			//$this->db->join('likes', 'likes.user_two = user.tinder_id');
			$query = $this->db->where('likes.match_id != "0" AND (likes.user_one = "'.$tinder_id.'" OR likes.user_two = "'.$tinder_id.'")');
			//$this->db->limit($per_page, $limit);
			$query = $this->db->get();
			$count = $query->num_rows();
			echo $count;
			die;

			$i = 0;

			foreach($query->result() as $row) {
				$id[$i] = $row->id;
				$match_id[$i] = $row->match_id;
				$user_one[$i] = $row->user_one;
				$user_two[$i] = $row->user_two;
				$datetime[$i] = $row->datetime;
				$unmatched[$i] = $row->unmatched;

				$i++;
			}

			$return = array();

			for($i=0;$i<$count;$i++) {
				if($user_one[$i] == $tinder_id) {
					$other_id = $user_two[$i];
				} else {
					$other_id = $user_one[$i];
				}

				// Get info about each of the user's matches
				$user_info = $this->GetUserInfo($other_id);

				$return[$i] = array('id' => $id[$i],
									'match_id' => $match_id[$i],
									'other_id' => $other_id,
									'datetime' => $datetime[$i],
									'user_info' => $user_info
									);
			}

			return $return;
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
				$profile_pic[$i] = $row->profile_pic_tiny;
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
									'pics' => array(array('tiny' => $profile_pic[$i])),
									'link' => FormatUserLink($p_tinder_id[$i], $username[$i]));

				$return[$i] = array('id' => $id[$i],
									'like' => $user_two[$i],
									'datetime' => $datetime[$i],
									'user_info' => $user_info);
			}

			return $return;
		}



		/* PICS */
		public function GetUserPics($tinder_id) {
			// Get the user's pics
			$this->db->select('link, pic_size');
			$this->db->where('tinder_id', $tinder_id);
			$this->db->order_by('pic_num', 'ASC');
			$query = $this->db->get('pics');
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				$link[$i] = $row->link;
				$size[$i] = $row->pic_size;

				$i++;
			}

			$pics = array();

			for($i=0;$i<$count;$i++) {
				$plus = $i+1;
				$mod = $plus%4;
				$ceil = ceil($plus/4)-1;
			
				if($mod == 1) {
					$pics[$ceil] = array();
				} 

				$pics[$ceil][$size[$i]] = StripPic($link[$i]);
			}

			return $pics;
		}

		public function InsertPics($tinder_id, $pics) {
			for($x=0;$x<count($pics);$x++) {
				foreach($pics[$x] as $key => $val) {
					if($key != 'fb_id') {
						// Check to see if there is a record of the user existing in the users table
						$this->db->select('id');
						$this->db->where('tinder_id', $tinder_id);
						$this->db->where('pic_size', $key);
						$this->db->where('link', $val);
						$query = $this->db->get('pics');
						$count = $query->num_rows();

						if($count == 0) {
							$data = array('tinder_id' => $tinder_id,
										'pic_size' => $key,
										'link' => StripPic($val),
										'pic_num' => $x);
							$query = $this->db->insert('pics', $data);
						}
					}
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
		public function GetHottest($gender, $city, $state, $distance, $min, $max, $page) {
			$sql = "SELECT users.tinder_id, users.first_name, users.age, users.username, users.profile_pic_tiny, last_seen.*
					FROM users 
					JOIN last_seen
					ON users.tinder_id = last_seen.seen_id ";

			// Filter the age
			if($gender != 'both') {
				// echo $gender;
				$sql .= "WHERE gender = '".$gender."' AND ";
			}

			if(is_numeric($min)) {
				$sql .= " age >= '".$min."' AND ";
			}

			if(is_numeric($max)) {
				$sql .= " age <= '".$max."'";
			}

			// Filter the location
			if($city != ''
			&& $state != '') {
				//$
			}

			if($page > 0) {
				$per_page = 10;
				$limit = $page*$per_page;
				$sql .= "LIMIT ".$limit.", ".$per_page;
			}

			echo $sql;

			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				$tinder_id[$i] = $row->tinder_id;
				$name[$i] = $row->first_name;
				$age[$i] = $row->age;
				$username[$i] = $row->username;
				$pic[$i] = $row->profile_pic_tiny;

				$i++;
			}

			$return = array();

			for($i=0;$i<$count;$i++) {
				// Get each user's match count
				$like_count = $this->GetLikeCount($tinder_id[$i], TRUE);

				$return[$i] = array('tinder_id' => $tinder_id[$i],
									'name' => $name[$i],
									'age' => $age[$i],
									'pic' => $pic[$i],
									'username' => $username[$i],
									'like_count' => $like_count);
			}

			function SortTest($a, $b){    
				return $b['like_count'] - $a['like_count'];
			}

			usort($return, 'SortTest');

			return array('count' => $count, 'users' => $return);
		}

		public function SearchUsers($q, $gender, $min = 18, $max = 100) {
			$sql = "SELECT tinder_id, first_name, age, username	
					FROM users
					WHERE age > '".$min."' AND age < '".$max."'";
					
			if($gender != 'both') {
				$sql .= " AND gender = '".$gender."'";
			}

			$sql .= " AND (first_name LIKE '%".$q."%' OR last_name LIKE '%".$q."%') LIMIT 3";

			$query = $this->db->query($sql);
			$count = $query->num_rows();
			$i = 0;

			foreach($query->result() as $row) {
				$id[$i] = $row->tinder_id;
				$name[$i] = $row->first_name;
				$age[$i] = $row->age;
				$username[$i] = $row->username;

				$i++;
			}

			$return = array('count' => $count,
							'users' => array());

			for($i=0;$i<$count;$i++) {
				// Get each user's pics
				$pics = $this->GetUserPics($id[$i]);

				$return['users'][$i] = array('tinder_id' => $id[$i],
											'first_name' => $name[$i],
											'age' => $age[$i],
											'username' => $username[$i],
											'pics' => $pics,
											'link' => FormatUserLink($id[$i], $username[$i]));
			}

			return $return;
		}

		public function GetUserInfo($id) {
			$this->db->select('id, tinder_id, first_name, last_name, age, gender, bio, username, last_activity_date');
			$this->db->where('tinder_id', $id);
			//$this->db->or_where('username', $id);
			$query = $this->db->get('users');
			$count = $query->num_rows();

			if($count == 1) {
				foreach($query->result() as $row) {
					$user_id = $row->id;
					$tinder_id = $row->tinder_id;
					$first_name = $row->first_name;
					$last_name = $row->last_name;
					$gender = $row->gender;
					$username = $row->username;
					$age = $row->age;
					$bio = $row->bio;
					$activity = $row->last_activity_date;
				}

				if($bio == '') {
					$bio = $first_name." doesnt't have a bio";
				}

				// Get the user's pics
				$pics = $this->GetUserPics($tinder_id);

				return array('tinder_id' => $tinder_id,
							'user_id' => $user_id,
							'link' => FormatUserLink($id, $username),
							'username' => $username,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'last_activity_date' => $activity,
							'last_active_format' => FormatTime($activity),
							'gender' => $gender,
							'age' => $age,
							'bio' => $bio,
							'bio_links' => BioLinks($bio),
							'pics' => $pics);
			} else {
				return 'error';
			}
		}

		public function InsertUser($data) {
			$this->db->select('id');
			$this->db->where('id', $data['tinder_id']);
			$query = $this->db->get('users');
			$count = $query->num_rows();

			if($count == 0) {
				$this->db->insert('users', $data);
			} else {
				$this->db->where('tinder_id', $data['tinder_id']);
				$this->db->update('users', $data);
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
			$count = $query->num_rows();
			return $count;
		}
	}