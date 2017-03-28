<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Friends_model extends CI_Model {

    private $table = "friends";

    public function get_friends_user($id_user = NULL) {

        if (!is_null($id_user)) {
            $this->db->select("friends.UserID2")
                ->where('friends.UserID1', $id_user);
        }

        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return false;

    }

    public function add_friends($data)
    {
        $this->db->insert($this->table, $data);
        return array('status' => 201,'message' => $data);
    }

    public function delete_friends($data)
    {
        $this->db->delete($this->table, $data);
        return array('status' => 201,'message' => 'Data has been deleted.');
    }

}
