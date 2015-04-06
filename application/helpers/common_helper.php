<?php
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		/**
		 * Send a request to Tinder's API with cURL
		 * @param {string} [url] The API endpoint
		 * @param {string} [auth] The API token
		 * @param {boolean} [post] Whether or not he request is a post request
		 * @param {array} [post_data] An associative array containing the post data
		 */
		function SendRequest($url, $auth, $post, $post_data) {
			// Define the HTTP headers
			$headers = array('app-version: 123', 'os_version: 80000100001', 'Accept: */*', 'platform: ios', 'Content-Type: application/json; charset=utf-8');

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
		    	curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
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

		/**
		 * Sort messages according to time
		 * @param {array} [a] Array one
		 * @param {array} [b] Array two
		 */
		function SortMessages($a, $b) {    
			return $b['last_msg']['time'] - $a['last_msg']['time'];
		}

		/**
		 * Sort messages according to likes
		 * @param {array} [a] Array one
		 * @param {array} [b] Array two
		 */
		function SortByLikes($a, $b) {    
			return $b['like_count'] - $a['like_count'];
		}

		/**
		 * Format a user's bio to read a default message if he/she doesn't have one
		 * @param {string} [bio] The user's bio
		 * @param {string} [name] The user's name
		 */
		function BioDefault($bio, $name) {
			return (empty($bio) ? $name." doesn't have a bio." : $bio);
		}

		/**
		 * Format a user's bio include links to any Instagram pages.
		 * Return links to Twitter that include any hashtags
		 * @param {string} [bio] The user's bio
		 */
		function BioLinks($bio) {
			$terms = array('@', 'instagram', 'ig:', 'insta:', 'Instagram', 'Ig:', 'Insta', 'INSTAGRAM', 'IG:', 'INSTA');
			// $ig_bio = preg_replace('/(?<=^|\s)'.implode('|', $terms).'([a-z0-9_]+)/i', '<a href="https://instagram.com/$1" target="_blank">$1</a>', $bio);
			$ig_bio = preg_replace('/\b('.implode('|', $terms).')\s*[:-]\s*\K([\w.]+)\b/', '<a href="http://instagram.com/$2" target="_blank">$2</a>', $bio);
			$hash_bio = preg_replace('/#(\w+)/', ' <a href="http://twitter.com/hashtag/$1" target="_blank">#$1</a> ', $ig_bio);
			return trim($hash_bio);
		}

		/**
		 * Make hashtags clickable
		 * @param {string} [tweet] Any string
		 */
		function ReturnHashtags($tweet) {
			return preg_replace('/#(\w+)/', ' <a href="http://twitter.com/hashtag/$1" target="_blank">#$1</a> ', $tweet);
		}

		/**
		 * Make Twitter handles clickable
		 * @param {string} [tweet] Any string
		 */
		function ReturnMentions($tweet) {
			return preg_replace('/@(\w+)/', ' <a href="http://twitter.com/$1" target="_blank">@$1</a> ', $tweet);
		}

		/**
		 * Make links clickable
		 * @param {string} [tweet] Any string
		 */
		function ReturnLinks($tweet) {
			 return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $tweet);
		}

		/**
		 * Make hashtags clickable
		 * @param {string} [tweet] Any string
		 */
		function FormattedTweet($tweet) {
			$link = ReturnLinks($tweet);
			$hash = ReturnHashtags($link);
			return ReturnMentions($hash);
		}

		/**
		 * Return a link to a user's profile page on WeTinder based upon whether or not they have a username
		 * @param {string} [tinder_id] The Tinder ID of the user
		 * @param {username} [username] The username of the user
		 */
		function FormatUserLink($tinder_id, $username) {
			return (empty($username) ? 'users/'.$tinder_id : 'users/'.$username);
		}

		/**
		 * Return the actual name of a gender based upon its numeric code
		 * @param {int} [num] The gender code
		 */
		function FormatGender($num) {
			return ($num == 0 ? 'male' : 'female');
		}

		/**
		 * Return the numeric code of a gender based upon its name
		 * @param {string} [name] The gender's name
		 */
		function ReverseGender($name) {
			return ($name == 'male' ? 0 : 1);
		}

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

		/**
		 * Return the interested in code based upon its anme
		 * @param {string} [name] The name of the interested in
		 */
		function ReverseInterestedIn($name) {
			switch($name) {
				case 'men':

					return 0;
					break;

				case 'women':

					return 1;
					break;

				default:

					return '-1';
					break;
			}
		}

		/**
		 * Format a number so that it reads 'k' instead of 1,000
		 * @param {int} [num] The number to be formatted
		 */
		function FormatNumber($num) {
			if($num > 10000) {
				$floor = floor($num/1000);
				$decimal = ceil($num/100)-($floor*10); 
				return $floor.'.'.$decimal.'k';
			} else {
				return number_format($num);
			}
		}

		/**
		 * Find out how long ago a given time was
		 * @param {string} [time] The time
		 */
		function FormatTime($time) {
			if($time != 'Just now' && substr($time, -3) != 'ago') {
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
			} else {
				return $time;
			}
		}

		/**
		 * Format a user's birthday
		 * @param {string} [birthday] The timestamp of the birthday
		 */
		function ReturnAge($birthday) {
			$dob = date('M j, Y', strtotime($birthday));
			return date_diff(date_create(), date_create($dob))->format('%y');
		}

		/**
		 * Return an array containing all of a user's pictures' filename
		 * @param {array} [photos] An array of a user's photos from Tinder's API
		 */
		function ReturnPicsArray($photos) {
			$pics = [];

			for($i=0;$i<count($photos);$i++) {
				$pics[$i] = $photos[$i]['processedFiles'][0]['url'];
			}

			return $pics;
		}

		/**
		 * Return a user's profile pic
		 * @param {array} [photos] An array contaning a user's photos
		 */
		function ReturnProfilePic($photos) {
			$key = 0;

			for($i=0;$i<count($photos);$i++) {
				if(array_key_exists('main', $photos[$i])) {
					if($photos[$i]['main'] == 'main') {
						$key = $i;
						break;
					}
				}
			}

			return $photos[$key]['processedFiles'][0]['url'];
		}

		/**
		 * Return the path to a Tinder user's image
		 * @param {string} [file] The name of the pic file
		 * @param {int} [size] The size of the pic
		 * @return {string} The path to the user's Tinder pic
		 */
		function ChangePicSize($file, $size) {
			if($size == 84 || $size == 172 || $size == 320 || $size == 640) {
				return str_replace('640x640_', $size.'x'.$size.'_', $file);
			} else {
				return $file;
			}
		}

		/**
		 * Format the popup window for the Google Maps API
		 * @param {array} [data] An array containg info about the user
		 * @param {string} [base_url] The base URL of WeTinder
		 */
		function FormatLastSeenText($data, $base_url) {
			// Save the user's info as variables
			$user_info = $data['user'];
			$name = $user_info['name'];
			$link = $user_info['link'];
			$pic = ChangePicSize($user_info['profile_pic'], 172);

			// Save the location data
			$loc_data = $data['data'];

			$text = '<div class="media" id="infowindow">
			            <div class="media-left media-top">
			                <a href="'.$base_url.$link.'">
			                    <img src="'.$pic.'" class="media-object img-circle" alt="'.$name.'">
			                </a>
			            </div>
			            
			            <div class="media-body text-left">
			                <h4 class="media-heading">
			                    <a href="'.$base_url.$link.'" title="'.$name.'">'.$name.'</a>
			                </h4>

			                <p>
			                    '.$loc_data['miles_away'].' miles away from '.$loc_data['city'].', '.$loc_data['state'].' <br>

			                    '.FormatTime($loc_data['datetime']).'
			                </p>
			            </div>
			        </div>';
			return trim($text);
		}

		/**
		 * Format a gender's possesion
		 * @param {int} [gender] Either 0 or 1
		 * @return {string}
		 */
		function FormatPossesion($gender) {
			return ($gender == 0 ? 'his' : 'her');
		}

		/**
		 * Return the Font-Awesome class name
		 * @param {string} [type] The name of the tab
		 * @return {string}
		 */
		function ReturnFA($type) {
			switch($type) {
	            case'likes':

	                return 'thumbs-up';
	                break;

	            case'matches':

	                return 'heart';
	                break;

	            case'passes':

	                return 'thumbs-down';
	                break;

	            case'tweets':

	                return 'twitter';
	                break;
	        }
	    }

	    /**
	     * Return a gender's article
	     * @param {int} [gender] Ether 0 or 1
	     * @return {string}
	     */
		function FormatArticle($gender) {
			return ($gender == 0 ? 'he' : 'she');
		}

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

		/**
		 * Convert miles to meters
		 * @param {int} [miles] The number of miles to be conerted
		 */
		function MilesToMeters($miles) {
			return ceil($miles/0.000621371);
		}

		/**
		 * Format a time so that it's correct to be sent to Tinder's 'updates' API endpoint
		 * @param {string} [time] The time
		 */
		function RequestTime($time) {
			return ($time == NULL ? $time : date('Y-m-d', strtotime($time)).'T'.date('h:i:s', strtotime($time)).'.906Z');
		}

		/**
		 * Return both the first and last names from a full name
		 * @param {string} [name] The name to be parsed
		 */
		function FormatNames($name) {
			$exp = explode(' ', $name);
			return array('first_name' => $exp[0], 'last_name' => end($exp));
		}

		/**
		 * Return the subject for the meta tag based upon whether or not the user has a username
		 * @param {string} [username] The username of the user
		 * @param {string} [name] The name of the user
		 */
		function MetaSubject($username, $name) {
			return (empty($username) ? $name : $username).' on Twinder';
		}

		/**
		 * Determine what tab to display
		 * @param [type] $tab  [description]
		 * @param [type] $same [description]
		 */
		function ReturnTabs($tab, $same, $session) {
			switch($tab) {
				case'likes':
				case'liked_by':
				case'mutual_likes':

					$tabs = array('likes', 'liked_by');
					$active = 'likes';

					if(!$same && $session) {
						array_push($tabs, 'mutual_likes');
					}
					break;

				case'passes':
				case'passed_by':
				case'mutual_passes':

					$tabs = array('passes', 'passed_by');
					$active = 'passes';

					if(!$same && $session) {
						array_push($tabs, 'mutual_passes');
					}
					break;	

				case'matches':
				case'mutual_matches':

					$tabs = array('matches');
					$active = 'matches';

					if(!$same && $session) {
						array_push($tabs, 'mutual_matches');
					}
					break;

				case'tweets':
				case'tweets_and_replies':
				case'photos_and_videos':

					$tabs = array('tweets', 'tweets_and_replies', 'photos_and_videos');
					$active = 'tweets';
					break;

				default:

					$tabs = array('likes', 'liked_by', 'mutual_likes');
					$active = 'likes';
			}

			return array('tabs' => $tabs, 'active' => $active);
		}

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
			if($gender == 'men' || $gender == 'women') {
				$title .= $gender.' ';
			} 

			// Format the age and distance
			if($min > 18 || $max < 50) {
				$title .= 'ages '.$min.' to '.$max.' ';
			}

			// Format the city
			if(!empty($city)) {
				$title .= 'within '.$distance.' miles of '.$city.', ';
			}

			// Format the state
			if(!empty($state)) {
				$title .= $state;
			}

			return trim($title);
		}
	}
?>