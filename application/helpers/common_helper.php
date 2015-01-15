<?php
	if(!defined('BASEPATH')) {
		exit('No direct script access allowed');
	} else {
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

		if(!function_exists('GeoLocation')) {
			function GeoLocation($lon, $lat) {
				$api_key = 'AIzaSyCy6LbgbzAqWNbPnUQx_lH60pTuurk43Cs';

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

		if(!function_exists('CircleDistance')) {
			function CircleDistance($lat_from, $lon_from, $lat_to, $lon_to, $radius = 6371000) {
				// Convert from degrees to radians
				$lat_from = deg2rad($lat_from);
				$lon_from = deg2rad($lon_from);
				$lat_to = deg2rad($lat_to);
				$lon_to = deg2rad($lon_to);
				$lon_delta = $lon_to-$lon_from;

				$a = pow(cos($lat_to)*sin($lon_delta), 2) + pow(cos($lat_from)*sin($lat_to) - sin($lat_from)*cos($lat_to)*cos($lon_delta), 2);
				$b = sin($lat_from)*sin($lat_to) + cos($lat_from)*cos($lat_to)*cos($lon_delta);
				$angle = atan2(sqrt($a), $b);

				return ceil(($angle*$radius)*0.000621371);
			}
		}

		if(!function_exists('BioLinks')) {
			function BioLinks($bio) {
				$terms = array('instagram', 'ig', 'insta', 'Instagram', 'Ig', 'Insta', 'INSTAGRAM', 'IG', 'INSTA');
				$string = implode('|', $terms);

				// Make links out of he Instagram usernames and Twitter hashtags
				$ig_bio = preg_replace('/\b('.$string.')\s*[:-]\s*\K([\w.]+)\b/', '<a href="http://instagram.com/$2" target="_blank">$2</a>', $bio);
				$hash_bio = preg_replace('/#(\w+)/', ' <a href="http://twitter.com/hashtag/$1" target="_blank">#$1</a> ', $ig_bio);
				return $hash_bio;
			}
		}

		if(!function_exists('FormatUserLink')) {
			function FormatUserLink($tinder_id, $username) {
				if(strlen($username) > 0) {
					return 'users/'.$username;
				} else {
					return 'users/'.$tinder_id;
				}
			}
		}

		if(!function_exists('FormatGender')) {
			function FormatGender($num) {
				if($num == 0) {
					return 'male';
				} else {
					return 'female';
				}
			}
		}

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

		if(!function_exists('FormatNumber')) {
			function FormatNumber($num) {
				if($num > 1000) {
					return floor($num/1000).'K';
				} else {
					return $num;
				}
			}
		}

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

		if(!function_exists('StripPic')) {
			function StripPic($pic_name) {
				$exp = explode('/', $pic_name);
				$pic = $exp[count($exp)-1];
				return $pic;
			}
		}

		if(!function_exists('FormatArray')) {
			function FormatArray($array) {
				echo '<pre>';
				print_r($array);
				echo '</pre>';
			}
		}

		if(!function_exists('MilesToMeters')) {
			function MilesToMeters($miles) {
				return ceil($miles/0.000621371);
			}
		}	

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