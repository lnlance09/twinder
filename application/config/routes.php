<?php  
	if(!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = 'hot';

$route['404_override'] = 'error';

/* Sitemap */
$route['seo/sitemap\.xml'] = 'seo/sitemap';

/* Users page */
$route['users/(:any)'] = 'users/index/$1';
$route['users/discover'] = 'users/Discover/';
$route['users/Discover'] = 'users/Discover/';
$route['users/DiscoverLoad'] = 'users/DiscoverLoad/';
$route['users/EditProfile'] = 'users/EditProfile/';
$route['users/GetConnections'] = 'users/GetConnections/';
$route['users/GetMatchInfo'] = 'users/GetMatchInfo/';
$route['users/GetUpdates'] = 'users/GetUpdates/';
$route['users/GetPings'] = 'users/GetPings/';
$route['users/LikeUser'] = 'users/LikeUser/';
$route['users/Logout'] = 'users/Logout/';
$route['users/PassUser'] = 'users/PassUser/';
$route['users/ReportUser'] = 'users/ReportUser/';
$route['users/SendMessage'] = 'users/SendMessage/';
$route['users/UpdateProfile'] = 'users/UpdateProfile/';

/* Matches page */
$route['matches/(:any)'] = 'matches/index/$1';
$route['matches/MatchesBackend'] = 'matches/MatchesBackend/';
$route['matches/ThreadBackend'] = 'matches/ThreadBackend/';

// Hot page
// Gender
$route['hot/(:any)'] = 'hot/index/$1'; 

// City or Longitude
$route['hot/(:any)/(:any)'] = 'hot/index/$1/$2';

// State or Latitude
$route['hot/(:any)/(:any)/(:any)'] = 'hot/index/$1/$2/$3';

// Distance
$route['hot/(:any)/(:any)/(:any)/(:num)'] = 'hot/index/$1/$2/$3/$4';

// Minimum age
$route['hot/(:any)/(:any)/(:any)/(:num)/(:num)'] = 'hot/index/$1/$2/$3/$4/$5';

// Maximum age
$route['hot/(:any)/(:any)/(:any)/(:num)/(:num)/(:num)'] = 'hot/index/$1/$2/$3/$4/$5/$6';

// Search parameter
$route['hot/(:any)/(:any)/(:any)/(:num)/(:num)/(:num)/(:any)'] = 'hot/index/$1/$2/$3/$4/$5/$6/$7';

// Page number
$route['hot/(:any)/(:any)/(:any)/(:num)/(:num)/(:num)/(:any)/(:num)'] = 'hot/index/$1/$2/$3/$4/$5/$6/$7/$8';

// Get hottest method
$route['hot/GetHottest'] = 'hot/GetHottest';
$route['hot/GetHottest/(:any)'] = 'hot/GetHottest/$1';
