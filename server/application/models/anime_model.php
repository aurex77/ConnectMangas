<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anime_model extends CI_Model {

    private $table = 'animes';

    public function getAll() {

        $query = $this->db->get($this->table);

        if ( $query->num_rows() > 0 )
            return $query->result();

        return FALSE;

    }

    public function get_anime($id = NULL, $id_user = NULL) {

        if ( !is_null($id) )
            $this->db->select("animes.id_anime,
                        animes.title,
                        publics.name as public,
                        animes.nb_episodes,
                        animes.duration,
                        animes.season,
                        animes.month,
                        animes.year,
                        animes.diffusion,
                        studios.name as studio,
                        animes.synopsis,
                        animes.img_affiche,
                        animes.img_banniere,
                        (SELECT GROUP_CONCAT(genres.name)
                         FROM animes_genres, genres
                         WHERE animes_genres.id_genre = genres.id
                         AND animes_genres.id_anime = animes.id_anime) as genres,
                        (SELECT GROUP_CONCAT(themes.name)
                         FROM animes_themes, themes
                         WHERE animes_themes.id_theme = themes.id
                         AND animes_themes.id_anime = animes.id_anime) as themes,
                        (SELECT COUNT(animes_collection.id)
                         FROM animes_collection
                         WHERE animes_collection.id_anime = animes.id_anime
                         AND animes_collection.id_user = ".(int)$id_user.") as inCollection")
                ->join("animes_titles", "animes_titles.id_anime = animes.id_anime")
                ->join("publics", "publics.id = animes.id_public", 'left')
                ->join("studios", "studios.id = animes.id_studio", 'left')
                ->where('animes.id_anime', $id);

        $query = $this->db->get($this->table);

        if ( $query->num_rows() > 0 )
            return $query->row();

        return FALSE;

    }

    public function get_episodes($id = NULL, $id_user = NULL) {

        if ( !is_null($id) )
            $this->db->select("id_anime,
                        number,
                        title,
                        diffusion,
                        hs,
                        screenshot1,
                        screenshot2,
                        screenshot3,
                        screenshot4,
                        screenshot5,
                        screenshot6,
                        (SELECT COUNT(episodes_collection.id)
                         FROM episodes_collection
                         WHERE episodes_collection.id_anime = animes_episodes.id_anime
                         AND episodes_collection.number = animes_episodes.number
                         AND episodes_collection.id_user = ".(int)$id_user.") as inCollection")
                ->where('id_anime', $id);

        $query = $this->db->get("animes_episodes");

        if ( $query->num_rows() > 0 )
            return $query->result();

        return FALSE;

    }

    public function get_anime_by_name($name = NULL) {

        $segments = explode(" ", $name);

        if ( !is_null($name) ) {

            $this->db->select("animes.id_anime, animes.title, animes.year")
                ->join("animes_titles", "animes_titles.id_anime = animes.id_anime");

            foreach ($segments as $segment) {
                $this->db->where("(animes.title LIKE '%$segment%'
                    OR animes_titles.title LIKE '%$segment%')");
            }

            $this->db->group_by('animes.id_anime');
            $this->db->limit(10);

            $query = $this->db->get($this->table);
            if ( $query->num_rows() > 0 )
                return $query->result();

        }

        return FALSE;
    }

    public function add_collection_anime($data)
    {
        $this->db->insert('animes_collection',$data);
        return array('status' => 201,'message' => 'Data has been created.');
    }

    public function add_collection_episode($data)
    {
        $this->db->insert('episodes_collection',$data);
        return array('status' => 201,'message' => 'Data has been created.');
    }

    public function check_anime($id_anime){
        $this->db->select("id_anime")
            ->where("id_anime", $id_anime);

        $query = $this->db->get($this->table);
        if ( $query->num_rows() > 0 )
            return true;

        return json_output(403, array('status' => 403,'message' => 'Anime not found.'));
    }

    public function check_collection_anime($id_anime, $id_user){
        $this->db->select("id_anime")
            ->where("id_anime", $id_anime)
            ->where("id_user", $id_user);

        $query = $this->db->get("animes_collection");
        if ( $query->num_rows() > 0 )
            return true;

        return false;
    }

    public function check_collection_episode($id_anime, $number, $id_user){
        $this->db->select("id_anime")
            ->where("id_anime", $id_anime)
            ->where("number", $number)
            ->where("id_user", $id_user);

        $query = $this->db->get("episodes_collection");
        if ( $query->num_rows() > 0 )
            return true;

        return false;
    }

    public function check_episode($id_anime, $number){
        $this->db->select("id_anime")
            ->where("id_anime", $id_anime)
            ->where("number", $number);

        $query = $this->db->get("animes_episodes");
        if ( $query->num_rows() > 0 )
            return true;

        return json_output(403, array('status' => 403,'message' => 'Episode not found.'));
    }

    public function delete_collection_anime($data)
    {
        $this->db->delete('animes_collection', $data);
        return array('status' => 201,'message' => 'Data has been deleted.');
    }

    public function delete_collection_episode($data)
    {
        $this->db->delete('episodes_collection', $data);
        return array('status' => 201,'message' => 'Data has been deleted.');
    }

}