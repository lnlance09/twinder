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

// Default controller
$route['default_controller'] = 'home';

// 404 controller
$route['404_override'] = 'error';

// Sitemap 
$route['seo/sitemap\.xml'] = 'seo';

/* Users page */
$route['users/(:any)'] = 'users/index/$1';
$route['users/(:any)/(:any)'] = 'users/index/$1/$2';
$route['users/discover'] = 'users/Discover/';
$route['users/Discover'] = 'users/Discover/';
$route['users/DiscoverLoad'] = 'users/DiscoverLoad/';
$route['users/GetConnections'] = 'users/GetConnections/';
$route['users/GetMatchInfo'] = 'users/GetMatchInfo/';
$route['users/GetUpdates'] = 'users/GetUpdates/';
$route['users/LikeUser'] = 'users/LikeUser/';
$route['users/Logout'] = 'users/Logout/';
$route['users/PassUser'] = 'users/PassUser/';
$route['users/ReportUser'] = 'users/ReportUser/';
$route['users/SendMessage'] = 'users/SendMessage/';
$route['users/UpdateProfile'] = 'users/UpdateProfile/';
$route['users/UnmatchUser'] = 'users/UnmatchUser/';

/* Matches page */
$route['matches/(:any)'] = 'matches/index/$1';
$route['matches/MatchesBackend'] = 'matches/MatchesBackend/';
$route['matches/Thread'] = 'matches/Thread/';

/* Hot page */
$route['hot/(:any)'] = 'hot/index/$1';
$route['hot/GetHottest'] = 'hot/GetHottest';
$route['hot/GetHottest/(:any)'] = 'hot/GetHottest/$1';
$route['hot/HottestUser'] = 'hot/HottestUser';
