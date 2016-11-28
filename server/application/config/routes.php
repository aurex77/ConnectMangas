<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'api';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login']['post']           = 'login';
$route['logout']['post']          = 'logout';
$route['register']['post']          = 'register';

/* $route['(:any)'] = 'api/action/$1';
$route['anime/(:num)'] = 'api/action/anime/$1';
$route['manga/(:num)'] = 'api/action/manga/$1';
$route['profil/(:num)'] = 'api/action/profil/$1';
$route["search"]['get'] = "api/action/search";
$route["api/action"] = "api/error"; */

$route['add_collection_anime']['post'] = 'add_collection_anime';
$route['add_collection_episode']['post'] = 'add_collection_episode';
$route['add_collection_manga']['post'] = 'add_collection_manga';
$route['add_collection_tome']['post'] = 'add_collection_tome';

$route['delete_collection_anime']['delete'] = 'delete_collection_anime';
$route['delete_collection_episode']['delete'] = 'delete_collection_episode';
$route['delete_collection_manga']['delete'] = 'delete_collection_manga';
$route['delete_collection_tome']['delete'] = 'delete_collection_tome';

$route['profil/(:num)']['get'] = 'profil/$1';
$route['profil_update']['put'] = 'profil_update';

$route['address']['get'] = 'address';
$route['users_tome']['get'] = 'users_tome';