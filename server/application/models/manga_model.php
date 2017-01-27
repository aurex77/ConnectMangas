<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manga_model extends CI_Model {

    private $table = "mangas";

    public function getAll() {

        $query = $this->db->get($this->table);

        if ( $query->num_rows() > 0 )
            return $query->result();

        return FALSE;

    }

    public function get_manga($id = NULL, $id_user = NULL) {

        if ( !is_null($id) )
            $this->db->select("mangas.id_manga,
                        mangas.title,
                        publics.name as public,
                        mangas.nb_tomes,
                        mangas.year,
                        mangas.publication_jp,
                        mangas.publication_fr,
                        editeurs_jp.name as editeur_jp,
                        editeurs_fr.name as editeur_fr,
                        mangas.synopsis,
                        (SELECT mangas_tomes.couverture_fr
                         FROM mangas_tomes
                         WHERE mangas_tomes.id_manga = mangas.id_manga
                         AND couverture_fr IS NOT NULL
                         AND couverture_fr != ''
                         ORDER BY number
                         LIMIT 1) as img_tome_fr,
                        (SELECT mangas_tomes.couverture_jp
                         FROM mangas_tomes
                         WHERE mangas_tomes.id_manga = mangas.id_manga
                         AND couverture_jp IS NOT NULL
                         AND couverture_jp != ''
                         ORDER BY number
                         LIMIT 1) as img_tome_jp,
                        mangas.img_banniere,
                        (SELECT GROUP_CONCAT(genres.name)
                         FROM mangas_genres, genres
                         WHERE mangas_genres.id_genre = genres.id
                         AND mangas_genres.id_manga = mangas.id_manga) as genres,
                        (SELECT GROUP_CONCAT(themes.name)
                         FROM mangas_themes, themes
                         WHERE mangas_themes.id_theme = themes.id
                         AND mangas_themes.id_manga = mangas.id_manga) as themes,
                        (SELECT COUNT(mangas_collection.id)
                         FROM mangas_collection
                         WHERE mangas_collection.id_manga = mangas.id_manga
                         AND mangas_collection.id_user = ".(int)$id_user.") as inCollection")
                ->join("mangas_titles", "mangas_titles.id_manga = mangas.id_manga")
                ->join("publics", "publics.id = mangas.id_public", 'left')
                ->join("editeurs as editeurs_jp", "editeurs_jp.id_editeur = mangas.id_editeur_jp", 'left')
                ->join("editeurs as editeurs_fr", "editeurs_fr.id_editeur = mangas.id_editeur_fr", 'left')
                ->where('mangas.id_manga', $id);

        $query = $this->db->get($this->table);

        if ( $query->num_rows() > 0 )
            return $query->row();

        return FALSE;

    }

    public function get_tomes($id = NULL, $id_user = NULL) {

        if ( !is_null($id) )
            $this->db->select("mangas_tomes.id_manga,
                        mangas_tomes.number,
                        DATE_FORMAT(mangas_tomes.publication_jp, '%d/%m/%Y') as publication_jp,
                        DATE_FORMAT(mangas_tomes.publication_fr, '%d/%m/%Y') as publication_fr,
                        mangas_tomes.synopsis,
                        mangas_tomes.couverture_jp,
                        mangas_tomes.couverture_fr,
                        mangas.title,
                        (SELECT COUNT(tomes_collection.id)
                         FROM tomes_collection
                         WHERE tomes_collection.id_manga = mangas_tomes.id_manga
                         AND tomes_collection.number = mangas_tomes.number
                         AND tomes_collection.id_user = ".(int)$id_user.") as inCollection")
                ->join("mangas", "mangas.id_manga = mangas_tomes.id_manga")
                ->where('mangas_tomes.id_manga', $id);

        $query = $this->db->get("mangas_tomes");

        if ( $query->num_rows() > 0 )
            return $query->result();

        return FALSE;

    }

    public function get_manga_by_name($name = NULL) {

        $segments = explode(" ", $name);

        if ( !is_null($name) ) {

            $this->db->select("mangas.id_manga, mangas.title, mangas.year, tome_fr.couverture_fr as img_tome_fr, tome_jp.couverture_jp as img_tome_jp, MIN(tome_fr.number), MIN(tome_jp.number)")
                ->join("mangas_titles", "mangas_titles.id_manga = mangas.id_manga")
                ->join("mangas_tomes as tome_fr",
                    "tome_fr.id_manga = mangas.id_manga AND tome_fr.couverture_fr != '' AND tome_fr.couverture_fr IS NOT NULL", "left")
                ->join("mangas_tomes as tome_jp",
                    "tome_jp.id_manga = mangas.id_manga AND tome_jp.couverture_jp != '' AND tome_jp.couverture_jp IS NOT NULL", "left");

            foreach ($segments as $segment) {
                $this->db->where("(mangas.title LIKE '%$segment%'
                    OR mangas_titles.title LIKE '%$segment%')");
            }

            $this->db->group_by('mangas.id_manga')
                ->order_by("LENGTH(mangas.title)")
                ->limit(10);

            $query = $this->db->get($this->table);

            if ( $query->num_rows() > 0 )
                return $query->result();

        }

        return FALSE;
    }

    public function check_manga($id_manga){
        $this->db->select("id_manga")
            ->where("id_manga", $id_manga);

        $query = $this->db->get($this->table);
        if ( $query->num_rows() > 0 )
            return true;

        return json_output(403, array('status' => 403,'message' => 'Manga not found.'));
    }

    public function check_collection_manga($id_manga, $id_user){
        $this->db->select("id_manga")
            ->where("id_manga", $id_manga)
            ->where("id_user", $id_user);

        $query = $this->db->get("mangas_collection");
        if ( $query->num_rows() > 0 )
            return true;

        return false;
    }

    public function add_collection_manga($data)
    {
        $this->db->insert('mangas_collection',$data);
        return array('status' => 201,'message' => 'Data has been created.');
    }

    public function check_tome($id_manga, $number){
        $this->db->select("id_manga")
            ->where("id_manga", $id_manga)
            ->where("number", $number);

        $query = $this->db->get("mangas_tomes");
        if ( $query->num_rows() > 0 )
            return true;

        return json_output(403, array('status' => 403,'message' => 'Tome not found.'));
    }

    public function check_collection_tome($id_manga, $number, $id_user){
        $this->db->select("id_manga")
            ->where("id_manga", $id_manga)
            ->where("number", $number)
            ->where("id_user", $id_user);

        $query = $this->db->get("tomes_collection");
        if ( $query->num_rows() > 0 )
            return true;

        return false;
    }

    public function add_collection_tome($data)
    {
        $this->db->insert('tomes_collection',$data);
        return array('status' => 201,'message' => 'Data has been created.');
    }

    public function delete_collection_manga($data)
    {
        $this->db->delete('mangas_collection', $data);
        return array('status' => 201,'message' => 'Data has been deleted.');
    }

    public function delete_collection_tome($data)
    {
        $this->db->delete('tomes_collection', $data);
        return array('status' => 201,'message' => 'Data has been deleted.');
    }

    public function get_manga_collection($id_user) {

        $this->db->select("mangas.id_manga, mangas.title,
                         (SELECT mangas_tomes.couverture_fr
                         FROM mangas_tomes
                         WHERE mangas_tomes.id_manga = mangas.id_manga
                         AND couverture_fr IS NOT NULL
                         AND couverture_fr != ''
                         ORDER BY number
                         LIMIT 1) as img_tome_fr,
                        (SELECT mangas_tomes.couverture_jp
                         FROM mangas_tomes
                         WHERE mangas_tomes.id_manga = mangas.id_manga
                         AND couverture_jp IS NOT NULL
                         AND couverture_jp != ''
                         ORDER BY number
                         LIMIT 1) as img_tome_jp, mangas.nb_tomes,
                        (SELECT COUNT(*)
                         FROM tomes_collection
                         WHERE tomes_collection.id_manga = mangas.id_manga
                         AND tomes_collection.id_user = mangas_collection.id_user) as tomes_progression,
                         ROUND(((SELECT COUNT(*)
                         FROM tomes_collection
                         WHERE tomes_collection.id_manga = mangas.id_manga
                         AND tomes_collection.id_user = mangas_collection.id_user) / mangas.nb_tomes)*100) as progression")
            ->join("mangas_collection", "mangas_collection.id_manga = mangas.id_manga")
            ->where("mangas_collection.id_user", $id_user)
            ->order_by('mangas.title', 'ASC');

        $query = $this->db->get($this->table);
        if ( $query->num_rows() > 0 )
            return $query->result();

        return FALSE;
    }

    public function get_tome($id_manga, $number) {

        $this->db->select("mangas.title, mangas_tomes.number, mangas_tomes.couverture_fr, mangas_tomes.couverture_jp")
            ->join("mangas_tomes", "mangas_tomes.id_manga = mangas.id_manga AND mangas_tomes.number = ".$number)
            ->where('mangas.id_manga', $id_manga);

        $query = $this->db->get($this->table);

        if ( $query->num_rows() > 0 )
            return $query->row();

        return FALSE;

    }

    public function get_mangas_calendar() {

        $sql = "SET lc_time_names = 'fr_FR'";

        $this->db->query($sql);

        $this->db->select("mangas.id_manga, mangas.title as manga_title,
                        CASE WHEN mangas_tomes.couverture_fr IS NULL OR mangas_tomes.couverture_fr = '' THEN mangas_tomes.couverture_fr ELSE mangas_tomes.couverture_jp END as couverture,
                        mangas_tomes.number, mangas_tomes.title, DATE_FORMAT(mangas_tomes.publication_fr, '%d %M %Y') as publication_fr,
                        DATEDIFF(mangas_tomes.publication_fr, NOW()) as nb_days", false)
            ->join("mangas", "mangas.id_manga = mangas_tomes.id_manga")
            ->where("mangas_tomes.publication_fr >= CONCAT(YEAR(NOW()), '-', MONTH(NOW()), '-', DAY(NOW()))")
            ->order_by('mangas_tomes.publication_fr', 'ASC');


        $query = $this->db->get("mangas_tomes");

        $results = [];
        foreach($query->result_array() as $row){
            $results[$row['publication_fr']][] = [
                'id' => $row['id_manga'],
                'title_oeuvre' => $row['manga_title'],
                'img' => $row['couverture'],
                'number' => $row['number'],
                'title' => $row['title'],
                'nb_days' => $row['nb_days'],
                'type' => 'manga'
            ];
        }

        return $results;
    }

    public function get_suivi($id_user){
        $this->db->select("mangas.id_manga, mangas.title as manga_title,
                            CASE WHEN mangas_tomes.couverture_fr IS NULL OR mangas_tomes.couverture_fr = '' THEN mangas_tomes.couverture_jp else mangas_tomes.couverture_fr END as couverture, mangas_tomes.number,
                            mangas_tomes.title, DATE_FORMAT(mangas_tomes.publication_fr, '%d %M %Y') as publication", false)
            ->join("mangas", "mangas.id_manga = tomes_collection.id_manga")
            ->join("mangas_tomes", "tomes_collection.id_manga = mangas_tomes.id_manga
                AND mangas_tomes.publication_fr <= NOW()
                AND mangas_tomes.number = (SELECT MAX(ec.number) FROM tomes_collection ec WHERE ec.id_user = tomes_collection.id_user AND ec.id_manga = tomes_collection.id_manga)+1")
            ->where("tomes_collection.id_user", $id_user)
            ->group_by('tomes_collection.id_manga');


        $query = $this->db->get("tomes_collection");

        return $query->result();
    }

    public function get_watchlist($id_user){
        $this->db->select("mangas.id_manga, mangas.title, CASE WHEN mangas_tomes.couverture_fr IS NULL OR mangas_tomes.couverture_fr = '' THEN mangas_tomes.couverture_jp else mangas_tomes.couverture_fr END as couverture,
                        (SELECT COUNT(mangas_tomes.number) FROM mangas_tomes WHERE mangas_tomes.id_manga = mangas.id_manga AND mangas_tomes.publication_fr <= NOW()) as nb_tomes,
                        mangas_tomes.number", false)
            ->join("tomes_collection", "tomes_collection.id_manga = mangas.id_manga", "left")
            ->join("mangas_tomes", "mangas_tomes.id_manga = mangas.id_manga")
            ->join("mangas_collection", "mangas.id_manga = mangas_collection.id_manga")
            ->where("mangas_collection.id_user", $id_user)
            ->where("tomes_collection.id IS NULL", null, false)
            ->where("mangas_tomes.number", "1")
            ->where("mangas_tomes.publication_fr <= NOW()");


        $query = $this->db->get($this->table);

        return $query->result();
    }

    public function get_next_tome($id_manga, $number){
        $this->db->select("mangas.id_manga, mangas.title as manga_title, mangas_tomes.number, mangas_tomes.title,
                        CASE WHEN mangas_tomes.couverture_fr IS NULL OR mangas_tomes.couverture_fr = '' THEN mangas_tomes.couverture_jp else mangas_tomes.couverture_fr END as couverture", false)
            ->join("mangas", "mangas.id_manga = mangas_tomes.id_manga")
            ->where('mangas_tomes.id_manga', $id_manga)
            ->where('mangas_tomes.number', $number + 1)
            ->where('mangas_tomes.publication_fr <= NOW()', null, false);

        $query = $this->db->get("mangas_tomes");

        if (isset($query) && $query->num_rows() > 0)
            return $query->result();

        return false;
    }

}
