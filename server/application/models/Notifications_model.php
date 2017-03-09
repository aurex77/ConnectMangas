<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications_model extends CI_Model {

    private $table = "notifications";

    public function get_notifictions_user($id_user = NULL) {

        if ( !is_null($id_user) )
            $this->db->select("notifications.content,notifications.view,notifications.url")
                ->where('notifications.id_user', $id_user);

        $query = $this->db->get($this->table);

        if ( $query->num_rows() > 0 )
            return $query->row();

        return false;

    }

    public function add_notifications($data)
    {
        $this->db->insert($this->table, $data);
        return array('status' => 201,'message' => $data);
    }

    public function delete_notification($data)
    {
        $this->db->delete('$this->table', $data);
        return array('status' => 201,'message' => 'Data has been deleted.');
    }

}
