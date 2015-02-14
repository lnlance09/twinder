<?php
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		if(!function_exists('SendRequest')) {
			/**
			 * Send a request to Tinder's API with cURL
			 * @param {string} [url] The API endpoint
			 * @param {string} [auth] The API token
			 * @param {boolean} [post] Whether or not he request is a post request
			 * @param {array} [post_data] An associative array containing the post data
			 */
			function SendRequest($url, $auth, $post, $post_data) {
				// Define the HTTP headers
				$headers = array('app-version: 123',
								'os_version: 80000100001',
								'Accept: */*',
								'platform: ios',
								'Content-Type: application/json; charset=utf-8');

				// Push the auth token headers into the array if necessary
				if($auth) {
					array_push($headers, 'Authorization: Token token="'.$auth.'"', 'X-Auth-Token: '.$auth);
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.gotinder.com/'.$url);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Tinder/4.0.9 (iPhone; iOS 8.1.1; Scale/2.00)');
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

			    if($post) {
			    	if($url == 'media') {
			    		$encode = $post_data;
			    	} else {
			    		$encode = json_encode($post_data);
			    	}
			    
			    	curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $encode);
			    } elseif($post == 'PUT') {
			    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			    	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
			    } elseif($post == 'DELETE') {
			    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			    }

			    $data = curl_exec($ch);
			    curl_close($ch);
			    return $data;
			}
		}

		if(!function_exists('GetHTTPCode')) {
			/**
			 * Return the HTTP code of a request to a given URL
			 * @param {string} [url] The URL to send a request to
			 */
			function GetHTTPCode($url) {
				$ch = curl_init($url);
			    curl_setopt($ch, CURLOPT_NOBODY, 1);
				curl_exec($ch);
				$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
				curl_close($ch);
				return $http;
			}
		}

		if(!function_exists('BioDefault')) {
			/**
			 * Format a user's bio to read a default message if he/she doesn't have one
			 * @param {string} [bio] The user's bio
			 * @param {string} [name] The user's name
			 */
			function BioDefault($bio, $name) {
				if($bio == '') {
					return $name." doesnt't have a bio";
				} else {
					return $bio;
				}
			}
		}

		if(!function_exists('BioLinks')) {
			/**
			 * Format a user's bio include links to any Instagram pages.
			 * Return links to Twitter that include any hashtags
			 * @param {string} [bio] The user's bio
			 */
			function BioLinks($bio) {
				$terms = array('instagram', 'ig', 'insta', 'Instagram', 'Ig', 'Insta', 'INSTAGRAM', 'IG', 'INSTA');
				$string = implode('|', $terms);

				// Make links out of he Instagram usernames and Twitter hashtags
				$ig_bio = preg_replace('/\b('.$string.')\s*[:-]\s*\K([\w.]+)\b/', '<a href="http://instagram.com/$2" target="_blank">$2</a>', $bio);
				$hash_bio = preg_replace('/#(\w+)/', ' <a href="http://twitter.com/hashtag/$1" target="_blank">#$1</a> ', $ig_bio);
				return trim($hash_bio);
			}
		}

		if(!function_exists('FormatUserLink')) {
			/**
			 * Return a link to a user's profile page on WeTinder based upon whether or not they have a username
			 * @param {string} [tinder_id] The Tinder ID of the user
			 * @param {username} [username] The username of the user
			 */
			function FormatUserLink($tinder_id, $username) {
				if(strlen($username) > 0) {
					return 'users/'.$username;
				} else {
					return 'users/'.$tinder_id;
				}
			}
		}

		if(!function_exists('FormatGender')) {
			/**
			 * Return the actual name of a gender based upon its numeric code
			 * @param {int} [num] The gender code
			 */
			function FormatGender($num) {
				if($num == 0) {
					return 'male';
				} else {
					return 'female';
				}
			}
		}

		if(!function_exists('ReverseGender')) {
			/**
			 * Return the numeric code of a gender based upon its name
			 * @param {string} [name] The gender's name
			 */
			function ReverseGender($name) {
				if($name == 'male') {
					return 0;
				} else {
					return 1;
				}
			}
		}

		if(!function_exists('FormatInterestedIn')) {
			/**
			 * Return the name of the interested in code
			 * @param {int} [num] The interested in code
			 */
			function FormatInterestedIn($num) {
				switch($num) {
					case 0;

						return 'men';
						break;

					case 1;

						return 'women';
						break;

					default:

						return 'both';
						break;
				}
			}
		}

		if(!function_exists('ReverseInterestedIn')) {
			/**
			 * Return the interested in code based upon its anme
			 * @param {string} [name] The name of the interested in
			 */
			function ReverseInterestedIn($name) {
				switch($name) {
					case 'men';

						return 0;
						break;

					case 'women';

						return 1;
						break;

					default:

						return '-1';
						break;
				}
			}
		}

		if(!function_exists('FormatNumber')) {
			/**
			 * Format a number so that it reads 'k' instead of 1,000
			 * @param {int} [num] The number to be formatted
			 */
			function FormatNumber($num) {
				if($num > 1000) {
					$floor = floor($num/1000);
					$decimal = ceil($num/100)-($floor*10); 
					return $floor.'.'.$decimal.'k';
				} else {
					return $num;
				}
			}
		}

		if(!function_exists('FormatTime')) {
			/**
			 * Find out how long ago a given time was
			 * @param {string} [time] The time
			 */
			function FormatTime($time) {
				// Find out the difference between now and the given date
				$time = date_diff(date_create(), date_create($time));

				// Format the date difference by minutes, hours, days and months
				$mins = $time->format('%i');
				$hours = $time->format('%h');
				$days = $time->format('%d');
				$months = $time->format('%m');

				if(ceil($mins/60) > 1) {
					if(ceil($hours/24) > 1) {
						if(ceil($days/30) > 1) {
							$format = $days.' months ago';
						} else {
							$format = $days.' days ago';
						}
					} else {
						$format = $hours.' hours ago';
					}
				} else {
					if($mins == 0) {
						$format = 'Just now';
					} else {
						$format = $mins.' mins ago';
					}
				}

				return $format;
			}
		}

		if(!function_exists('ReturnAge')) {
			/**
			 * Format a user's birthday
			 * @param {string} [birthday] The timestamp of the birthday
			 */
			function ReturnAge($birthday) {
				$dob = date('M j, Y', strtotime($birthday));
				return date_diff(date_create(), date_create($dob))->format('%y');
			}
		}

		if(!function_exists('ReturnPicsArray')) {
			/**
			 * Return an array containing all of a user's pictures' filename
			 * @param {array} [photos] An array of a user's photos from Tinder's API
			 */
			function ReturnPicsArray($photos) {
				$pics = [];

				for($i=0;$i<count($photos);$i++) {
					$pics[$i] = $photos[$i]['fileName']; 
				}

				return $pics;
			}
		}

		if(!function_exists('ReturnProfilePic')) {
			/**
			 * Return a user's profile pic
			 * @param {array} [photos] An array contaning a user's photos
			 */
			function ReturnProfilePic($photos) {
				for($i=0;$i<count($photos);$i++) {
					if($photos[$i]['fileName'] !== FALSE) {
						return $photos[$i]['fileName']; 
						break;
					}
				}
			}
		}

		if(!function_exists('FormatLastSeenText')) {
			/**
			 * Format the popup window for the Google Maps API
			 * @param {array} [data] An array containg info about the user
			 * @param {string} [base_url] The base URL of WeTinder
			 */
			function FormatLastSeenText($data, $base_url) {
				$user_info = $data['user'];
				$tinder_id = $user_info['tinder_id'];
				$name = $user_info['name'];
				$link = $user_info['link'];
				$gender = $user_info['gender'];
				$pic = $user_info['profile_pic'];

				if($gender == 0) {
					$subject = "he";
				} elseif($gender == 1) {
					$subject = "she";
				}

				$raw_data = $data[0]['data'];
				// FormatArray($data);

				$time = $raw_data['datetime'];
				$distance = $raw_data['miles_away'];
				$city = $raw_data['city'];
				$state = $raw_data['state'];
				$lon = $raw_data['lon'];
				$lat = $raw_data['lat'];

				return trim("<div id='infowindow'><h3><img src='http://images.gotinder.com/".$tinder_id."/84x84_".$pic."' width='50' height='50' alt='".$name."' class='img-circle'> <a href='".$base_url.$link."'>".$name."</a>
						</h3> <p>".FormatTime($time)." <br> ".$distance." miles away <br>".$city.", ".$state." <br> ".$lon.", ".$lat."</p></div>");
			}

		}

		if(!function_exists('CookieFile')) {
			/**
			 * Return the path to a users' cookie file
			 * @param {string} [email] The user's email
			 */
			function CookieFile($email) {
				$exp = explode('@', $email);

				if(count($exp) > 1) {
					$file = $exp[0];
				} else {
					$file = $email;
				}

			    return 'cookies/'.$file.'.txt';
			}
		}

		if(!function_exists('FormatArray')) {
			/**
			 * Format an array and style it if necessary
			 * @param {array} [array] The array to be preformatted
			 * @param {boolean} [style] Whether or not to style the preformatted array
			 */
			function FormatArray($array, $style = FALSE) {
				if($style) {
					echo '<div style="color: #090127;text-shadow:none;text-align:left;">';
				}

				echo '<pre>';
				print_r($array);
				echo '</pre>';

				if($style) {
					echo '</div>';
				}
			}
		}

		if(!function_exists('MilesToMeters')) {
			/**
			 * Convert miles to meters
			 * @param {int} [miles] The number of miles to be conerted
			 */
			function MilesToMeters($miles) {
				return ceil($miles/0.000621371);
			}
		}	

		if(!function_exists('RequestTime')) {
			/**
			 * Format a time so that it's correct to be sent to Tinder's 'updates' API endpoint
			 * @param {string} [time] The time
			 */
			function RequestTime($time) {
				if($time === NULL) {
					return $time;
				} else {
					return date('Y-m-d', strtotime($time)).'T'.date('h:i:s', strtotime($time)).'.906Z';
				}
			}
		}

		if(!function_exists('FormatNames')) {
			/**
			 * Return both the first and last names from a full name
			 * @param {string} [name] The name to be parsed
			 */
			function FormatNames($name) {
				$exp = explode(' ', $name);
				$exp_num = count($exp);
				return array('first_name' => $exp[0], 'last_name' => $exp[$exp_num-1]);
			}
		}

		if(!function_exists('MetaSubject')) {
			/**
			 * Return the subject for the meta tag based upon whether or not the user has a username
			 * @param {string} [username] The username of the user
			 * @param {string} [name] The name of the user
			 */
			function MetaSubject($username, $name) {
				if($username == '') {
					return $name;
				} else {
					return $username;
				}
			}
		}

		if(!function_exists('DefineTitle')) {
			/**
			 * Define the title for the 'Hot' page
			 * @param {int} [gender] The gender from the URL
			 * @param {string} [city] The city
			 * @param {string} [state] The state
			 * @param {int} [distance] The distance filter in miles
			 * @param {int} [min] The minimum age filter
			 * @param {int} [max] The maxmimum age filter
			 */
			function DefineTitle($gender, $city, $state, $distance, $min, $max) {
				$title = 'The hottest ';

				// Format the gender
				if($gender == 0 || $gender == 1) {
					$title .= $gender.' ';
				} 

				// Format the distance
				$title .= 'within '.$distance.' miles of ';

				// Format the city
				if($city != '') {
					$title .= $city.', ';
				}

				// Format the state
				if($state != '') {
					$title .= $state.' ';
				}

				// Format the age
				$title .= 'ages '.$min.' to '.$max;
				return $title;
			}
		}
	}
?>