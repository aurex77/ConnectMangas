<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    private $table = 'users';

    var $client_service = "frontend-client";
    var $auth_key       = "simplerestapi";

    public function save($datas = NULL) {

        if ( !empty($datas) ) {
            $lastId = $this->db->insert($this->table, $datas);
            return $lastId;
        }

        return FALSE;

    }

    public function check_auth_client(){
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key  = $this->input->get_request_header('Auth-Key', TRUE);
        if($client_service == $this->client_service && $auth_key == $this->auth_key){
            return true;
        } else {
            return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
        }
    }

    public function login($username,$password)
    {
        $q  = $this->db->select('password,id')->from('users')->where('username',$username)->get()->row();
        if($q == ""){
            return array('status' => 403,'message' => 'Username not found.');
        } else {
            $hashed_password = $q->password;
            $id              = $q->id;
            if ($hashed_password === md5($password)) {
                $last_login = date('Y-m-d H:i:s');
                $token = bin2hex(openssl_random_pseudo_bytes(32));
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->db->trans_start();
                $this->db->where('id',$id)->update('users',array('last_login' => $last_login));
                $this->db->insert('users_authentication',array('id_user' => $id,'token' => $token,'date_expired' => $expired_at));
                if ($this->db->trans_status() === FALSE){
                    $this->db->trans_rollback();
                    return array('status' => 500,'message' => 'Internal server error.');
                } else {
                    $this->db->trans_commit();
                    return array('status' => 200,'message' => 'Successfully login.','id' => $id, 'token' => $token, 'username' => $username);
                }
            } else {
                return array('status' => 403,'message' => 'Wrong password.');
            }
        }
    }

    public function get_user($username = NULL, $myId, $getAllInfos = false) {

        $this->db->select("id")
            ->where('username', $username);

        $query = $this->db->get($this->table);

        if (isset($query) && $query->num_rows() > 0)
            $id_user = $query->row("id");
        else
            $id_user = null;

        if ($id_user == $myId || $getAllInfos) {
            $this->db->select("id, username, email, address, latitude, longitude, img_profil, DATE_FORMAT(date_create, '%d/%m/%Y') as date_create");
        }else{
            $this->db->select("id, username, img_profil, DATE_FORMAT(date_create, '%d/%m/%Y') as date_create");
        }

        $this->db->where('username', $username);

        $query = $this->db->get($this->table);

        if (isset($query) && $query->num_rows() > 0)
            return $query->row();

        return false;

    }

    public function auth()
    {
        $id_user  = $this->input->get_request_header('User-ID', TRUE);
        $token     = $this->input->get_request_header('Authorization', TRUE);
        $q  = $this->db->select('date_expired')->from('users_authentication')->where('id_user',$id_user)->where('token',$token)->get()->row();
        if($q == ""){
            return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
        } else {
            if($q->date_expired < date('Y-m-d H:i:s')){
                return json_output(401,array('status' => 401,'message' => 'Your session has been expired.'));
            } else {
                $date_upd = date('Y-m-d H:i:s');
                $date_expired = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->db->where('id_user',$id_user)->where('token',$token)->update('users_authentication',array('date_expired' => $date_expired,'date_upd' => $date_upd));
                return array('status' => 200,'message' => 'Authorized.');
            }
        }
    }

    public function check_username($username){
        $this->db->select("id")
            ->where("username", $username);

        $query = $this->db->get($this->table);
        if ( $query->num_rows() > 0 )
            return true;

        return false;
    }

    public function check_email($email){
        $this->db->select("id")
            ->where("email", $email);

        $query = $this->db->get($this->table);
        if ( $query->num_rows() > 0 )
            return true;

        return false;
    }

    public function update_user($id_user, $infos){
        if ($this->db->where('id',$id_user)->update('users',$infos)) {
            return true;
        }else{
            return false;
        }
    }


    public function _get_users_tome($id_manga, $number, $id_user, $latitude, $longitude) {

        if ($latitude == null) {
            $this->db->select("latitude, longitude")
                ->where('id', $id_user);

            $query = $this->db->get($this->table);

            if (isset($query) && $query->num_rows() > 0) {
                $latitude = $query->row("latitude");
                $longitude = $query->row("longitude");
            }
        }

        $this->db->select("users.id, users.username, users.img_profil, tomes_collection.date_add,
        ROUND(6366*ACOS(COS(RADIANS(".$latitude."))*COS(RADIANS(users.`latitude`))*COS(RADIANS(users.`longitude`) -RADIANS(".$longitude."))+SIN(RADIANS(".$latitude."))*SIN(RADIANS(users.`latitude`)))
	    ) as distance")
            ->join("tomes_collection", "tomes_collection.id_user = users.id")
            ->where('tomes_collection.id_manga', $id_manga)
            ->where('tomes_collection.number', $number)
            ->where('tomes_collection.id_user !=', $id_user)
            ->where('users.latitude IS NOT NULL', null, false)
            ->where('users.longitude IS NOT NULL', null, false)
            ->having('distance < 50')
            ->group_by("users.id")
            ->order_by("distance", "asc")
            ->limit(20);

        $query = $this->db->get($this->table);

        if (isset($query) && $query->num_rows() > 0)
            return $query->result();

        return false;

    }

    public function check_request_tome($id_manga, $number, $id_user_src, $id_user_dest){
        $this->db->select("request_tome.id")
            ->where('request_tome.id_manga', $id_manga)
            ->where('request_tome.number', $number)
            ->where('request_tome.id_user_src', $id_user_src)
            ->where('request_tome.id_user_dest', $id_user_dest)
            ->where('request_tome.date_add >= ADDDATE(NOW(), INTERVAL -1 MONTH)');

        $query = $this->db->get("request_tome");

        if (isset($query) && $query->num_rows() > 0)
            return false;

        return true;
    }

    public function save_request_tome($id_manga, $number, $id_user_src, $id_user_dest){
        $this->db->insert('request_tome', array('id_manga' => $id_manga,'number' => $number,'id_user_src' => $id_user_src,'id_user_dest' => $id_user_dest));
    }

}
