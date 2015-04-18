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
				$id = $this->uri->segment(2, NULL);
				$tab = $this->uri->segment(3, 'likes');

				// Get the info about the user
				$user_info = $this->database->GetUserInfo($id);

				// If the user actually exists in the DB
				if($user_info) {
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
						$same = ($id == $tinder_id || $user_info['username'] == $username && !empty($username) ? TRUE : FALSE);

						// Make a request to Tinder to get the most recent info about this user
						$live_info = $this->user->UserLookup($user_info['tinder_id'], $token);

						// If the user actually exists according to Tinder, then get their info and update the profile
						if($live_info) {
							// The user's distance from the person who was logged in
							$distance = $live_info['distance'];

							// Update the users table with the most recent info about this user
							$data = array('first_name' => $live_info['name'],
										'bio' => $live_info['bio'],
										'dob' => $live_info['dob'],
										'age' => $live_info['age'],
										'gender' => $live_info['gender'],
										'first_name' => $live_info['name'],
										'last_activity_date' => $live_info['last_activity_date']);
							if($live_info['instagram']) {
								$data['ig_username'] = $live_info['instagram']['username'];
							}

							$this->database->UpdateUser($live_info['tinder_id'], $data);

							// Add these elements to the array
							$keys = array('name', 'bio', 'distance', 'age', 'gender', 'gender_format', 'last_activity_date', 'profile_pic', 'last_activity_date');
							foreach($keys as $key) {
								$user_info[$key] = $live_info[$key];
							}

							// Check to see if this user is allowed to report this user
							$active = TRUE;
							$report = $this->database->CheckReport($tinder_id, $user_info['tinder_id']);
							$like = $this->user->CanLike($user_info['tinder_id'], $tinder_id, $session);
							$edit = $this->user->CanEdit($user_info['tinder_id'], $tinder_id);
						} else {
							// Get all of the data for the view
							$locations = $this->loc->FooterPlaces();
							$rand_users = $this->database->GetAllUsers(5);

							$_user = array('name' => $user_info['name'], 
										'gender' => FormatPossesion($user_info['gender']), 
										'pic' => ChangePicSize($user_info['profile_pic'], 84));
							$info = array('name' => $name,
										'auth' => $token,
										'tinder_id' => $tinder_id,
										'match_count' => $match_count,
										'profile_link' => FormatUserLink($tinder_id, $username),
										'profile_pic' => ChangePicSize($pic, 84),
										'locations' => $locations,
										'users' => $rand_users,
										'user' => $_user);

							// Load the error view page and quit the script
							$this->load->view('errors/account', $info);
							$active = FALSE;
						}
					} else {
						$report = FALSE;
						$like = FALSE;
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
						// Format the user's profile link
						$profile_link = FormatUserLink($tinder_id, $username);
						$profile_pic = ChangePicSize($pic, 172);

						// Get the tab list based upon the tab in the URL
						$tabs = ReturnTabs($tab, $same, $session);

						// Update the user's last seen position
						$last_seen = $this->database->EditLastSeen($tinder_id, $user_info['tinder_id'], $distance, $lon, $lat);
						
						// Define the meta tags
						$meta_info = array('title' => MetaSubject($user_info['username'], $user_info['name']),
										'description' => MetaSubject($user_info['username'], $user_info['name']),
										'img' => $user_info['profile_pic'],
										'url' => 'http://twinder.io/'.$user_info['link'],
										'username' => (empty($user_info['username']) ? $user_info['tinder_id'] : $user_info['username']);
										'type' => 'profile');

						// Set all of the info that needs to be passed to the header view
						$header_info = array('title' => $user_info['name'],
											'type' => 'profile',
											'session' => $session,
											'header' => $user_info['name'],
											'auth' => $token,
											'tinder_id' => $tinder_id,
											'name' => $name,
											'profile_name' => $user_info['name'],
											'gender' => $user_info['gender'],
											'username' => $user_info['username'],
											'meta' => $meta_info,
											'profile_link' => $profile_link,
											'profile_pic' => $profile_pic);

						// Update the user's views
						$user_info['views'] = $this->database->UpdateProfileViews($user_info['views'], $user_info['tinder_id']); 
						
						// Can vote
						$session_id = (!$tinder_id ? $this->session->userdata('session_id') : $tinder_id);
						$can_vote = $this->database->CheckVote($session_id, $user_info['tinder_id']);
						// var_dump($can_vote);
						// die;

						// Get the votes of the user
						$votes = $this->database->GetVoteStats($id);

						// Get all of the stats of the user who is being viewed
						$user_stats = $this->database->GetUserStats($user_info['tinder_id'], $tinder_id);
						
						// Set all of the info that needs to be passed to the body view
						$body_info = array('user_info' => $user_info,
										'pic_count' => count($user_info['pics']),
										'session' => $session,
										'report' => $report,
										'like' => $like,
										'edit' => $edit,
										'lat' => $last_seen['data']['lat'],
										'lon' => $last_seen['data']['lon'],
										'city' => $last_seen['data']['city'],
										'state' => $last_seen['data']['state'],
										'distance' => $last_seen['data']['miles_away'],
										'last_seen' => FormatLastSeenText($last_seen, $this->base_url),
										'can_vote' => $can_vote,
										'votes' => $votes,
										'stats' => $user_stats,
										'con_icon' => ReturnFA($tabs['active']),
										'sub_tab' => $tab,
										'tab_active' => $tabs['active'],
										'tabs' => $tabs['tabs']);

						if($like['perm'] == 'can_like') {
							$body_info['my_info'] = array('name' => $name,
														'pic' => ChangePicSize($pic, 172),
														'link' => $profile_link);
						}

						// Get all of the data for the footer view
						$locations = $this->loc->FooterPlaces();
						$rand_users = $this->database->GetAllUsers(5);
						$footer_info = array('locations' => $locations, 'users' => $rand_users);

						// Load all of the views
						$this->load->view('templates/header', $header_info); 
						$this->load->view('profile', $body_info); 
						$this->load->view('templates/footer', $footer_info); 
					}
				} else {
					header('Location: '.$this->base_url);
				}
			} 

			public function Discover() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					$tinder_id = $this->session->userdata('tinder_id');
					$pic = $this->session->userdata('profile_pic');

					// Save the user's link to their profile
					$profile_link = FormatUserLink($tinder_id, $this->session->userdata('username'));
					$profile_pic = ChangePicSize($pic, 84);

					$meta = array('description' => 'Discover on Twinder',
								'img' => 'http://twinder.io/public/img/favicon.ico',
								'url' => 'http://twinder.io/users/Discover',
								'type' => 'article');

					// Set all of the info that needs to be passed to the header view
					$header_info = array('title' => 'Play',
										'session' => TRUE,
										'header' => NULL,
										'auth' => $this->session->userdata('token'),
										'tinder_id' => $tinder_id,
										'name' => $this->session->userdata('first_name'),
										'meta' => $meta,
										'profile_link' => $profile_link,
										'profile_pic' => $profile_pic);

					// Set all of the info that needs to be passed to the dashboard view
					$body_info = array('pic' => ChangePicSize($pic, 172),
									'link' => $profile_link,
									'name' => $this->session->userdata('first_name'));

					// Get all of the data for the footer view
					$locations = $this->loc->FooterPlaces();
					$rand_users = $this->database->GetAllUsers(5);
					$footer_info = array('locations' => $locations, 'users' => $rand_users);

					// Load all of the views
					$this->load->view('templates/header', $header_info); 
					$this->load->view('find_users', $body_info); 
					$this->load->view('templates/footer', $footer_info); 
				} else {
					header('Location: '.$this->base_url);
				}
			}

			public function DiscoverLoad() {
				// Make sure the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					$tinder_id = $this->session->userdata('tinder_id');
					$token = $this->session->userdata('token');
					$username = $this->session->userdata('username');
					$pic = $this->session->userdata('profile_pic');

					// Get all of the parameters from the URL
					$params = $this->input->get();	
					foreach($params as $key => $value) {
						$$key = $value;
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
							// Remove all of the batches from the previous load
							$this->database->RemoveAllBatch($user_id);

							// Insert the user batch into the DB
							$this->database->InsertBatch($user_id, $tinder_id, $info, $lon, $lat);
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
						$next = $this->database->GetBatchUser($user_id);

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
						break;
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
						$this->database->RemoveBatchUser($id, $user_id);
						$this->database->InsertIntoLikes($tinder_id, $id, $match, $last_active, $created_at);

						// Echo out the match ID
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
					$logout = $this->user->Logout($this->session->userdata('token'));

					// Unset the session data
					$items = array('username' => '', 'user_id' => '', 'auth' => '', 'tinder_id' => '');
					$this->session->unset_userdata($items);
				}

				// Redirect the user to the home page
				header('Location: '.$this->base_url);
			}

			public function PassUser() {
				// Make sure that the user is logged in
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Get the user ID from the URL
					$id = $this->input->get('id');

					// Call the PassUser function in the users model 
					$pass = $this->user->PassUser($id, $this->session->userdata('token'));

					// Remove the batch user from the DB and then insert him/her into the passes table
					$this->database->RemoveBatchUser($id, $user_id);
					$this->database->InsertIntoPasses($this->session->userdata('tinder_id'), $id);
				}
				echo 'done';
			}

			public function ReportUser() {
				$user_id = $this->session->userdata('user_id');

				if($user_id) {
					// Get the form parameters from the URL
					$id = $this->input->get('id');
					$reason = $this->input->get('reason');
					$text = $this->input->get('text');

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