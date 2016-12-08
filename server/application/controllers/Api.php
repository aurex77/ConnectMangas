<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('user_model', 'user');
        $this->load->model('anime_model', 'anime');
        $this->load->model('manga_model', 'manga');
    }

    /*
    * Gestion des appels à l'API
    * @param $action
    */
    public function action($action) {
        $param = ( !is_null($this->uri->segment(4)) ) ? $this->uri->segment(4) : $this->uri->segment(2);

        switch ($action) {
            case "login":
                $this->login();
                break;
            case "logout":
                $this->logout();
                break;
            case "register":
                $this->_register();
                break;
            case "profil":
                $this->_get_user($param);
                break;
            case "profil_update":
                $this->_update_user();
                break;
            case "mangas":
                $this->_get_all_mangas();
                break;
            case "manga":
                $this->_get_manga($param);
                break;
            case "tomes":
                $this->_get_tomes($param);
                break;
            case "animes":
                $this->_get_all_animes();
                break;
            case "anime":
                $this->_get_anime($param);
                break;
            case "episodes":
                $this->_get_episodes($param);
                break;
            case "search":
                $this->_get_search_result($param);
                break;
            case "add_collection_anime":
                $this->_add_collection_anime();
                break;
            case "add_collection_episode":
                $this->_add_collection_episode();
                break;
            case "add_collection_manga":
                $this->_add_collection_manga();
                break;
            case "add_collection_tome":
                $this->_add_collection_tome();
                break;
            case "delete_collection_anime":
                $this->_delete_collection_anime();
                break;
            case "delete_collection_episode":
                $this->_delete_collection_episode();
                break;
            case "delete_collection_manga":
                $this->_delete_collection_manga();
                break;
            case "delete_collection_tome":
                $this->_delete_collection_tome();
                break;
            case "users_tome":
                $this->_get_users_tome();
                break;
            case "address":
                $this->_get_address();
                break;
            case "collection":
                $this->_get_collection();
                break;
            case "update_user":
                $this->_update_user();
                break;
            default:
                show_404();
                break;
        }

    }

    public function login()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->user->check_auth_client();
            if($check_auth_client == true){
                $params = json_decode(file_get_contents('php://input'), TRUE);
                
                $username = $params['username'];
                $password = $params['password'];
                
                $response = $this->user->login($username,$password);
                json_output($response['status'],$response);
            }
        }
    }

    public function logout()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->user->check_auth_client();
            if($check_auth_client == true){
                $response = $this->user->logout();
                json_output($response['status'],$response);
            }
        }
    }

    /*
    * Gestion des appels aux modèles
    * @param $id, $search
    */
    // Récupère l'utilisateur depuis son id
    private function _get_user($username) {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'GET'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->user->check_auth_client();
            if ($check_auth_client == true){

                $response = $this->user->auth();
                if($response['status'] == 200) {
                    if (empty($username) || !isset($username)) {
                        return json_output(403, array('status' => 403, 'message' => 'Username is missing.'));
                    }

                    if (!$user = $this->user->get_user($username, (int)$this->input->get_request_header('User-ID', TRUE))) {
                        print json_encode(array('status' => 403, 'message' => 'User not found.'));
                        exit;
                    }

                    $animes = $this->anime->get_anime_collection($user->id);
                    $mangas = $this->manga->get_manga_collection($user->id);
                    
                    print json_encode(array('status' => 200, 'infos' => $user, 'animes' => $animes, 'mangas' => $mangas));
                }
            }
        }

    }

    // Enregistre l'utilisateur
    private function _register() {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->user->check_auth_client();
            if ($check_auth_client == true) {
                $params = json_decode(file_get_contents('php://input'), TRUE);


                if (empty($params['username'])) {
                    return json_output(403, array('status' => 403, 'message' => 'Username must not be empty.'));
                }
                if (empty($params['password'])) {
                    return json_output(403, array('status' => 403, 'message' => 'Password must not be empty.'));
                }
                if (empty($params['email'])) {
                    return json_output(403, array('status' => 403, 'message' => 'Email must not be empty.'));
                }
                $username = $params['username'];
                $password = $params['password'];
                $email = $params['email'];
                if ($this->user->check_username($username) === true) {
                    return json_output(403, array('status' => 403, 'message' => 'Username already exist.'));
                }
                if ($this->user->check_email($email) === true) {
                    return json_output(403, array('status' => 403, 'message' => 'Email already exist.'));
                }

                $form_datas = array(
                    'username' => $username,
                    'password' => md5($password),
                    'email' => $email
                );

                $this->user->save($form_datas);
                $this->htmlmail($username, $email, 'Bienvenue sur ConnectMangas !');die();
                return json_output(200, array('status' => 200, 'message' => 'User created with success.'));
            }
        }
    }

    public function htmlmail($username, $email, $subject) {
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'connectmangas',
            'smtp_pass' => 'etnaconnectmangas',
            'smtp_timeout' => '4',
            'mailtype'  => 'html',
            'charset'   => 'iso-8859-1'
        );
        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");

        $this->email->from('connectmangas', 'Connectmangas Support');
        $data = array(
            'userName'=> $username
        );
        $this->email->to($email);  // replace it with receiver mail id
        $this->email->subject($subject); // replace it with relevant subject

        $body = $this->load->view('email/template.php',$data,TRUE);
        $this->email->message($body);
        $this->email->send();
    }

    // Récupère tous les mangas
    private function _get_all_mangas() {

        $mangas = $this->manga->getAll();
        print json_encode(array('status' => 200, 'infos' => $mangas));

    }

    // Récupère le manga depuis son id
    private function _get_manga($id) {

      $manga = $this->manga->get_manga($id, (int)$this->input->get_request_header('User-ID', TRUE));
      print json_encode(array('status' => 200, 'infos' => $manga));

    }

    // Récupère les tomes du manga depuis son id
    private function _get_tomes($id) {

        $tomes = $this->manga->get_tomes($id, (int)$this->input->get_request_header('User-ID', TRUE));
        print json_encode(array('status' => 200, 'infos' => $tomes));

    }

    // Récupère tous les animes
    private function _get_all_animes() {

        $animes = $this->anime->getAll();
        print json_encode(array('status' => 200, 'infos' => $animes));

    }

    // Récupère l'anime depuis son id
    private function _get_anime($id) {

        $anime = $this->anime->get_anime($id, (int)$this->input->get_request_header('User-ID', TRUE));
        print json_encode(array('status' => 200, 'infos' => $anime));

    }

    // Récupère les épisodes de l'anime depuis son id
    private function _get_episodes($id) {

        $episodes = $this->anime->get_episodes($id, (int)$this->input->get_request_header('User-ID', TRUE));
        print json_encode(array('status' => 200, 'infos' => $episodes));

    }

    // Récupère les animes et mangas depuis leur nom
    private function _get_search_result($search) {
      if(empty($search)){
          return json_output(403, array('status' => 403,'message' => 'Search empty.'));
      }

      $animes = $this->anime->get_anime_by_name($search);
      $mangas = $this->manga->get_manga_by_name($search);

        $result = ["animes" => [], "mangas" => []];
      if (!empty($animes)) {
          foreach ($animes as $row) {
              $result["animes"][] = array("id_anime" => intval($row->id_anime), "title" => $row->title, "year" => intval($row->year), "img_affiche" => $row->img_affiche);
          }
      }
      if (!empty($mangas)) {
          foreach ($mangas as $row) {
              $result["mangas"][] = array("id_manga" => intval($row->id_manga), "title" => $row->title, "year" => intval($row->year), "img_tome_fr" => $row->img_tome_fr, "img_tome_jp" => $row->img_tome_jp);
          }
      }

      print json_output(200,$result);
    }


    public function _add_collection_anime()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);
            
            $id_anime = (int)$params['id_anime'];

            $check_auth_client = $this->user->check_auth_client();
            $check_anime = $this->anime->check_anime($id_anime);
            if($this->anime->check_collection_anime($id_anime, (int)$this->input->get_request_header('User-ID', TRUE)) === true){
                return json_output(403, array('status' => 403,'message' => 'Already in collection.'));
            }

            if($check_auth_client === true && $check_anime === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_anime' => $id_anime,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->anime->add_collection_anime($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }


    public function _add_collection_episode()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);
            
            $id_anime = (int)$params['id_anime'];
            $number = (int)$params['number'];

            $check_auth_client = $this->user->check_auth_client();
            $check_anime = $this->anime->check_anime($id_anime);
            $check_episode = $this->anime->check_episode($id_anime, $number);
            if($this->anime->check_collection_episode($id_anime, $number, (int)$this->input->get_request_header('User-ID', TRUE)) === true){
                return json_output(403, array('status' => 403,'message' => 'Already in collection.'));
            }

            if($check_auth_client === true && $check_anime === true && $check_episode === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_anime' => $id_anime,
                        'number' => $number,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->anime->add_collection_episode($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }


    public function _add_collection_manga()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);
            
            $id_manga = (int)$params['id_manga'];

            $check_auth_client = $this->user->check_auth_client();
            $check_manga = $this->manga->check_manga($id_manga);
            if($this->manga->check_collection_manga($id_manga, (int)$this->input->get_request_header('User-ID', TRUE)) === true){
                return json_output(403, array('status' => 403,'message' => 'Already in collection.'));
            }

            if($check_auth_client === true && $check_manga === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_manga' => $id_manga,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->manga->add_collection_manga($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }


    public function _add_collection_tome()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);
            
            $id_manga = (int)$params['id_manga'];
            $number = (int)$params['number'];

            $check_auth_client = $this->user->check_auth_client();
            $check_tome = $this->manga->check_tome($id_manga, $number);
            if ($this->manga->check_collection_tome($id_manga, $number, (int)$this->input->get_request_header('User-ID', TRUE)) === true){
                return json_output(403, array('status' => 403,'message' => 'Already in collection.'));
            }

            if($check_auth_client === true && $check_tome === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_manga' => $id_manga,
                        'number' => $number,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->manga->add_collection_tome($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }


    public function _delete_collection_anime()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'DELETE'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);

            $id_anime = (int)$params['id_anime'];

            $check_auth_client = $this->user->check_auth_client();
            $check_anime = $this->anime->check_anime($id_anime);
            if($this->anime->check_collection_anime($id_anime, (int)$this->input->get_request_header('User-ID', true)) === false){
                return json_output(403, array('status' => 403,'message' => 'Not in collection.'));
            }

            if($check_auth_client === true && $check_anime === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_anime' => $id_anime,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->anime->delete_collection_anime($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }


    public function _delete_collection_episode()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'DELETE'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);

            $id_anime = (int)$params['id_anime'];
            $number = (int)$params['number'];

            $check_auth_client = $this->user->check_auth_client();
            $check_anime = $this->anime->check_anime($id_anime);
            $check_episode = $this->anime->check_episode($id_anime, $number);
            if($this->anime->check_collection_episode($id_anime, $number, (int)$this->input->get_request_header('User-ID', TRUE)) === false){
                return json_output(403, array('status' => 403,'message' => 'Not in collection.'));
            }

            if($check_auth_client === true && $check_anime === true && $check_episode === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_anime' => $id_anime,
                        'number' => $number,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->anime->delete_collection_episode($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }

    public function _delete_collection_manga()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'DELETE'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);

            $id_manga = (int)$params['id_manga'];

            $check_auth_client = $this->user->check_auth_client();
            $check_manga = $this->manga->check_manga($id_manga);
            if($this->manga->check_collection_manga($id_manga, (int)$this->input->get_request_header('User-ID', TRUE)) === false){
                return json_output(403, array('status' => 403,'message' => 'Not in collection.'));
            }

            if($check_auth_client === true && $check_manga === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_manga' => $id_manga,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->manga->delete_collection_manga($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }

    public function _delete_collection_tome()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'DELETE'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), TRUE);

            $id_manga = (int)$params['id_manga'];
            $number = (int)$params['number'];

            $check_auth_client = $this->user->check_auth_client();
            $check_tome = $this->manga->check_tome($id_manga, $number);
            if ($this->manga->check_collection_tome($id_manga, $number, (int)$this->input->get_request_header('User-ID', TRUE)) === false){
                return json_output(403, array('status' => 403,'message' => 'Not in collection.'));
            }

            if($check_auth_client === true && $check_tome === true){
                $response = $this->user->auth();
                $respStatus = $response['status'];
                if($response['status'] == 200){
                    $data = [
                        'id_manga' => $id_manga,
                        'number' => $number,
                        'id_user' => (int)$this->input->get_request_header('User-ID', TRUE)
                    ];

                    $resp = $this->manga->delete_collection_tome($data);
                    json_output($respStatus,$resp);
                }
            }
        }
    }

    private function _update_user() {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->user->check_auth_client();
            if ($check_auth_client == true){

                $response = $this->user->auth();
                if($response['status'] == 200) {
                    $params = $_POST;

                    $datas = [];

                    if (isset($params['password']) && !empty($params['password'])){
                        $user_infos['password'] = md5($params['password']);
                    }
                    if (isset($params['address']) && !empty($params['address'])){
                        $user_infos['address'] = $params['address'];

                        $map_api_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($params['address']);

                        $resp_json = file_get_contents($map_api_url);
                        $resp = json_decode($resp_json, true);

                        if(isset($resp['results'][0]['geometry']['location'])){
                            $user_infos['latitude'] = $resp['results'][0]['geometry']['location']['lat'];
                            $user_infos['longitude'] = $resp['results'][0]['geometry']['location']['lng'];
                        }else{
                            print json_encode(array('status' => 401, 'message' => 'Invalid address.'));
                            exit;
                        }
                    }
                    if (isset($_FILES['file']) && !empty($_FILES['file'])){;

                        $check = getimagesize($_FILES["file"]["tmp_name"]);
                        if($check === false) {
                            print json_encode(array('status' => 401, 'message' => "Le fichier n'est pas une image."));
                            exit;
                        }

                        if ($_FILES["file"]["size"] > 2000000) {
                            print json_encode(array('status' => 401, 'message' => "Le poids de l'image doit être inférieur à 2Mo."));
                            exit;
                        }

                        $target_file = getcwd()."/../client/medias/profils/".basename($_FILES["file"]["name"]);

                        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

                        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                            print json_encode(array('status' => 401, 'message' => "L'image doit être au format JPG, JPEG, PNG ou GIF."));
                            exit;
                        }

                        $filename = uniqid().".".$imageFileType;
                        $datas['img_profil'] = $filename;

                        $destination = getcwd()."/../client/medias/profils/".$filename;

                        if (!move_uploaded_file($_FILES['file']['tmp_name'] , $destination)){
                            print json_encode(array('status' => 401, 'message' => "Une erreur est survenue lors de l'upload de l'image."));
                            exit;
                        }

                        $user_infos['img_profil'] = $filename;
                    }

                    $user_infos['date_upd'] = date('Y-m-d H:i:s');

                    if ($this->user->update_user((int)$this->input->get_request_header('User-ID', TRUE), $user_infos)) {
                        print json_encode(array('status' => 200, 'message' => 'User updated with success.', 'datas' => $datas));
                    } else {
                        print json_encode(array('status' => 403, 'message' => 'User could not be updated.'));
                    }
                }
            }
        }

    }

    private function _get_address() {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'GET'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->user->check_auth_client();
            if ($check_auth_client === true){
                $response = $this->user->auth();
                if($response['status'] == 200) {
                    $address = $this->input->get('address');

                    if (empty($address)){
                        return json_output(403, array('status' => 403,'message' => 'Address is empty.'));
                    }

                    $resp_json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?components=country:FR&address=".urlencode($address));
                    $resp = (object) json_decode($resp_json, true);
                    $address = [];
                    if($resp->status == "OK" && count($resp->results)){
                        foreach($resp->results as $result){
                            if(
                                isset($result['geometry']['location']['lat'])
                                && isset($result['geometry']['location']['lng'])
                            )
                                $address[] = [
                                    'address' => $result['formatted_address'],
                                    'latitude' => $result['geometry']['location']['lat'],
                                    'longitude' => $result['geometry']['location']['lng']
                                ];
                        }
                    }

                    print json_encode(array('status' => 200, 'total'=>count($address), 'infos' => $address));
                }
            }
        }
    }

    private function _get_users_tome() {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'GET'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $id_manga = $this->input->get('id_manga');
            $number = $this->input->get('number');
            
            $check_auth_client = $this->user->check_auth_client();
            $check_tome = $this->manga->check_tome($id_manga, $number);
            if ($check_auth_client === true && $check_tome === true){

                $response = $this->user->auth();
                if($response['status'] == 200) {
                    $users = $this->user->_get_users_tome($id_manga, $number, (int)$this->input->get_request_header('User-ID', TRUE));
                    print json_encode(array('status' => 200, 'total' => count($users), 'infos' => $users));
                }
            }
        }
    }


    // Récupère la collection de l'utilisateur
    private function _get_collection() {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'GET'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            if ($this->user->check_auth_client()) {
                $response = $this->user->auth();
                if($response['status'] == 200) {
                    $id_user = (int)$this->input->get_request_header('User-ID', TRUE);

                    $animes = $this->anime->get_anime_collection($id_user);
                    $mangas = $this->manga->get_manga_collection($id_user);

                    $result = ["animes" => [], "mangas" => []];
                    if (!empty($animes)) {
                        foreach ($animes as $anime) {
                            $result["animes"][] = $anime;
                        }
                    }
                    if (!empty($mangas)) {
                        foreach ($mangas as $manga) {
                            $result["mangas"][] = $manga;
                        }
                    }

                    print json_encode(['status' => 200, 'infos' => $result]);
                }
            }

        }
    }

}
