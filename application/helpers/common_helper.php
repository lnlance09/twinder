<?php
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
		// Send the request to one of Tinder's API endpoints
		if(!function_exists('SendRequest')) {
			function SendRequest($url, $auth = NULL, $post, $post_data) {
				// Define the HTTP headers
				$headers = array('app-version: 123',
								'os_version: 80000100001',
								'Accept: */*',
								'platform: ios',
								'Content-Type: application/json; charset=utf-8');

				if($auth != NULL) {
					array_push($headers, 'Authorization: Token token="'.$auth.'"', 'X-Auth-Token: '.$auth);
				}

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.gotinder.com/'.$url);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Tinder/4.0.9 (iPhone; iOS 8.1.1; Scale/2.00)');
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

			    if($post === TRUE) {
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

		//  Get the formatted name of a location from its latitude and longitude coordinates
		if(!function_exists('GeoLocation')) {
			function GeoLocation($lon, $lat) {
				// $api_key = 'AIzaSyCy6LbgbzAqWNbPnUQx_lH60pTuurk43Cs';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lon.'&sensor=false');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				$data = curl_exec($ch);
			    curl_close($ch);

			    // Decode the response
			    $decode = @json_decode($data, TRUE);
			    return $decode;
			}
		}

		// Find the Distance between two places
		if(!function_exists('Haversine')) {
			function Haversine($lat_from, $lon_from, $lat_to, $lon_to) {
				$radius = 6371000;
				$delta_lat = deg2rad($lat_to - $lat_from);
				$delta_lon = deg2rad($lon_to - $lon_from);
				
				$a = sin($delta_lat/2) * sin($delta_lat/2) +
					cos(deg2rad($lat_from)) * cos(deg2rad($lat_to)) *
					sin($delta_lon/2) * sin($delta_lon/2);
				$c = 2*atan2(sqrt($a), sqrt(1-$a));
				return ceil(($radius*$c)*0.000621371);
			}
		}

		// Format the user's bios to link to their Instagram profiles and Twitter hashtags
		if(!function_exists('BioLinks')) {
			function BioLinks($bio) {
				$terms = array('instagram', 'ig', 'insta', 'Instagram', 'Ig', 'Insta', 'INSTAGRAM', 'IG', 'INSTA');
				$string = implode('|', $terms);

				// Make links out of he Instagram usernames and Twitter hashtags
				$ig_bio = preg_replace('/\b('.$string.')\s*[:-]\s*\K([\w.]+)\b/', '<a href="http://instagram.com/$2" target="_blank">$2</a>', $bio);
				$hash_bio = preg_replace('/#(\w+)/', ' <a href="http://twitter.com/hashtag/$1" target="_blank">#$1</a> ', $ig_bio);
				return trim($hash_bio);
			}
		}

		// Format a user's WeTinder link according to their username
		if(!function_exists('FormatUserLink')) {
			function FormatUserLink($tinder_id, $username) {
				if(strlen($username) > 0) {
					return 'users/'.$username;
				} else {
					return 'users/'.$tinder_id;
				}
			}
		}

		// Format the user's gender name
		if(!function_exists('FormatGender')) {
			function FormatGender($num) {
				if($num == 0) {
					return 'male';
				} else {
					return 'female';
				}
			}
		}

		// Get the gender's number from its name
		if(!function_exists('ReverseGender')) {
			function ReverseGender($name) {
				if($name == 'male') {
					return 0;
				} else {
					return 1;
				}
			}
		}

		// Format the user's interested in
		if(!function_exists('FormatInterestedIn')) {
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

		// Get the user's interested in number from the name
		if(!function_exists('ReverseInterestedIn')) {
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

		// Format a user's likes, passes and matches numbers
		if(!function_exists('FormatNumber')) {
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

		// Format the time that the user was last online
		if(!function_exists('FormatTime')) {
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

		// Find out a user's age from their date of birth
		if(!function_exists('ReturnAge')) {
			function ReturnAge($birthday) {
				$dob = date('M j, Y', strtotime($birthday));
				return date_diff(date_create(), date_create($dob))->format('%y');
			}
		}

		// Return an array containing all of the user's pics (smallest size)
		if(!function_exists('ReturnPicsArray')) {
			function ReturnPicsArray($photos) {
				$pics = array();

				for($i=0;$i<count($photos);$i++) {
					$pics[$i] = $photos[$i]['fileName']; 
				}

				return $pics;
			}
		}

		// Find out which of the user's pics is their profile pic
		if(!function_exists('ReturnProfilePic')) {
			function ReturnProfilePic($photos) {
				for($i=0;$i<count($photos);$i++) {
					if($photos[$i]['fileName'] !== FALSE) {
						return $photos[$i]['fileName']; 
						break;
					}
				}
			}
		}

		// Find out which of the user's pics is their profile pic
		if(!function_exists('FormatLastSeenText')) {
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

				$raw_data = $data['data'];
				$time = $raw_data['datetime'];
				$distance = $raw_data['miles_away'];
				$city = $raw_data['city'];
				$state = $raw_data['state'];
				$lon = $raw_data['lon'];
				$lat = $raw_data['lat'];

				$text = "<div id='infowindow'><h3><img src='http://images.gotinder.com/".$tinder_id."/84x84_".$pic."' width='50' height='50' alt='".$name."' class='img-circle'> <a href='".$base_url.$link."'>".$name."</a>
						</h3> <p>".FormatTime($time)." <br> ".$distance." miles away <br>".$city.", ".$state." <br> ".$lon.", ".$lat."</p></div>";
				return trim($text);
			}

		}

		// Return the link to the user's cookie file
		if(!function_exists('CookieFile')) {
			function CookieFile($email) {
				$exp = explode('@', $email);

				if(count($exp) > 1) {
					$file = $exp[0];
				} else {
					$file = $email;
				}

				// Define the path to the cookies
			    return $file.'.txt';
			}
		}

		// Format an json decoded array
		if(!function_exists('FormatArray')) {
			function FormatArray($array, $style = NULL) {
				if($style !== NULL) {
					echo '<div style="color: #090127;text-shadow:none;text-align:left;">';
				}

				echo '<pre>';
				print_r($array);
				echo '</pre>';

				if($style !== NULL) {
					echo '</div>';
				}
			}
		}

		// Convert miles to meters
		if(!function_exists('MilesToMeters')) {
			function MilesToMeters($miles) {
				return ceil($miles/0.000621371);
			}
		}	

		// Format the time that was 5 minutes ago
		if(!function_exists('RequestTime')) {
			function RequestTime($time) {
				if($time === NULL) {
					return $time;
				} else {
					return date('Y-m-d', strtotime($time)).'T'.date('h:i:s', strtotime($time)).'.906Z';
				}
			}
		}

		// Get a user's first and last names
		if(!function_exists('FormatNames')) {
			function FormatNames($name) {
				$exp = explode(' ', $name);
				$exp_num = count($exp);
				return array('first_name' => $exp[0], 'last_name' => $exp[$exp_num-1]);
			}
		}

		if(!function_exists('MetaSubject')) {
			function MetaSubject($username, $name) {
				if($username == '') {
					return $name;
				} else {
					return $username;
				}
			}
		}

		// Print out pagination links
		if(!function_exists('Pagination')) {
			function Pagination($page, $pages) {
				$each = 5;
				$low_point = $page-$each;
				$high_point = $page+$each;

				if($low_point < 0) {
					$low_point = 0;
				}

				if($high_point > $pages) {
					$high_point = $pages;
				}

		        if($page > 0) {
		            echo '<li><a href="#">Prev</a></li>';
		        }

		        // Loop thru all of the previous pages
		        for($i=$low_point;$i<$page;$i++) {
		            echo '<li><a href="#">'.($i+1).'</a></li>';
		        }

		        echo '<li class="active"><a href="#">'.($page+1).'</a></li>';;

		        // Loop thru all of the next pages
		        for($i=($page+1);$i<$high_point;$i++) {
		            echo '<li><a href="#">'.($i+1).'</a></li>';
		        }

		        if($page < ($pages-1)) {
		            echo '<li><a href="#">Next</a></li>';
		        }
			}
		}	
	}
?>