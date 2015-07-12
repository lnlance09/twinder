<?php 
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		class Users extends CI_Controller {
			public function __construct() {       
				parent:: __construct();
				
				$this->base_url = $this->config->base_url();
				$this->load->library('session');
				
				// Load the users model
				$this->load->model('users_model', 'user');
			}

			public function Index() {
				// Get the URL segments
				$id = $this->uri->segment(1, NULL);
				$tab = $this->uri->segment(2, 'likes');

				if($id == 'users') {
					header('Location: '.$this->base_url.$this->uri->segment(2, NULL));
				} else {
					// Get the info about the user
					$user = $this->database->GetUserInfo($id);

					// If the user actually exists in the DB
					if($user) {
						// Find out if the client is logged in or not
						$user_id = $this->session->userdata('user_id');
						$session = ($user_id ? TRUE : FALSE);

						// If the client is logged in
						if($session) {
							$tinder_id = $this->session->userdata('tinder_id');
							$username = $this->session->userdata('username');
							$token = $this->session->userdata('token');
							$name = $this->session->userdata('first_name');
							$lon = $this->session->userdata('lon');
							$lat = $this->session->userdata('lat');
							$pic = $this->session->userdata('profile_pic');

							// Findout if the user is viewing their own profile
							$same = ($id == $tinder_id || $user['username'] == $username && !empty($username) ? TRUE : FALSE);

							// Make a request to Tinder to get the most recent info about this user
							$live = $this->user->UserLookup($user['tinder_id'], $token);

							// If the user actually exists according to Tinder, then get their info and update the profile
							if($live) {
								$distance = $live['distance'];
								// Update the users table with the most recent info about this user
								$data = array('first_name' => $live['name'],
											'bio' => $live['bio'],
											'dob' => $live['dob'],
											'age' => $live['age'],
											'gender' => $live['gender'],
											'first_name' => $live['name'],
											'last_activity_date' => $live['last_activity_date']);
								if($live['instagram']) {
									$data['ig_username'] = $live['instagram']['username'];
								}

								$this->database->UpdateUser($live['tinder_id'], $data);

								// Add these elements to the array
								$keys = array('name', 'bio', 'distance', 'age', 'gender', 'gender_format', 'last_activity_date', 'profile_pic', 'last_activity_date');
								foreach($keys as $key) {
									$user[$key] = $live[$key];
								}

								// Check to see if this user is allowed to report this user
								$active = TRUE;
								$report = $this->database->CheckReport($tinder_id, $user['tinder_id']);
								$edit = $this->user->CanEdit($user['tinder_id'], $tinder_id);
							} else {
								// Define the meta tags
								$meta = array('title' => MetaSubject($user['username'], $user['name']),
											'description' => $user['name']."'s Tinder Profile",
											'img' => $user['profile_pic'],
											'url' => 'http://twinder.io/'.$user['link'],
											'username' => (empty($user['username']) ? $user['tinder_id'] : $user['username']),
											'type' => 'profile');
								
								// Format the user's profile link
								$link = FormatUserLink($tinder_id, $username);
								$pic = ChangePicSize($pic, 172);

								// Set all of the info that needs to be passed to the header view
								$header = array('title' => $user['name'],
												'type' => 'profile',
												'session' => $session,
												'header' => $user['name'],
												'auth' => $token,
												'tinder_id' => $tinder_id,
												'name' => $name,
												'profile_name' => $user['name'],
												'gender' => $user['gender'],
												'username' => $user['username'],
												'meta' => $meta,
												'link' => $link,
												'pic' => $pic);

								// Define the body info
								$_user = array('name' => $user['name'], 
												'gender' => FormatPossesion($user['gender']), 
												'pic' => ChangePicSize($user['profile_pic'], 84));
								$info = array('name' => $name,
											'auth' => $token,
											'tinder_id' => $tinder_id,
											'profile_link' => FormatUserLink($tinder_id, $username),
											'profile_pic' => ChangePicSize($pic, 84),
											'locations' => $places,
											'users' => $users,
											'user' => $_user);

								// Get all of the data for the footer view
								$places = $this->loc->FooterPlaces();
								$users = $this->database->GetAllUsers(5);
								$footer = array('locations' => $places, 'users' => $users);

								// Load the error view page and quit the script
								$this->load->view('templates/header', $header); 
								$this->load->view('errors/account', $info);
								$this->load->view('templates/footer', $footer); 
								$active = FALSE;
							}
						} else {
							$report = FALSE;
							$edit = FALSE;
							$name = NULL;
							$pic = NULL;
							$token = NULL;
							$tinder_id = NULL;
							$username = NULL;
							$lon = NULL;
							$lat = NULL;
							$distance = NULL;
							$active = TRUE;
							$same = FALSE;
						}

						if($active) {
							// Find out if the user can like this profile
							$like = $this->user->CanLike($tinder_id, $user['tinder_id'], $session);

							// Format the user's profile link
							$link = FormatUserLink($tinder_id, $username);
							$pic = ChangePicSize($pic, 172);

							// Get the tab list based upon the tab in the URL
							$tabs = ReturnTabs($tab, $same, $session);

							// Update the user's last seen position
							$seen = $this->database->EditLastSeen($tinder_id, $user['tinder_id'], $distance, $lon, $lat);
							
							// Define the meta tags
							$meta = array('title' => MetaSubject($user['username'], $user['name']),
										'description' => $user['name']."'s Tinder Profile",
										'img' => $user['profile_pic'],
										'url' => 'http://twinder.io/'.$user['link'],
										'username' => (empty($user['username']) ? $user['tinder_id'] : $user['username']),
										'type' => 'profile');

							// Set all of the info that needs to be passed to the header view
							$header = array('title' => $user['name'],
											'type' => 'profile',
											'session' => $session,
											'header' => $user['name'],
											'auth' => $token,
											'tinder_id' => $tinder_id,
											'name' => $name,
											'profile_name' => $user['name'],
											'gender' => $user['gender'],
											'username' => $user['username'],
											'meta' => $meta,
											'link' => $link,
											'pic' => $pic);

							// Update the user's views
							$user['views'] = $this->database->UpdateProfileViews($user['views'], $user['tinder_id']); 
							
							// Can vote
							$session_id = (!$tinder_id ? $this->session->userdata('session_id') : $tinder_id);
							$can_vote = $this->database->CheckVote($session_id, $user['tinder_id']);
							$votes = $this->database->GetVoteStats($id);

							// Get all of the stats of the user who is being viewed
							$user_stats = $this->database->GetUserStats($user['tinder_id'], $tinder_id);
							
							// Set all of the info that needs to be passed to the body view
							$body = array('user_info' => $user,
										'pic_count' => count($user['pics']),
										'session' => $session,
										'report' => $report,
										'like' => $like,
										'edit' => $edit,
										'lat' => $seen['data']['lat'],
										'lon' => $seen['data']['lon'],
										'city' => $seen['data']['city'],
										'state' => $seen['data']['state'],
										'distance' => $seen['data']['miles_away'],
										'last_seen' => FALSE,
										'can_vote' => $can_vote,
										'votes' => $votes,
										'stats' => $user_stats,
										'con_icon' => ReturnFA($tabs['active']),
										'sub_tab' => $tab,
										'tab_active' => $tabs['active'],
										'tabs' => $tabs['tabs']);

							if($like['perm'] == 'can_like') {
								$body['my_info'] = array('name' => $name, 'pic' => $pic, 'link' => $link);
							}

							// Get all of the data for the footer view
							$places = $this->loc->FooterPlaces();
							$users = $this->database->GetAllUsers(5);
							$footer = array('locations' => $places, 'users' => $users);

							// Load all of the views
							$this->load->view('templates/header', $header); 
							$this->load->view('profile', $body); 
							$this->load->view('templates/footer', $footer); 
						}
					} else {
						header('Location: '.$this->base_url.'hot');
					}
				}
			} 

			public function Discover() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					$tinder_id = $this->session->userdata('tinder_id');
					$pic = $this->session->userdata('profile_pic');

					// Save the user's link to their profile
					$link = FormatUserLink($tinder_id, $this->session->userdata('username'));
					$pic = ChangePicSize($pic, 84);

					$meta = array('description' => 'Discover on Twinder',
								'img' => 'http://twinder.io/public/img/favicon.ico',
								'url' => 'http://twinder.io/users/Discover',
								'type' => 'article');

					// Set all of the info that needs to be passed to the header view
					$header = array('title' => 'Play',
									'session' => TRUE,
									'header' => NULL,
									'auth' => $this->session->userdata('token'),
									'tinder_id' => $tinder_id,
									'name' => $this->session->userdata('first_name'),
									'meta' => $meta,
									'link' => $link,
									'pic' => $pic);

					// Set all of the info that needs to be passed to the dashboard view
					$body = array('pic' => ChangePicSize($pic, 172),
								'link' => $link,
								'name' => $this->session->userdata('first_name'));

					// Get all of the data for the footer view
					$places = $this->loc->FooterPlaces();
					$users = $this->database->GetAllUsers(5);
					$footer = array('locations' => $places, 'users' => $users);

					// Load all of the views
					$this->load->view('templates/header', $header); 
					$this->load->view('find_users', $body); 
					$this->load->view('templates/footer', $footer); 
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function DiscoverLoad() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					$tinder_id = $this->session->userdata('tinder_id');
					$username = $this->session->userdata('username');
					$token = $this->session->userdata('token');
					$pic = $this->session->userdata('profile_pic');

					// Get all of the parameters from the URL
					$params = $this->input->get();	
					foreach($params as $key => $val) {
						$$key = $val;
					}

					// If the user is requesting a new batch, then ping the location in request a fresh batch of users
					if($type == 'new') {
						// See if the user is traveling
						$meta = $this->user->GetMeta($token);

						if(array_key_exists('travel', $meta)) {
							if($meta['travel']['is_traveling'] == 1) {
								$lon = $meta['travel']['travel_pos']['lon'];
								$lat = $meta['travel']['travel_pos']['lat'];
							}
						}

						// Set the session data for the latitude and longitude
						$this->session->set_userdata(array('lon' => $lon, 'lat' => $lat));

						// Ping the user's current location
						$info = $this->user->PingUser($lon, $lat, $token);

						// Insert the user's ping into the DB
						if($info['status'] == 200 && !array_key_exists('error', $info)) {
							$this->database->InsertPing($lon, $lat, $tinder_id);
						}

						// Get the current batch of users
						$info = $this->user->PresentUsers($token); 

						// If there isn't a recs timeout
						if($info) {
							// Remove all of the batches from the previous load and insert a new one
							$this->database->RemoveAllBatch($tinder_id);
							$this->database->InsertBatch($tinder_id, $info, $lon, $lat);
							$new = TRUE;
						} else {
							$new = FALSE;
						}
					} else {
						$new = TRUE;
					}

					// If there wasn't an error, then present him/her with their most recent info from Tinder
					if($new) {
						// Get the most recent batch user
						$next = $this->database->GetBatchUser($tinder_id);

						// Lookup the user to see if there's any mutual likes or friends
						$lookup = $this->user->UserLookup($next, $token);

						// Load the view
						$this->load->view('find_users_two', $lookup); 
					} else {
						$this->load->view('errors/timeout'); 
					}
				}
			}

			public function GetConnections() {
				// Get the query parameters from the URL
				$page = $this->input->get('page');
				$type = $this->input->get('type');
				$id = $this->input->get('id');
				$q = $this->input->get('q');

				// Save the Tinder ID session
				$tinder_id = $this->session->userdata('tinder_id');
				$same = ($id == $tinder_id ? TRUE : FALSE);

				// Get the results depening on what the user is looking for
				switch($type) {
					case'likes':

						$results = $this->database->GetLikes($id, FALSE, $q);
						break;

					case'mutual_likes':

						$results = $this->database->GetMutualLikes($id, $tinder_id, $q);
						break;

					case'liked_by':

						$results = $this->database->GetLikes($id, TRUE, $q);
						break;

					case'matches':

						$results = $this->database->GetMatches($id, $q, $same);
						break;

					case'mutual_matches':

						$results = $this->database->GetMutualMatches($id, $tinder_id, $q);
						break;

					case'passes':

						$results = $this->database->GetPasses($id, FALSE, $q);
						break;

					case'mutual_passes':

						$results = $this->database->GetMutualPasses($id, $tinder_id, $q);
						break;

					case'passed_by':

						$results = $this->database->GetMutualPasses($id, TRUE, $q);
				}
				// FormatArray($results, TRUE);

				// Get the stats for the pagination
				$count = $results['count'];
				$per_page = 10;
				$new_page = $page+1;
				$pages = ceil($count/$per_page);
				$start = $page*$per_page;

				if($page == ($pages-1)) {
					$mod = $count%$per_page;
					$end = ($mod > 0 ? $start+$mod : $start+$per_page);
				} else {
					$end = $start+$per_page;
				}

				$info = array('connections' => $results['users'],
							'id' => $id,
							'type' => $type,
							'count' => $count,
							'left_over' => $count-($new_page*$per_page),
							'end' => ceil($end),
							'pages' => $pages,
							'page' => $page,
							'new_page' => $new_page);

				// Determine which view to load
				$view = ($type == 'matches' || $type == 'mutual_matches' ? 'matches' : 'connections');
				
				// Load the view
				$this->load->view('backend/'.$view, $info);
			}

			public function GetMatchInfo() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Get the match ID from the URL
					$id = $this->input->get('match_id');

					// Get the match info
					$match = $this->user->GetMatchInfo($id, $this->session->userdata('token'));
					$user_id = $match['results']['participants'][1];
					$data = array('name' => $match['results']['person']['name'],
								'pic' => ReturnProfilePic($match['results']['person']['photos']),
								'id' => $match['results']['person']['_id']);
					echo json_encode($data);
				}
			}

			public function GetUpdates() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Save all of the session variables
					$tinder_id = $this->session->userdata('tinder_id');
					$token = $this->session->userdata('token');
					$distance = $this->session->userdata('distance');
					$lon = $this->session->userdata('lon');
					$lat = $this->session->userdata('lat');
					
					// Get the city and state
					$loc = $this->loc->MapquestLatLon($lat, $lon);
					$city = $loc['city'];
					$state = $loc['state'];

					// Call the GetUpdates function in the users model 
					$updates = $this->user->GetUpdates($token, '-10hours');

					// Sync all of the user's messages
					$this->database->SyncMessages($updates['matches'], $tinder_id, $distance, $lon, $lat, $city, $state);
					
					// Get all of the blocks and update the likes table accordingly
					$this->database->UpdateBlocks($tinder_id, $updates['blocks']);
				}
			}

			public function LikeUser() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Save the client's Tinder ID
					$tinder_id = $this->session->userdata('tinder_id');

					// Get the user ID from the URL
					$id = $this->input->get('id');

					// Call the LikeUser function in the users model 
					$like = $this->user->LikeUser($id, $this->session->userdata('token'));
					// FormatArray($like);

					// Get the match ID
					if(array_key_exists('match', $like)) {
						if(is_array($like['match'])) {
							$match_id = $like['match']['_id'];
							$match = $like['match']['_id'];
							$last_active = $like['match']['last_activity_date'];
							$created_at = $like['match']['created_date'];
						} else {
							$match_id = 'false';
							$match = NULL;
							$last_active = date('Y-m-d H:i:s');
							$created_at = date('Y-m-d H:i:s');
						}

						// Remove the batch user from the DB and then insert him/her into the likes table
						$this->database->RemoveBatchUser($tinder_id, $id);
						$this->database->InsertIntoLikes($tinder_id, $id, $match, $last_active, $created_at);
						echo $match_id;
					} else {
						echo '';
					}
				}
			}

			public function Logout() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Log the user out of Tinder
					$this->user->Logout($this->session->userdata('token'));
					$items = array('username' => '', 'user_id' => '', 'auth' => '', 'tinder_id' => '');
					$this->session->unset_userdata($items);
				}

				// Redirect the user to the home page
				header('Location: '.$this->base_url);
			}

			public function PassUser() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');
				$tinder_id = $this->session->userdata('tinder_id');

				if($user_id) {
					// Get the user ID from the URL
					$id = $this->input->get('id');

					// Call the PassUser function in the users model 
					$pass = $this->user->PassUser($id, $this->session->userdata('token'));

					// Remove the batch user from the DB and then insert him/her into the passes table
					$this->database->RemoveBatchUser($tinder_id, $id);
					$this->database->InsertIntoPasses($tinder_id, $id);
				}
				echo 'done';
			}

			public function ReportUser() {
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Get the form parameters from the URL
					$id = $this->input->get('id');
					$text = $this->input->get('text');
					$reason = $this->input->get('reason');

					if(empty($text)) {
						$text = NULL;
					}

					$tinder_id = $this->session->userdata('tinder_id');
					$auth = $this->session->userdata('token');

					// Check to see if this user has already reported this user before
					$check = $this->database->CheckReport($tinder_id, $id);

					if($check) {
						// Send a request to Tinder's API to report this user
						$report = $this->user->ReportUser($id, $auth, $reason, $text);

						// If the report was successfully sent to Tinder, then record it in the DB
						if(array_key_exists('status', $report)) {
							if($report['status'] == 200) {
								$this->database->InsertReport($tinder_id, $id);
							}
						}

						echo json_encode($report);
					}
				}
			}

			public function SendMessage() {
				// Find out if the user is logged in or not
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Get the match ID and the message from the URL
					$id = $this->input->post('id');
					$msg = $this->input->post('msg');

					// Make sure the form was submitted
					if($this->input->post('submit') == 'submit') { 
						$message = $this->user->SendMessage($id, $msg, $this->session->userdata('token'));
						// FormatArray($message);

						// Insert the message into the DB
						if(is_array($message)) {
							$data = array('match_id' => $message['match_id'],
										'msg' => $message['message'],
										'user_from' => $message['from'],
										'user_to' => $message['to'],
										'datetime' => strtotime($message['sent_date']));
							$this->database->InsertMessage($data);
							echo 'true';
						} else {
							echo 'error';
						}
					} else {
						echo 'error';
					}
				}
			}

			public function UnmatchUser() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Get the match ID from the URL
					$match_id = $this->input->get('id');

					// Save the API token
					$auth = $this->session->userdata('token');

					// Unmatch the user by sending a request to Tinder's API
					$unmatch = $this->user->UnmatchUser($match_id, $auth);
					// echo $unmatch;

					if(is_array($unmatch)) {
						if($unmatch['status'] == 200) {
							// Update the likes table to 'unmatched'
							$this->database->UnmatchUser($this->session->userdata('tinder_id'), $match_id);
						}
					}
				}
			}

			public function UpdateProfile() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					$auth = $this->session->userdata('token');
					$tinder_id = $this->session->userdata('tinder_id');
					$gender = $this->session->userdata('gender');

					// Update the bio and/or gender
					$bio = $this->input->post('bio');
					$update = $this->user->UpdateProfile($auth, $bio, $gender);

					// Update the user's row in the DB
					$this->database->UpdateUser($tinder_id, array('bio' => $bio, 'gender' => $gender));
					// FormatArray($update);
				}
			}

			public function Vote() {
				// Get the ID from URL
				$id = $this->input->get('id');
				$vote = $this->input->get('vote');

				// Save the logged in user's Tinder ID
				$tinder_id = $this->session->userdata('tinder_id');
				if(!$tinder_id) {
					$tinder_id = $this->session->userdata('session_id');
				}

				// Insert the row into the DB if it hasn't been already
				echo $this->database->InsertVote($tinder_id, $id, $vote);
			}
		}
	}