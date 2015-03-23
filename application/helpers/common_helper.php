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
				if(empty($bio)) {
					return $name." doesn't have a bio.";
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

				// Turn the links to anchor tags
				$link_bio = preg_replace('#(?<!href\=[\'"])(https?|ftp|file)://[-A-Za-z0-9+&@\#/%()?=~_|$!:,.;]*[-A-Za-z0-9+&@\#/%()=~_|$]#', 'regexp_url_search', $bio);
				
				// Make links out of he Instagram usernames and Twitter hashtags
				$ig_bio = preg_replace('/\b('.$string.')\s*[:-]\s*\K([\w.]+)\b/', '<a href="http://instagram.com/$2" target="_blank">$2</a>', $link_bio);
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
		}

		if(!function_exists('FormatNumber')) {
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
				if($time != 'Just now') {
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
					$pics[$i] = $photos[$i]['processedFiles'][0]['url'];
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
		}

		if(!function_exists('ChangePicSize')) {
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
		}

		if(!function_exists('FormatLastSeenText')) {
			/**
			 * Format the popup window for the Google Maps API
			 * @param {array} [data] An array containg info about the user
			 * @param {string} [base_url] The base URL of WeTinder
			 */
			function FormatLastSeenText($data, $base_url) {
				// Save the user's info as variables
				$user_info = $data['user'];
				$tinder_id = $user_info['tinder_id'];
				$name = $user_info['name'];
				$link = $user_info['link'];
				$gender = $user_info['gender'];
				$pic = ChangePicSize($user_info['profile_pic'], 172);

				// Define the subject
				$subject = FormatArticle($gender);

				// Save the location data
				$raw_data = $data['data'];
				$time = $raw_data['datetime'];
				$distance = $raw_data['miles_away'];
				$city = $raw_data['city'];
				$state = $raw_data['state'];
				$lon = $raw_data['lon'];
				$lat = $raw_data['lat'];
	
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
				                    '.$distance.' miles away from '.$city.', '.$state.' <br>

				                    '.FormatTime($time).'
				                </p>
				            </div>
				        </div>';

				return trim($text);
			}

		}

		if(!function_exists('FormatPossesion')) {
			function FormatPossesion($gender) {
				if($gender == 0) {
					return 'his';
				} else {
					return 'her';
				}
			}
		}

		if(!function_exists('ReturnFA')) {
			function ReturnFA($type) {
				switch($type) {
		            case'likes':

		                $fa = 'thumbs-up';
		                break;

		            case'matches':

		                $fa = 'heart';
		                break;

		            case'passes':

		                $fa = 'thumbs-down';
		                break;

		            case'tweets':

		                $fa = 'twitter';
		                break;
		        }

		        return $fa;
		    }
	    }


		if(!function_exists('FormatArticle')) {
			function FormatArticle($gender) {
				if($gender == 0) {
					return 'he';
				} else {
					return 'she';
				}
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
				if(empty($username)) {
					return $name;
				} else {
					return $username;
				}
			}
		}

		if(!function_exists('ReturnTabs')) {
			function ReturnTabs($tab) {
				switch($tab) {
					case'likes':
					case'liked_by':
					case'mutual_likes':

						$tabs = array('likes', 'liked_by', 'mutual_likes');
						$active = 'likes';
						break;

					case'passes':
					case'passed_by':
					case'mutual_passes':

						$tabs = array('passes', 'passed_by', 'mutual_passes');
						$active = 'passes';
						break;	

					case'matches':
					case'mutual_matches':

						$tabs = array('matches', 'mutual_matches');
						$active = 'matches';
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
		}

		if(!function_exists('RowPagination')) {
			function RowPagination($count, $per_row, $per_page, $page, $pages, $start) {
				if($page == ($pages-1)) {
					$mod = $count%$per_page;

					if($mod > 0) {
						$end = $mod;
						$col_mod = $end%$per_row;

						if($col_mod == 0) {
							$end_col = $end;
						} else {
							$end_col = $count-$col_mod;
						}
					} else {
						$end = $start+$per_page;
						$end_col = $end;
					}
				} else {
					$end = $start+$per_page;
					$end_col = $end;
				}

				$num_rows = ceil($end/$per_row);

				return array('end' => $end, 'end_col' => $end_col, 'num_rows' => $num_rows);
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
				if($gender == 'men' || $gender == 'women') {
					$title .= $gender.' ';
				} 

				// Format the age and distance
				$title .= 'ages '.$min.' to '.$max.' within '.$distance.' miles of ';

				// Format the city
				if($city != '') {
					$title .= $city.', ';
				}

				// Format the state
				if($state != '') {
					$title .= $state.' ';
				}

				return $title;
			}
		}
	}
?>