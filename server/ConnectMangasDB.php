<?php
class ConnectMangasDB {

    /**
     * @var mysqli
     */
    private $cnx;
    /**
     * @var array
     */
    private $publics = [];
    /**
     * @var array
     */
    private $themes = [];
    /**
     * @var array
     */
    private $genres = [];
    /**
     * @var array
     */
    private $studios = [];
    /**
     * @var array
     */
    private $editeurs = [];
    /**
     * @var string
     */
    private $medias = '/Applications/MAMP/htdocs/ConnectMangas/client/medias/';
    /**
     * @var string
     */
    private $regex_editeur_jp = '/Editeur original : <\/b>[^<]*<a href="[^"]*">([^<]*)<\/a>/';
    /**
     * @var string
     */
    private $regex_editeur_fr = '/Editeur français : <\/b>[^<]*<a href="[^"]*">([^<]*)<\/a>/';

    /**
     * ConnectMangasDB constructor.
     */
    public function __construct() {
        // Connexion to DB
        $this->cnx = mysqli_connect("127.0.0.1","root","root","connectmangas", 8889);

        if (!mysqli_set_charset($this->cnx, "utf8")) {
            printf("Erreur lors du chargement du jeu de caractères utf8 : %s\n", mysqli_error($this->cnx));
            exit();
        }

        // Set publics from DB
        $sql = "SELECT id, name
                FROM publics";

        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $this->publics[$row['name']] = $row['id'];
            }
        }else{
            die("Impossible de récupérer les publics de la base de données : ".mysqli_error($this->cnx)."\n");
        }

        // Set themes from DB
        $sql = "SELECT id, name
                FROM themes";

        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $this->themes[$row['name']] = $row['id'];
            }
        }else{
            die("Impossible de récupérer les thèmes de la base de données : ".mysqli_error($this->cnx)."\n");
        }

        // Set genres from DB
        $sql = "SELECT id, name
                FROM genres";

        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $this->genres[$row['name']] = $row['id'];
            }
        }else{
            die("Impossible de récupérer les genres de la base de données : ".mysqli_error($this->cnx)."\n");
        }

        // Set studios from DB
        $sql = "SELECT id, name
                FROM studios";

        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $this->studios[$row['name']] = $row['id'];
            }
        }else{
            die("Impossible de récupérer les studios de la base de données : ".mysqli_error($this->cnx)."\n");
        }

        // Set editeurs from DB
        $sql = "SELECT id_editeur, name
                FROM editeurs";

        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $this->editeurs[$row['name']] = $row['id_editeur'];
            }
        }else{
            die("Impossible de récupérer les éditeurs de la base de données : ".mysqli_error($this->cnx)."\n");
        }
    }

    /**
     * @param $content
     * @param $start
     * @param $end
     * @return string
     */
    public function getBetween($content,$start,$end){
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }

    /**
     * @return array
     */
    public function getIdAnimesListDb() {

        $sql = "SELECT id_anime FROM animes";

        $id_animes = [];
        if ($query = mysqli_query($this->cnx, $sql)){
            while ($row = mysqli_fetch_assoc($query)){
                $id_animes[] = $row['id_anime'];
            }
        }

        return $id_animes;
    }

    /**
     * @return int
     */
    public function nbPagesAnimesList() {

        $url = "http://anime.icotaku.com/recherche-avancee.html?titre=&categorie=tv&public=&annee=&origine=&studio=&saison=&mois=&commit=Rechercher&page=1";

        $content = file_get_contents($url);

        $regex_nb_pages = '/<a href="\/recherche-avancee\.html\?titre=&amp;categorie=tv&amp;public=&amp;annee=&amp;origine=&amp;studio=&amp;saison=&amp;mois=&amp;commit=Rechercher&amp;page=([0-9]*)">&raquo;/';

        if (preg_match_all($regex_nb_pages, $content, $matches, PREG_SET_ORDER)){
            $nb_pages = $matches[0][1];
        }else{
            $nb_pages = 1;
            trigger_error('Total de pages liste animés non trouvé', E_USER_WARNING);
        }

        return $nb_pages;
    }

    /**
     * @return array
     */
    public function getIdAnimesListWeb() {

        echo "### Récupère la liste des animes ###\n\n";

        $id_animes = [];

        $nb_pages = $this->nbPagesAnimesList();

        $i = 0;
        for ($page = 1; $page <= $nb_pages; $page++) {

            $url = "http://anime.icotaku.com/recherche-avancee.html?titre=&categorie=tv&public=&annee=&origine=&studio=&saison=&mois=&commit=Rechercher&page=".$page;

            $content = file_get_contents($url);

            $regex_anime = '/<tr class="">[^<]*<td>[^<]*<div class="td_apercufiche">[^<]*<a href="\/anime\/(?\'id\'[^\/]*)\/[^"]*">/';

            if (!preg_match_all($regex_anime, $content, $matches, PREG_SET_ORDER)) {
                trigger_error('Erreur regex liste animes', E_USER_WARNING);
            }

            foreach ($matches as $key => $anime) {
                $id_animes[] = $anime[1];
                $i++;
                echo "$i Anime(s) trouvé(s).\r";
            }
        }
        echo "$i Anime(s) trouvé(s).\n";

        return $id_animes;
    }

    /**
     * @return array
     */
    public function getNewIdAnimes() {

        $id_animes_db = $this->getIdAnimesListDb();
        $id_animes_web = $this->getIdAnimesListWeb();

        $id_new_animes = array_diff($id_animes_web, $id_animes_db);

        return $id_new_animes;
    }

    /**
     * @param $content
     * @return string
     */
    public function updateAnimeAffiche($content){

        $regex_url_affiche = '/src="\/images\/..\/([^"]*)"/';

        $url_affiche = '';
        if (preg_match_all($regex_url_affiche, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $affiche) {
                $url_affiche = "http://anime.icotaku.com/".trim($affiche[1]);
            }

            $file_name = uniqid().".jpg";

            if (copy($url_affiche, $this->medias.'animes/'.$file_name)){
                return $file_name;
            }
        }
    }

    /**
     * @param $content
     * @param $type
     * @return string
     */
    public function updateBanniere($content, $type){

        $regex_url_banniere = '/style="background:url\(\/images\/..\/([^)]*)\)/';

        $url_banniere = '';
        if (preg_match_all($regex_url_banniere, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $banniere) {
                if ($type == "anime") {
                    $url_banniere = "http://anime.icotaku.com/" . trim($banniere[1]);
                }else if ($type == "manga"){
                    $url_banniere = "http://manga.icotaku.com/" . trim($banniere[1]);
                }
            }
        }

        $file_name = uniqid().".jpg";

        if (!filter_var($url_banniere, FILTER_VALIDATE_URL) === false) {
            $headers = get_headers($url_banniere);

            if(substr($headers[0], 9, 3) == "200"){
                if ($type == "anime") {
                    if (!empty($url_banniere) && copy($url_banniere, $this->medias . 'animes/' . $file_name)) {
                        return $file_name;
                    }
                }else if ($type == "manga"){
                    if (!empty($url_banniere) && copy($url_banniere, $this->medias . 'mangas/' . $file_name)) {
                        return $file_name;
                    }
                }
            }
        }

        return '';
    }


    public function updateMangaBanniere($content){

        $regex_url_banniere = "/style='background:url\(\/images\/..\/([^)]*)\)/";

        $url_banniere = '';
        if (preg_match_all($regex_url_banniere, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $banniere) {
                $url_banniere = "http://manga.icotaku.com/".trim($banniere[1]);
            }
        }

        $file_name = uniqid().".jpg";

        if (!filter_var($url_banniere, FILTER_VALIDATE_URL) === false) {
            $headers = get_headers($url_banniere);

            if(substr($headers[0], 9, 3) == "200"){
                if (!empty($url_banniere) && copy($url_banniere, $this->medias.'mangas/'.$file_name)){
                    return $file_name;
                }
            }
        }
    }


    public function updateTomesImages(){

        $sql = "SET SESSION group_concat_max_len = 1000000";

        mysqli_query($this->cnx, $sql);

        $sql = "SELECT t.id_manga, 
                        (SELECT GROUP_CONCAT(t2.number)
                         FROM mangas_tomes t2
                         WHERE t2.id_manga = t.id_manga
                         AND (t2.couverture_jp IS NULL
                         OR t2.couverture_jp = '')) as numbers_couverture_jp,
                         (SELECT GROUP_CONCAT(t3.number)
                         FROM mangas_tomes t3
                         WHERE t3.id_manga = t.id_manga
                         AND (t3.couverture_fr IS NULL
                         OR t3.couverture_fr = '')) as numbers_couverture_fr
                FROM mangas_tomes t
                WHERE (t.couverture_jp IS NULL
                OR t.couverture_jp = ''
                OR t.couverture_fr IS NULL
                OR t.couverture_fr = '')
                GROUP BY t.id_manga";

        if ($query = mysqli_query($this->cnx, $sql)){
            while ($row = mysqli_fetch_assoc($query)){

                $numbers_couverture_jp = explode(",", $row['numbers_couverture_jp']);
                $numbers_couverture_fr = explode(",", $row['numbers_couverture_fr']);

                $regex_tomes = '/<h2>Tome (?\'number\'[0-9]*)(?: : (?\'title\'[^<]*))*<\/h2>[^<]*<div [^>]*>[^<]*(?:<img [^>]*>[^<]*(?:<a title="[^"]*" rel="[^"]*" href="(?\'image_fr2\'[^"]*)"><img [^>]*><\/a>[^<]*){0,1})*(?:<a title="[^"]*" rel="[^"]*" href="(?\'image_jp\'[^"]*)"><img [^>]*><\/a>[^<]*){0,1}(?:<a title="[^"]*" rel="[^"]*" href="(?\'image_fr\'[^"]*)"><img [^>]*><\/a>[^<]*){0,1}(?:(?!Date de la)[\D\d])*Date de la première publication : <\/b><\/p>[^<]*<div><img src="[^"]*" alt="Jp" \/>(?\'date_jp\'[^<]*)<\/div>[^<]*(?:<br\/>[^<]*<div><img src="[^"]*" alt="Fr" \/>(?\'date_fr\'[^<]*)<\/div>[^<]*<div class="clear"><\/div>[^<]*<(?:a [^<]*<\/a> [^<]*<p [^>]*>(?\'synopsis\'[^<]*(?:<br \/>[^<]*)*)<\/p>)*)*/';

                $url = "http://manga.icotaku.com/fiche/tome/manga/".$row['id_manga'];

                $content = file_get_contents($url);

                if (preg_match_all($regex_tomes, $content, $matches, PREG_SET_ORDER)) {

                    foreach ($matches as $key => $tome) {

                        if (isset($tome['image_fr2']) && !empty($tome['image_fr2'])){
                            $url_couverture_jp = "";
                            $url_couverture_fr = $tome['image_fr2'];
                        }else{
                            if (isset($tome['image_jp']) && !empty($tome['image_jp'])){
                                $url_couverture_jp = $tome['image_jp'];
                            }else{
                                $url_couverture_jp = "";
                            }
                            if (isset($tome['image_fr']) && !empty($tome['image_fr'])){
                                $url_couverture_fr = $tome['image_fr'];
                            }else{
                                $url_couverture_fr = "";
                            }
                        }

                        if (!empty($url_couverture_jp) && in_array($tome['number'], $numbers_couverture_jp)){

                            $file_name = uniqid().".jpg";

                            if (copy($url_couverture_jp, $this->medias.'tomes/'.$file_name)){
                                $sql_update = sprintf("UPDATE mangas_tomes
                                                SET couverture_jp = '%s'
                                                WHERE id_manga = '%s'
                                                AND number = '%s'",
                                                mysqli_real_escape_string($this->cnx, $file_name),
                                                mysqli_real_escape_string($this->cnx, $row['id_manga']),
                                                mysqli_real_escape_string($this->cnx, $tome['number'])
                                                );

                                mysqli_query($this->cnx, $sql_update);
                            }

                        }

                        if (!empty($url_couverture_fr) && in_array($tome['number'], $numbers_couverture_fr)){

                            $file_name = uniqid().".jpg";

                            if (copy($url_couverture_fr, $this->medias.'tomes/'.$file_name)){
                                $sql_update = sprintf("UPDATE mangas_tomes
                                                SET couverture_fr = '%s'
                                                WHERE id_manga = '%s'
                                                AND number = '%s'",
                                                mysqli_real_escape_string($this->cnx, $file_name),
                                                mysqli_real_escape_string($this->cnx, $row['id_manga']),
                                                mysqli_real_escape_string($this->cnx, $tome['number'])
                                                );

                                mysqli_query($this->cnx, $sql_update);
                            }

                        }

                    }
                }
            }
        }
    }

    /**
     * @param $content
     * @return string
     */
    public function getMainTitle($content){

        $regex_titre_principal = '/<h1>([^<]*)<\/h1>/';

        $title = '';
        if (preg_match_all($regex_titre_principal, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $titre) {
                $title = trim(html_entity_decode($titre[1], ENT_QUOTES | ENT_HTML5));
            }
        }

        return $title;
    }

    /**
     * @param $content
     * @return array
     */
    public function getAlterTitles($content){

        $regex_titre_original = '/Titre original : <\/b>([^<]*)<\/div>/';
        $regex_titre_alter = '/Titre alternatif : <\/b>([^<]*)<\/div>/';
        $regex_titre_francais = '/Titre français : <\/b>([^<]*)<\/div>/';

        $titles = [];

        if (preg_match_all($regex_titre_original, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $titre) {
                $titles[] = ['title' => trim(html_entity_decode($titre[1], ENT_QUOTES | ENT_HTML5)), 'lang' => 'JA'];
            }
        }

        if (preg_match_all($regex_titre_alter, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $titre) {
                $titles[] = ['title' => trim(html_entity_decode($titre[1], ENT_QUOTES | ENT_HTML5)), 'lang' => ''];
            }
        }

        if (preg_match_all($regex_titre_francais, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $titre) {
                $titles[] = ['title' => trim(html_entity_decode($titre[1], ENT_QUOTES | ENT_HTML5)), 'lang' => 'FR'];
            }
        }

        return $titles;
    }

    /**
     * @param $content
     * @return array
     */
    public function getGenres($content){

        $regex_genres = '/<a href="\/genre\/[^"]*">([^<]*)<\/a>/';

        $genres_names = [];

        if (preg_match_all($regex_genres, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $genre) {
                $genres_names[] = trim(html_entity_decode($genre[1]));
            }
        }

        $genres_id = [];

        foreach ($genres_names as $genre_name){
            if (isset($this->genres[$genre_name])) {
                $genres_id[] = $this->genres[$genre_name];
            }else if (!empty($genre_name) && $genre_name != "?"){
                // Si le genre n'est pas déjà dans la BDD alors on l'insert
                $insert = sprintf("INSERT INTO genres (name) VALUES ('%s')",
                    mysqli_real_escape_string($this->cnx, $genre_name)
                );

                if (mysqli_query($this->cnx, $insert)){
                    $genres_id[] = mysqli_insert_id($this->cnx);
                    $this->genres[$genre_name] = mysqli_insert_id($this->cnx);
                }else{
                    die("Erreur lors de l'enregistrement d'un nouveau genre : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
                }
            }else{
                $genres_id = 0;
            }
        }

        return $genres_id;
    }

    /**
     * @param $content
     * @return array
     */
    public function getThemes($content){

        $regex_themes = '/<a href="\/theme\/[^"]*">([^<]*)<\/a>/';

        $themes_names = [];

        if (preg_match_all($regex_themes, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $theme) {
                $themes_names[] = trim(html_entity_decode($theme[1]));
            }
        }

        $themes_id = [];

        foreach ($themes_names as $theme_name){
            if (isset($this->themes[$theme_name])) {
                $themes_id[] = $this->themes[$theme_name];
            }else if (!empty($theme_name) && $theme_name != '?'){
                // Si le thème n'est pas déjà dans la BDD alors on l'insert
                $insert = sprintf("INSERT INTO themes (name) VALUES ('%s')",
                    mysqli_real_escape_string($this->cnx, $theme_name)
                );

                if (mysqli_query($this->cnx, $insert)){
                    $themes_id[] = mysqli_insert_id($this->cnx);
                    $this->themes[$theme_name] = mysqli_insert_id($this->cnx);
                }else{
                    die("Erreur lors de l'enregistrement d'un nouveau thème : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
                }
            }else{
                $themes_id = 0;
            }
        }

        return $themes_id;
    }

    /**
     * @param $content
     * @return int
     */
    public function getPublic($content){

        $regex_type = '/Public visé : <\/b>([^< ]*)[^<]*<\/div>/';

        $public_name = '';

        if (preg_match_all($regex_type, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $type) {
                $public_name = trim(html_entity_decode($type[1]));
            }
        }

        if (isset($this->publics[$public_name])) {
            $public_id = $this->publics[$public_name];
        }else if (!empty($public_name) && $public_name != '?'){
            // Si le public n'est pas déjà dans la BDD alors on l'insert
            $insert = sprintf("INSERT INTO publics (name) VALUES ('%s')",
                mysqli_real_escape_string($this->cnx, $public_name)
            );

            if (mysqli_query($this->cnx, $insert)){
                $public_id = mysqli_insert_id($this->cnx);
                $this->publics[$public_name] = mysqli_insert_id($this->cnx);
            }else{
                die("Erreur lors de l'enregistrement d'un nouveau public : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
            }
        }else{
            $public_id = 0;
        }

        return $public_id;
    }

    /**
     * @param $content
     * @return int
     */
    public function getStudio($content){

        $regex_studio = '/Studio\(s\) d\'animation : <\/b>[^<]*<a href="[^"]*">([^<]*)<\/a>/';

        $studio_name = '';

        if (preg_match_all($regex_studio, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $studio_animation) {
                $studio_name = trim(html_entity_decode($studio_animation[1]));
            }
        }

        if (isset($this->studios[$studio_name])) {
            $studio_id = $this->studios[$studio_name];
        }else if (!empty($studio_name) && $studio_name != '?'){
            // Si le studio n'est pas déjà dans la BDD alors on l'insert
            $insert = sprintf("INSERT INTO studios (name) VALUES ('%s')",
                mysqli_real_escape_string($this->cnx, $studio_name)
            );

            if (mysqli_query($this->cnx, $insert)){
                $studio_id = mysqli_insert_id($this->cnx);
                $this->studios[$studio_name] = mysqli_insert_id($this->cnx);
            }else{
                die("Erreur lors de l'enregistrement d'un nouveau studio : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
            }
        }else{
            $studio_id = 0;
        }

        return $studio_id;
    }

    /**
     * @param $content
     * @return int
     */
    public function getEditeur($content, $regex){

        $editeur_name = '';
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $editeur) {
                $editeur_name = trim(html_entity_decode($editeur[1]));
            }
        }

        if (isset($this->editeurs[$editeur_name])) {
            $editeur_id = $this->editeurs[$editeur_name];
        }else if (!empty($editeur_name) && $editeur_name != '?'){
            // Si l'éditeur n'est pas déjà dans la BDD alors on l'insert
            $insert = sprintf("INSERT INTO editeurs (name) VALUES ('%s')",
                mysqli_real_escape_string($this->cnx, $editeur_name)
            );

            if (mysqli_query($this->cnx, $insert)){
                $editeur_id = mysqli_insert_id($this->cnx);
                $this->editeurs[$editeur_name] = mysqli_insert_id($this->cnx);
            }else{
                die("Erreur lors de l'enregistrement d'un nouveau éditeur : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
            }
        }else{
            $editeur_id = 0;
        }

        return $editeur_id;
    }

    /**
     * @param $content
     * @return int
     */
    public function getNbEpisodes($content){

        $regex_nb_ep = '/Nombre d\'épisode : <\/b>([^< ]*)[^<]*<\/div>/';

        $nb_episodes = '';
        if (preg_match_all($regex_nb_ep, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $value) {
                $nb_episodes = trim($value[1]);
            }
        }

        return $nb_episodes;
    }

    /**
     * @param $content
     * @return int
     */
    public function getNbTomes($content){

        $regex_nb_tomes = '/Nombre de tomes : <\/b>([^<]*)<\/div>/';

        $nb_tomes = '';
        if (preg_match_all($regex_nb_tomes, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $value) {
                $nb_tomes = trim($value[1]);
            }
        }

        return $nb_tomes;
    }

    /**
     * @param $content
     * @return int
     */
    public function getRuntime($content){

        $regex_duree = '/Durée d\'un épisode : <\/b>([^ ]*)[^<]*<\/div>/';

        $runtime = '';

        if (preg_match_all($regex_duree, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $duree) {
                $runtime = trim($duree[1]);
            }
        }

        return $runtime;
    }

    /**
     * @param $content
     * @return string
     */
    public function getSeason($content){

        $regex_saison = '/Saison : <\/b>([^< ]*)[^<]*<\/div>/';

        $season = '';
        if (preg_match_all($regex_saison, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $saison) {
                $season = trim($saison[1]);
            }
        }

        return $season;
    }

    /**
     * @param $content
     * @return string
     */
    public function getMonth($content){

        $regex_mois = '/Mois de début de diffusion : <\/b>([^< ]*)[^<]*<\/div>/';

        $month = '';
        if (preg_match_all($regex_mois, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $mois_diffusion) {
                $month = trim($mois_diffusion[1]);
            }
        }

        return $month;
    }

    /**
     * @param $content
     * @return int
     */
    public function getYear($content){

        $regex_annee = '/Année de production : <\/b>([^< ]*)[^<]*<\/div>/';

        $year = '';
        if (preg_match_all($regex_annee, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $annee) {
                $year = trim($annee[1]);
            }
        }

        return $year;
    }

    /**
     * @param $content
     * @return string
     */
    public function getDiffusion($content){

        $regex_diffusion = '/Diffusion : <\/b>([^<]*)<\/div>/';

        $status = '';
        if (preg_match_all($regex_diffusion, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $diffusion) {
                $status = trim($diffusion[1]);
            }
        }

        return $status;
    }

    /**
     * @param $content
     * @return string
     */
    public function getPublicationJp($content){

        $regex_publication_jp = '/Publication : <\/b>([^<]*)<\/div> : <\/b>([^<]*)<\/div>/';

        $publication_jp = '';
        if (preg_match_all($regex_publication_jp, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $publication) {
                $publication_jp = trim($publication[1]);
            }
        }

        return $publication_jp;
    }

    /**
     * @param $content
     * @return string
     */
    public function getPublicationFr($content){

        $regex_publication_fr = '/Publication française : <\/b>([^<]*)<\/div>/';

        $publication_fr = '';
        if (preg_match_all($regex_publication_fr, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $publication) {
                $publication_fr = trim($publication[1]);
            }
        }

        return $publication_fr;
    }

    /**
     * @param $content
     * @return string
     */
    public function getSynopsis($content){

        $regex_synopsis = '/<h2>Histoire<\/h2>[^<]*<p align=\'justify\'>((?:(?!<\/p>)[\D\d])*)<\/p>/';

        $synopsis = '';
        if (preg_match_all($regex_synopsis, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $histoire) {
                $synopsis = trim(html_entity_decode($histoire[1], ENT_QUOTES | ENT_HTML5));
            }
        }

        return $synopsis;
    }

    /**
     * @param $anime
     */
    public function insertNewAnime($anime){

        $insert = sprintf(
            "INSERT INTO animes (id_anime, title, id_public, nb_episodes, duration, season, month, year, diffusion, id_studio, synopsis, img_affiche, img_banniere)
              VALUES
               ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
               ON DUPLICATE KEY UPDATE title = VALUES(title), id_public = VALUES(id_public), nb_episodes = VALUES(nb_episodes),
               duration = VALUES(duration), season = VALUES(season), month = VALUES(month),
               year = VALUES(year), diffusion = VALUES(diffusion), id_studio = VALUES(id_studio),
               synopsis = VALUES(synopsis), img_affiche = VALUES(img_affiche), img_banniere = VALUES(img_banniere)",
            mysqli_real_escape_string($this->cnx, $anime['id_anime']),
            mysqli_real_escape_string($this->cnx, $anime['title']),
            mysqli_real_escape_string($this->cnx, $anime['public']),
            mysqli_real_escape_string($this->cnx, $anime['nb_episodes']),
            mysqli_real_escape_string($this->cnx, $anime['runtime']),
            mysqli_real_escape_string($this->cnx, $anime['season']),
            mysqli_real_escape_string($this->cnx, $anime['month']),
            mysqli_real_escape_string($this->cnx, $anime['year']),
            mysqli_real_escape_string($this->cnx, $anime['status']),
            mysqli_real_escape_string($this->cnx, $anime['studio']),
            mysqli_real_escape_string($this->cnx, $anime['synopsis']),
            mysqli_real_escape_string($this->cnx, $anime['img_affiche']),
            mysqli_real_escape_string($this->cnx, $anime['img_banniere'])
        );

        if (!mysqli_query($this->cnx, $insert)){
            die("Erreur lors de l'enregistrement d'un nouvel anime (".$anime['id_anime'].") : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
        }

        if ($anime['genres']) {
            $insert_genres = "INSERT INTO animes_genres (id_anime, id_genre)
                          VALUES (" . $anime['id_anime'] . ", " . implode("), (" . $anime['id_anime'] . ", ", $anime['genres']) . ")
                          ON DUPLICATE KEY UPDATE id_anime = id_anime";

            if (!mysqli_query($this->cnx, $insert_genres)) {
                die("Erreur lors de l'enregistrement des genres de l'anime (" . $anime['id_anime'] . ") : " . mysqli_error($this->cnx) . "\n\n" . "Requête SQL : " . $insert_genres . "\n");
            }
        }

        if ($anime['themes']) {
            $insert_themes = "INSERT INTO animes_themes (id_anime, id_theme)
                          VALUES (" . $anime['id_anime'] . ", " . implode("), (" . $anime['id_anime'] . ", ", $anime['themes']) . ")
                          ON DUPLICATE KEY UPDATE id_anime = id_anime";

            if (!mysqli_query($this->cnx, $insert_themes)) {
                die("Erreur lors de l'enregistrement des thèmes de l'anime (" . $anime['id_anime'] . ") : " . mysqli_error($this->cnx) . "\n\n" . "Requête SQL : " . $insert_themes . "\n");
            }
        }

        if ($anime['alternative_title']) {
            $values_titles = [];
            foreach ($anime['alternative_title'] as $title) {
                $values_titles[] = sprintf("(" . $anime['id_anime'] . ", '%s', '%s')",
                    mysqli_real_escape_string($this->cnx, $title['title']),
                    mysqli_real_escape_string($this->cnx, $title['lang'])
                );
            }

            $insert_titles = "INSERT INTO animes_titles (id_anime, title, lang)
                          VALUES " . implode(",", $values_titles)."
                          ON DUPLICATE KEY UPDATE id_anime = id_anime";

            if (!mysqli_query($this->cnx, $insert_titles)) {
                die("Erreur lors de l'enregistrement des titles de l'anime (" . $anime['id_anime'] . ") : " . mysqli_error($this->cnx) . "\n\n" . "Requête SQL : " . $insert_titles . "\n");
            }
        }

        if ($anime['link']['prequels']) {
            $insert_prequels = "INSERT INTO animes_links (id_anime, type, id_link)
                            VALUES (" . $anime['id_anime'] . ", 'PREQUELLE', " . implode("), (" . $anime['id_anime'] . ", 'PREQUELLE', ", $anime['link']['prequels']) . ")
                            ON DUPLICATE KEY UPDATE id_anime = id_anime";

            if (!mysqli_query($this->cnx, $insert_prequels)) {
                die("Erreur lors de l'enregistrement des préquelles de l'anime (".$anime['id_anime'].") : " . mysqli_error($this->cnx) . "\n\n"."Requête SQL : ".$insert_prequels."\n");
            }
        }

        if ($anime['link']['suites']) {
            $insert_suites = "INSERT INTO animes_links (id_anime, type, id_link)
                            VALUES (" . $anime['id_anime'] . ", 'SUITE', " . implode("), (" . $anime['id_anime'] . ", 'SUITE', ", $anime['link']['suites']) . ")
                            ON DUPLICATE KEY UPDATE id_anime = id_anime";

            if (!mysqli_query($this->cnx, $insert_suites)) {
                die("Erreur lors de l'enregistrement des suites de l'anime (".$anime['id_anime'].") : " . mysqli_error($this->cnx) . "\n\n"."Requête SQL : ".$insert_suites."\n");
            }
        }

        if ($anime['link']['mangas']) {
            $insert_mangas = "INSERT INTO animes_links (id_anime, type, id_link)
                            VALUES (" . $anime['id_anime'] . ", 'MANGA', " . implode("), (" . $anime['id_anime'] . ", 'MANGA', ", $anime['link']['mangas']) . ")
                            ON DUPLICATE KEY UPDATE id_anime = id_anime";

            if (!mysqli_query($this->cnx, $insert_mangas)) {
                die("Erreur lors de l'enregistrement des mangas liés de l'anime (".$anime['id_anime'].") : " . mysqli_error($this->cnx) . "\n\n"."Requête SQL : ".$insert_mangas."\n");
            }
        }

    }

    public function insertNewManga($manga){

        $insert = sprintf(
            "INSERT INTO mangas (id_manga, title, id_public, nb_tomes, year, publication_jp, publication_fr, id_editeur_jp, id_editeur_fr, synopsis, img_banniere)
              VALUES
               ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
               ON DUPLICATE KEY UPDATE title = VALUES(title), id_public = VALUES(id_public), nb_tomes = VALUES(nb_tomes),
               year = VALUES(year), publication_jp = VALUES(publication_jp), publication_fr = VALUES(publication_fr),
               id_editeur_jp = VALUES(id_editeur_jp), id_editeur_fr = VALUES(id_editeur_fr), synopsis = VALUES(synopsis),
               img_banniere = VALUES(img_banniere)",
            mysqli_real_escape_string($this->cnx, $manga['id_manga']),
            mysqli_real_escape_string($this->cnx, $manga['title']),
            mysqli_real_escape_string($this->cnx, $manga['public']),
            mysqli_real_escape_string($this->cnx, $manga['nb_tomes']),
            mysqli_real_escape_string($this->cnx, $manga['year']),
            mysqli_real_escape_string($this->cnx, $manga['publication_jp']),
            mysqli_real_escape_string($this->cnx, $manga['publication_fr']),
            mysqli_real_escape_string($this->cnx, $manga['id_editeur_jp']),
            mysqli_real_escape_string($this->cnx, $manga['id_editeur_fr']),
            mysqli_real_escape_string($this->cnx, $manga['synopsis']),
            mysqli_real_escape_string($this->cnx, $manga['img_banniere'])
        );

        if (!mysqli_query($this->cnx, $insert)){
            die("Erreur lors de l'enregistrement d'un nouveau manga (".$manga['id_manga'].") : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
        }

        if ($manga['genres']) {
            $insert_genres = "INSERT INTO mangas_genres (id_manga, id_genre)
                          VALUES (" . $manga['id_manga'] . ", " . implode("), (" . $manga['id_manga'] . ", ", $manga['genres']) . ")
                          ON DUPLICATE KEY UPDATE id_manga = id_manga";

            if (!mysqli_query($this->cnx, $insert_genres)) {
                die("Erreur lors de l'enregistrement des genres du manga (" . $manga['id_manga'] . ") : " . mysqli_error($this->cnx) . "\n\n" . "Requête SQL : " . $insert_genres . "\n");
            }
        }

        if ($manga['themes']) {
            $insert_themes = "INSERT INTO mangas_themes (id_manga, id_theme)
                          VALUES (" . $manga['id_manga'] . ", " . implode("), (" . $manga['id_manga'] . ", ", $manga['themes']) . ")
                          ON DUPLICATE KEY UPDATE id_manga = id_manga";

            if (!mysqli_query($this->cnx, $insert_themes)) {
                die("Erreur lors de l'enregistrement des thèmes du manga (" . $manga['id_manga'] . ") : " . mysqli_error($this->cnx) . "\n\n" . "Requête SQL : " . $insert_themes . "\n");
            }
        }

        if ($manga['alternative_title']) {
            $values_titles = [];
            foreach ($manga['alternative_title'] as $title) {
                $values_titles[] = sprintf("(" . $manga['id_manga'] . ", '%s', '%s')",
                    mysqli_real_escape_string($this->cnx, $title['title']),
                    mysqli_real_escape_string($this->cnx, $title['lang'])
                );
            }

            $insert_titles = "INSERT INTO mangas_titles (id_manga, title, lang)
                          VALUES " . implode(",", $values_titles)."
                          ON DUPLICATE KEY UPDATE id_manga = id_manga";

            if (!mysqli_query($this->cnx, $insert_titles)) {
                die("Erreur lors de l'enregistrement des titles du manga (" . $manga['id_manga'] . ") : " . mysqli_error($this->cnx) . "\n\n" . "Requête SQL : " . $insert_titles . "\n");
            }
        }

        if ($manga['link']['prequels']) {
            $insert_prequels = "INSERT INTO mangas_links (id_manga, type, id_link)
                            VALUES (" . $manga['id_manga'] . ", 'PREQUELLE', " . implode("), (" . $manga['id_manga'] . ", 'PREQUELLE', ", $manga['link']['prequels']) . ")
                            ON DUPLICATE KEY UPDATE id_manga = id_manga";

            if (!mysqli_query($this->cnx, $insert_prequels)) {
                die("Erreur lors de l'enregistrement des préquelles du manga (".$manga['id_manga'].") : " . mysqli_error($this->cnx) . "\n\n"."Requête SQL : ".$insert_prequels."\n");
            }
        }

        if ($manga['link']['suites']) {
            $insert_suites = "INSERT INTO mangas_links (id_manga, type, id_link)
                            VALUES (" . $manga['id_manga'] . ", 'SUITE', " . implode("), (" . $manga['id_manga'] . ", 'SUITE', ", $manga['link']['suites']) . ")
                            ON DUPLICATE KEY UPDATE id_manga = id_manga";

            if (!mysqli_query($this->cnx, $insert_suites)) {
                die("Erreur lors de l'enregistrement des suites du manga (".$manga['id_manga'].") : " . mysqli_error($this->cnx) . "\n\n"."Requête SQL : ".$insert_suites."\n");
            }
        }

        if ($manga['link']['animes']) {
            $insert_mangas = "INSERT INTO mangas_links (id_manga, type, id_link)
                            VALUES (" . $manga['id_manga'] . ", 'ANIME', " . implode("), (" . $manga['id_manga'] . ", 'MANGA', ", $manga['link']['animes']) . ")
                            ON DUPLICATE KEY UPDATE id_manga = id_manga";

            if (!mysqli_query($this->cnx, $insert_mangas)) {
                die("Erreur lors de l'enregistrement des animes liés du manga (".$manga['id_manga'].") : " . mysqli_error($this->cnx) . "\n\n"."Requête SQL : ".$insert_mangas."\n");
            }
        }

    }

    /**
     * @param $id_anime
     */
    public function getAnimeInfos($id_anime, $update = false){

        $regex_id_anime = "/'\/popup\/popupAnimeInfo\/anime\/([0-9]*)'/";
        $regex_id_manga = "/'\/popup\/popupMangaInfo\/manga\/([0-9]*)'/";

        $url = "http://anime.icotaku.com/anime/" . $id_anime . "/test.html";

        $content = file_get_contents($url);

        $genres = $this->getGenres($content);
        $themes = $this->getThemes($content);

        if ($update && isset($update['title']) && !empty($update['title'])){
            $title = $update['title'];
        }else {
            $title = $this->getMainTitle($content);
        }
        $titles = $this->getAlterTitles($content);

        $public = $this->getPublic($content);

        $nb_episodes = $this->getNbEpisodes($content);
        $runtime = $this->getRuntime($content);

        $season = $this->getSeason($content);
        $month = $this->getMonth($content);
        $year = $this->getYear($content);

        $status = $this->getDiffusion($content);

        $studio = $this->getStudio($content);

        $synopsis = $this->getSynopsis($content);

        if ($update && isset($update['img_affiche']) && !empty($update['img_affiche'])){
            $img_affiche = $update['img_affiche'];
        }else {
            $img_affiche = $this->updateAnimeAffiche($content);
        }

        if ($update && isset($update['img_banniere']) && !empty($update['img_banniere'])){
            $img_banniere = $update['img_banniere'];
        }else {
            $img_banniere = $this->updateBanniere($content, 'anime');
        }

        $url = "http://anime.icotaku.com/anime/liens/" . $id_anime . ".html";

        $content = file_get_contents($url);

        /**
         * Préquelles
         */

        $prequelles = [];

        $html_prequelles = $this->getBetween($content, '<h2 style="clear:both;">Préquelles', '<h2 style="clear:both;">Suites');

        if (preg_match_all($regex_id_anime, $html_prequelles, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $id_anime_prequelle) {
                $prequelles[] = $id_anime_prequelle[1];
            }
        }

        /**
         * Suites
         */

        $suites = [];

        $html_suites = $this->getBetween($content, '<h2 style="clear:both;">Suites', '<h2 style="clear:both;">Spin-off (histoires alternatives)');

        if (preg_match_all($regex_id_anime, $html_suites, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $id_anime_suite) {
                $suites[] = $id_anime_suite[1];
            }
        }

        /**
         * Manga
         */

        $mangas = [];

        if (preg_match_all($regex_id_manga, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $id_manga) {
                $mangas[] = $id_manga[1];
            }
        }

        $anime = [
            'id_anime' => $id_anime,
            'title' => $title,
            'public' => $public,
            'nb_episodes' => $nb_episodes,
            'runtime' => $runtime,
            'season' => $season,
            'month' => $month,
            'year' => $year,
            'status' => $status,
            'studio' => $studio,
            'synopsis' => $synopsis,
            'genres' => $genres,
            'themes' => $themes,
            'alternative_title' => $titles,
            'img_affiche' => $img_affiche,
            'img_banniere' => $img_banniere,
            'link' => [
                'prequels' => $prequelles,
                'suites' => $suites,
                'mangas' => $mangas
            ]
        ];

        $this->insertNewAnime($anime);
    }

    public function getAnimesListWeb() {

        $id_animes = $this->getNewIdAnimes();

        echo "### Recherche des informations des animes ###\n\n";

        $total_animes = count($id_animes);
        $current_anime = 0;
        foreach ($id_animes as $id_anime) {

            // Récupère les infos puis enregistre toutes les infos de l'animé à part les épisodes
            $this->getAnimeInfos($id_anime);
            // Récupère les infos sur les épisodes de l'anime puis les enregistre
            $this->insertAnimeEpisodesWeb($id_anime);

            $current_anime++;
            echo "$current_anime/$total_animes anime(s) traité(s).\r";
        }
        echo "$current_anime/$total_animes anime(s) traité(s).\n";
    }

    /**
     * @param $id_anime
     * @return int
     */
    public function nbPagesAnimeEpisodes($id_anime) {

        $url = "http://anime.icotaku.com/fiche/episode/anime/".$id_anime."/page/1";

        $content = file_get_contents($url);

        $regex_nb_pages = '/<a href="\/fiche\/episode\/anime\/'.$id_anime.'\/page\/([0-9]*)">&raquo;/';

        if (preg_match_all($regex_nb_pages, $content, $matches, PREG_SET_ORDER)){
            $nb_pages = $matches[0][1];
        }else{
            $nb_pages = 1;
        }

        return $nb_pages;
    }

    /**
     * @param $id_anime
     */
    public function insertAnimeEpisodesWeb($id_anime) {

        $nb_pages = $this->nbPagesAnimeEpisodes($id_anime);

        $regex_episodes = '/<div class="(?\'hs\'[^"]*)">[^<]*<h2>[^ ]* (?\'number\'[0-9]*) : (?\'title\'(?:(?!<\/h2>)[\D\d])*)<\/h2>[^<]*<div class="screenshot">(?:[^<]*<div class="lien_video">[^<]*<a [^>]*><img [^>]*><\/a><div [^>]*><\/div>[^<]*<\/div>)*(?:[^<]*<a title="Aperçu - Épisode [0-9]*" rel="lightbox\[lightbox\]" href="(?\'image1\'[^"]*)"><img [^>]*><\/a>){0,1}(?:[^<]*<a title="Aperçu - Épisode [0-9]*" rel="lightbox\[lightbox\]" href="(?\'image2\'[^"]*)"><img [^>]*><\/a>){0,1}(?:[^<]*<a title="Aperçu - Épisode [0-9]*" rel="lightbox\[lightbox\]" href="(?\'image3\'[^"]*)"><img [^>]*><\/a>){0,1}(?:[^<]*<a title="Aperçu - Épisode [0-9]*" rel="lightbox\[lightbox\]" href="(?\'image4\'[^"]*)"><img [^>]*><\/a>){0,1}(?:[^<]*<a title="Aperçu - Épisode [0-9]*" rel="lightbox\[lightbox\]" href="(?\'image5\'[^"]*)"><img [^>]*><\/a>){0,1}(?:[^<]*<a title="Aperçu - Épisode [0-9]*" rel="lightbox\[lightbox\]" href="(?\'image6\'[^"]*)"><img [^>]*><\/a>){0,1}(?:(?!Date)[\D\d])*Date de la première diffusion : (?\'date\'[^ ]*)[^<]*<br \/>(?:[^<]*<a href="#"[^>]*>Teaser<\/a>[^<]*<div id="teaser_episode_[0-9]*" style="display:none;">(?\'synopsis\'(?:(?!<\/div>)[\D\d])*))*/';

        for ($p = 1; $p <= $nb_pages ; $p++) {

            $url = "http://anime.icotaku.com/fiche/episode/anime/".$id_anime."/page/".$p;

            $content = file_get_contents($url);

            if (preg_match_all($regex_episodes, $content, $matches, PREG_SET_ORDER)) {

                foreach ($matches as $key => $episode) {

                    if (isset($episode['synopsis'])){
                        $synopsis = html_entity_decode($episode['synopsis'], ENT_QUOTES | ENT_HTML5);
                    }else{
                        $synopsis = "";
                    }

                    if (isset($episode['title']) && $episode['title'] != '?'){
                        $title = html_entity_decode($episode['title'], ENT_QUOTES | ENT_HTML5);
                    }else{
                        $title = '';
                    }

                    // Convertit la date format français (01/02/2016) au format anglais (2016-02-01)
                    if (strlen($episode['date']) > 7) {
                        $formatDate = explode("/", $episode['date']);
                        $date = $formatDate[2] . "-" . $formatDate[1] . "-" . $formatDate[0];
                    } else {
                        $date = "";
                    }

                    $images = [
                        "screenshot1" => NULL,
                        "screenshot2" => NULL,
                        "screenshot3" => NULL,
                        "screenshot4" => NULL,
                        "screenshot5" => NULL,
                        "screenshot6" => NULL

                    ];
                    for ($i = 1; isset($episode['image'.$i]) && !empty($episode['image'.$i]); $i++){
                        $file_name = uniqid().".jpg";

                        if (!filter_var($episode['image'.$i], FILTER_VALIDATE_URL) === false) {
                            $headers = get_headers($episode['image'.$i]);

                            if(substr($headers[0], 9, 3) == "200"){
                                if (!empty($episode['image'.$i]) && copy($episode['image'.$i], $this->medias . 'episodes/' . $file_name)) {
                                    $images['screenshot'.$i] = $file_name;
                                }
                            }
                        }
                    }

                    $insert = sprintf(
                        "INSERT INTO animes_episodes (id_anime, number, title, diffusion, synopsis, hs, screenshot1, screenshot2, screenshot3, screenshot4, screenshot5, screenshot6)
                         VALUES (".$id_anime.", ".(int)$episode['number'].", '%s', '%s', '%s', ".($episode['hs'] == ' ' ? 0 : 1).", '%s', '%s', '%s', '%s', '%s', '%s')
                         ON DUPLICATE KEY UPDATE title = VALUES(title), diffusion = VALUES(diffusion), synopsis = VALUES(synopsis), hs = VALUES(hs),
                            screenshot1 = VALUES(screenshot1), screenshot2 = VALUES(screenshot2), screenshot3 = VALUES(screenshot3), screenshot4 = VALUES(screenshot4), screenshot5 = VALUES(screenshot5), screenshot6 = VALUES(screenshot6)",
                        mysqli_real_escape_string($this->cnx, $title),
                        mysqli_real_escape_string($this->cnx, $date),
                        mysqli_real_escape_string($this->cnx, $synopsis),
                        mysqli_real_escape_string($this->cnx, $images['screenshot1']),
                        mysqli_real_escape_string($this->cnx, $images['screenshot2']),
                        mysqli_real_escape_string($this->cnx, $images['screenshot3']),
                        mysqli_real_escape_string($this->cnx, $images['screenshot4']),
                        mysqli_real_escape_string($this->cnx, $images['screenshot5']),
                        mysqli_real_escape_string($this->cnx, $images['screenshot6'])
                    );

                    if (!mysqli_query($this->cnx, $insert)){
                        die("Erreur lors de l'enregistrement de l'épisode de l'anime (".$id_anime.") : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
                    }
                }
            }
        }
    }

    public function updateNewEpisodes(){

        echo "### Met à jour les nouveaux épisodes des animes ###\n\n";

        // Récupère les animes dont le nombre d'épisodes total n'est pas renseigné
        // ou ceux dont le nombre d'épisodes en BDD est inférieur au nombre d'épisodes total
        $sql = "SELECT a.id_anime
                FROM animes a
                WHERE a.nb_episodes >
                (SELECT COUNT(ae.id_anime)
                FROM animes_episodes ae
                WHERE ae.id_anime = a.id_anime)
                OR a.nb_episodes = 0";

        $id_animes = [];
        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $id_animes[] = $row['id_anime'];
            }
        }else{
            die("Impossible de récupérer les animes des épisodes à mettre à jour : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$sql."\n");
        }

        $total_animes = count($id_animes);
        $current_anime = 0;
        foreach($id_animes as $id_anime){
            $this->insertAnimeEpisodesWeb($id_anime);

            $current_anime++;
            echo "$current_anime/$total_animes Anime(s) traité(s).\r";
        }
        echo "$current_anime/$total_animes Anime(s) traité(s).\n";
    }

    public function updateScreenshots(){
        echo "### Met à jour les screenshots des épisodes ###\n\n";

        // Récupère les animes avec 0 screenshot d'épisode
        $sql = "SELECT a.id_anime
                FROM animes a
                LEFT JOIN animes_episodes ae
                ON ae.id_anime = a.id_anime
                AND ae.screenshot1 IS NULL
                WHERE ae.id_anime IS NOT NULL
                GROUP BY a.id_anime";

        $id_animes = [];
        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $id_animes[] = $row['id_anime'];
            }
        }else{
            die("Impossible de récupérer les épisodes à mettre à jour : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$sql."\n");
        }

        $total_animes = count($id_animes);
        $current_anime = 0;
        foreach($id_animes as $id_anime){
            $this->insertAnimeEpisodesWeb($id_anime);

            $current_anime++;
            echo "$current_anime/$total_animes Anime(s) traité(s).\r";
        }
        echo "$current_anime/$total_animes Anime(s) traité(s).\n";
    }

    public function getAnimesToUpdate() {

        echo "### Met à jour les animes ###\n\n";

        // Récupère les animes dont les informations générales sont susceptibles d'avoir été changés
        $sql = "SELECT id_anime, title, img_affiche, img_banniere
                FROM animes
                WHERE id_public = 0
                OR id_public IS NULL
                OR duration = 0
                OR id_studio IS NULL
                OR id_studio = 0
                OR nb_episodes = 0
                OR diffusion IN ('Bientôt', 'En cours', 'En pause')";

        $animes = [];
        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $animes[] = $row;
            }
        }else{
            die("Impossible de récupérer les animes à mettre à jour : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$sql."\n");
        }

        $total_animes = count($animes);
        $current_anime = 0;
        foreach($animes as $anime){
            $this->getAnimeInfos($anime['id_anime'], $anime);

            $current_anime++;
            echo "$current_anime/$total_animes Anime(s) traité(s).\r";
        }
        echo "$current_anime/$total_animes Anime(s) traité(s).\n";
    }

    public function updateNewTomes(){

        echo "### Met à jour les nouveaux tomes des mangas ###\n\n";

        // Récupère les mangas dont le nombre de tomes total n'est pas renseigné
        // ou ceux dont le nombre de tomes en BDD est inférieur au nombre de tomes total
        $sql = "SELECT m.id_manga
                FROM mangas m
                WHERE m.nb_tomes >
                (SELECT COUNT(mt.id_manga)
                FROM mangas_tomes mt
                WHERE mt.id_manga = m.id_manga)
                OR m.nb_tomes = 0";

        $id_mangas = [];
        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $id_mangas[] = $row['id_manga'];
            }
        }else{
            die("Impossible de récupérer les mangas des tomes à mettre à jour : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$sql."\n");
        }

        $total_mangas = count($id_mangas);
        $current_manga = 0;
        foreach($id_mangas as $id_manga){
            $this->insertMangaTomesWeb($id_manga);

            $current_manga++;
            echo "$current_manga/$total_mangas manga(s) traité(s).\r";
        }
        echo "$current_manga/$total_mangas manga(s) traité(s).\n";
    }

    public function insertMangaTomesWeb($id_manga) {

        $regex_tomes = '/<h2>Tome (?\'number\'[0-9]*)(?: : (?\'title\'[^<]*))*<\/h2>[^<]*<div [^>]*>[^<]*(?:<img [^>]*>[^<]*(?:<a title="[^"]*" rel="[^"]*" href="(?\'image_fr2\'[^"]*)"><img [^>]*><\/a>[^<]*){0,1})*(?:<a title="[^"]*" rel="[^"]*" href="(?\'image_jp\'[^"]*)"><img [^>]*><\/a>[^<]*){0,1}(?:<a title="[^"]*" rel="[^"]*" href="(?\'image_fr\'[^"]*)"><img [^>]*><\/a>[^<]*){0,1}(?:(?!Date de la)[\D\d])*Date de la première publication : <\/b><\/p>[^<]*<div><img src="[^"]*" alt="Jp" \/>(?\'date_jp\'[^<]*)<\/div>[^<]*(?:<br\/>[^<]*<div><img src="[^"]*" alt="Fr" \/>(?\'date_fr\'[^<]*)<\/div>[^<]*<div class="clear"><\/div>[^<]*<(?:a [^<]*<\/a> [^<]*<p [^>]*>(?\'synopsis\'[^<]*(?:<br \/>[^<]*)*)<\/p>)*)*/';

        $url = "http://manga.icotaku.com/fiche/tome/manga/".$id_manga;

        $content = file_get_contents($url);

        if (preg_match_all($regex_tomes, $content, $matches, PREG_SET_ORDER)) {

            foreach ($matches as $key => $tome) {

                if (isset($tome['title']) && $tome['title'] != '?'){
                    $title = html_entity_decode($tome['title'], ENT_QUOTES | ENT_HTML5);
                }else{
                    $title = '';
                }

                // Convertit la date format français (01/02/2016) au format anglais (2016-02-01)
                if (isset($tome['date_jp']) && strlen($tome['date_jp']) > 7) {
                    $formatDate = explode("/", $tome['date_jp']);
                    $publication_jp = $formatDate[2] . "-" . $formatDate[1] . "-" . $formatDate[0];
                } else {
                    $publication_jp = "";
                }
                if (isset($tome['date_fr']) && strlen($tome['date_fr']) > 7) {
                    $formatDate = explode("/", $tome['date_fr']);
                    $publication_fr = $formatDate[2] . "-" . $formatDate[1] . "-" . $formatDate[0];
                } else {
                    $publication_fr = "";
                }

                if (isset($tome['synopsis'])){
                    $synopsis = html_entity_decode($tome['synopsis'], ENT_QUOTES | ENT_HTML5);
                }else{
                    $synopsis = '';
                }

                if (isset($tome['image_fr2']) && !empty($tome['image_fr2'])){
                    $url_couverture_jp = "";
                    $url_couverture_fr = $tome['image_fr2'];
                }else{
                    if (isset($tome['image_jp']) && !empty($tome['image_jp'])){
                        $url_couverture_jp = $tome['image_jp'];
                    }else{
                        $url_couverture_jp = "";
                    }
                    if (isset($tome['image_fr']) && !empty($tome['image_fr'])){
                        $url_couverture_fr = $tome['image_fr'];
                    }else{
                        $url_couverture_fr = "";
                    }
                }

                $couverture_jp = '';
                if (!empty($url_couverture_jp)){
                    $file_name = uniqid().".jpg";

                    if (copy($url_couverture_jp, $this->medias.'tomes/'.$file_name)){
                        $couverture_jp = $file_name;
                    }
                }

                $couverture_fr = '';
                if (!empty($url_couverture_fr)){
                    $file_name = uniqid().".jpg";

                    if (copy($url_couverture_fr, $this->medias.'tomes/'.$file_name)){
                        $couverture_fr = $file_name;
                    }
                }

                $insert = sprintf(
                    "INSERT INTO mangas_tomes (id_manga, number, title, publication_jp, publication_fr, synopsis, couverture_jp, couverture_fr)
                     VALUES (".$id_manga.", ".(int)$tome['number'].", '%s', '%s', '%s', '%s', '%s', '%s')
                     ON DUPLICATE KEY UPDATE title = VALUES(title), publication_jp = VALUES(publication_jp), publication_fr = VALUES(publication_fr),
                     synopsis = VALUES(synopsis), couverture_jp = VALUES(couverture_jp), couverture_fr = VALUES(couverture_fr)",
                    mysqli_real_escape_string($this->cnx, $title),
                    mysqli_real_escape_string($this->cnx, $publication_jp),
                    mysqli_real_escape_string($this->cnx, $publication_fr),
                    mysqli_real_escape_string($this->cnx, $synopsis),
                    mysqli_real_escape_string($this->cnx, $couverture_jp),
                    mysqli_real_escape_string($this->cnx, $couverture_fr)
                );

                if (!mysqli_query($this->cnx, $insert)){
                    die("Erreur lors de l'enregistrement du tome du manga (".$id_manga.") : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$insert."\n");
                }
            }
        }
    }

    public function getMangasToUpdate() {

        echo "### Met à jour les mangas ###\n\n";

        // Récupère les mangas dont les informations générales sont susceptibles d'avoir été changés
        $sql = "SELECT id_manga, title, img_banniere
                FROM mangas
                WHERE id_public = 0
                OR id_public IS NULL
                OR nb_tomes = 0
                OR publication_jp IN ('Bientôt', 'En cours', 'En pause')
                OR publication_fr IN ('Bientôt', 'En cours', 'En pause')
                OR id_editeur_jp = 0
                OR id_editeur_jp IS NULL
                OR id_editeur_fr = 0
                OR id_editeur_fr IS NULL";

        $mangas = [];
        if ($query = mysqli_query($this->cnx, $sql)) {
            while ($row = mysqli_fetch_assoc($query)){
                $mangas[] = $row;
            }
        }else{
            die("Impossible de récupérer les mangas à mettre à jour : ".mysqli_error($this->cnx)."\n\n"."Requête SQL : ".$sql."\n");
        }

        $total_mangas = count($mangas);
        $current_manga = 0;
        foreach($mangas as $manga){
            $this->getMangaInfos($manga['id_manga'], $manga);

            $current_manga++;
            echo "$current_manga/$total_mangas manga(s) traité(s).\r";
        }
        echo "$current_manga/$total_mangas manga(s) traité(s).\n";
    }

    /**
     * @return array
     */
    public function getIdMangasListDb() {

        $sql = "SELECT id_manga FROM mangas";

        $id_mangas = [];
        if ($query = mysqli_query($this->cnx, $sql)){
            while ($row = mysqli_fetch_assoc($query)){
                $id_mangas[] = $row['id_manga'];
            }
        }

        return $id_mangas;
    }

    /**
     * @return int
     */
    public function nbPagesMangasList() {

        $url = "http://manga.icotaku.com/manga/index/filter/all/page/1";

        $content = file_get_contents($url);

        $regex_nb_pages = '/<a href="\/manga\/index\/filter\/all\/page\/([0-9]*)">&raquo;/';

        if (preg_match_all($regex_nb_pages, $content, $matches, PREG_SET_ORDER)){
            $nb_pages = $matches[0][1];
        }else{
            $nb_pages = 1;
            trigger_error('Total de pages liste mangas non trouvé', E_USER_WARNING);
        }

        return $nb_pages;
    }

    public function getIdMangasListWeb() {

        echo "### Récupère la liste des mangas ###\n\n";

        $id_mangas = [];

        $nb_pages = $this->nbPagesMangasList();

        $i = 0;
        for ($page = 1; $page <= $nb_pages; $page++) {

            $url = "http://manga.icotaku.com/manga/index/filter/all/page/".$page;

            $content = file_get_contents($url);

            $regex_manga = '/<tr class="">[^<]*<td>[^<]*<div class="td_apercufiche">[^<]*<a href="\/manga\/([^\/]*)\/[^"]*"><img width="64" alt="[^"]*" class="affiche_bloc" src="\/images\/..\/([^"]*)" \/><\/a>[^<]*<a href="[^"]*">([^<]*)/';

            if (!preg_match_all($regex_manga, $content, $matches, PREG_SET_ORDER)) {
                trigger_error('Erreur regex liste mangas', E_USER_WARNING);
            }

            foreach ($matches as $key => $manga) {
                $id_mangas[] = $manga[1];
                $i++;
                echo "$i manga(s) trouvé(s).\r";
            }
        }
        echo "$i manga(s) trouvé(s).\n";

        return $id_mangas;
    }

    /**
     * @return array
     */
    public function getNewIdMangas() {

        $id_mangas_db = $this->getIdMangasListDb();
        $id_mangas_web = $this->getIdMangasListWeb();

        $id_new_mangas = array_diff($id_mangas_web, $id_mangas_db);

        return $id_new_mangas;
    }

    public function getMangasListWeb() {

        $id_mangas = $this->getNewIdMangas();

        echo "### Recherche des informations des mangas ###\n\n";

        $total_mangas = count($id_mangas);
        $current_manga = 0;
        foreach ($id_mangas as $id_manga) {

            // Récupère les infos puis enregistre toutes les infos du manga à part les tomes
            $this->getMangaInfos($id_manga);
            // Récupère les infos sur les tomes du manga puis les enregistre
            $this->insertMangaTomesWeb($id_manga);

            $current_manga++;
            echo "$current_manga/$total_mangas manga(s) traité(s).\r";
        }
        echo "$current_manga/$total_mangas manga(s) traité(s).\n";
    }

    public function getMangaInfos($id_manga, $update = false){

        $regex_id_anime = "/'\/popup\/popupAnimeInfo\/anime\/([0-9]*)'/";
        $regex_id_manga = "/'\/popup\/popupMangaInfo\/manga\/([0-9]*)'/";

        $url = "http://manga.icotaku.com/manga/".$id_manga."/test.html";

        $content = file_get_contents($url);

        $genres = $this->getGenres($content);
        $themes = $this->getThemes($content);

        if ($update && isset($update['title']) && !empty($update['title'])){
            $title = $update['title'];
        }else {
            $title = $this->getMainTitle($content);
        }
        $titles = $this->getAlterTitles($content);

        $public = $this->getPublic($content);

        $nb_tomes = $this->getNbTomes($content);

        $year = $this->getYear($content);

        $publication_jp = $this->getPublicationJp($content);
        $publication_fr = $this->getPublicationFr($content);

        $id_editeur_jp = $this->getEditeur($content, $this->regex_editeur_jp);
        $id_editeur_fr = $this->getEditeur($content, $this->regex_editeur_fr);

        $synopsis = $this->getSynopsis($content);

        if ($update && isset($update['img_banniere']) && !empty($update['img_banniere'])){
            $img_banniere = $update['img_banniere'];
        }else {
            $img_banniere = $this->updateBanniere($content, 'manga');
        }

        $url = "http://manga.icotaku.com/fiche/lien/manga/".$id_manga;

        $content = file_get_contents($url);

        /**
         * Préquelles
         */

        $prequelles = [];

        $html_prequelles = $this->getBetween($content, '<h2 style="clear:both;">Préquelles', '<h2 style="clear:both;">Suites');

        if (preg_match_all($regex_id_manga, $html_prequelles, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $id_manga_prequelle) {
                $prequelles[] = $id_manga_prequelle[1];
            }
        }

        /**
         * Suites
         */

        $suites = [];

        $html_suites = $this->getBetween($content, '<h2 style="clear:both;">Suites', '<h2 style="clear:both;">Spin-off (histoires alternatives)');

        if (preg_match_all($regex_id_manga, $html_suites, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $id_manga_suite) {
                $suites[] = $id_manga_suite[1];
            }
        }

        /**
         * Anime
         */

        $animes = [];

        if (preg_match_all($regex_id_anime, $content, $matches, PREG_SET_ORDER)){
            foreach ($matches as $key => $id_anime) {
                $animes[] = $id_anime[1];
            }
        }

        $manga = [
            'id_manga' => $id_manga,
            'genres' => $genres,
            'themes' => $themes,
            'title' => $title,
            'alternative_title' => $titles,
            'public' => $public,
            'nb_tomes' => $nb_tomes,
            'year' => $year,
            'publication_jp' => $publication_jp,
            'publication_fr' => $publication_fr,
            'id_editeur_jp' => $id_editeur_jp,
            'id_editeur_fr' => $id_editeur_fr,
            'synopsis' => $synopsis,
            'img_banniere' => $img_banniere,
            'link' => [
                'prequels' => $prequelles,
                'suites' => $suites,
                'animes' => $animes
            ]
        ];

        $this->insertNewManga($manga);
    }
}

// Stock la date de début d'exécution du script
$timestart = microtime(true);

$db = new ConnectMangasDB;

// Ajoute les nouveaux épisodes des animes de la BDD
$db->updateNewEpisodes();
// Met à jour les informations générales des animes nécessaires
$db->getAnimesToUpdate();
// Ajoute les nouveaux animes non présents dans la BDD
$db->getAnimesListWeb();

// Ajoute les nouveaux tomes des mangas de la BDD
$db->updateNewTomes();
// Met à jour les informations générales des mangas nécessaires
$db->getMangasToUpdate();
// Ajoute les nouveaux mangas non présents dans la BDD
$db->getMangasListWeb();

date_default_timezone_set('UTC');

$timeend = microtime(true);
$time = $timeend - $timestart;

$time = getdate($time);
echo "\n\nDébut du script: ".date("H:i:s", $timestart)."\n";
echo "Fin du script: ".date("H:i:s", $timeend)."\n";
echo "Script exécuté en ".($time['hours'] == 0 ? ($time['minutes'] == 0 ? $time['seconds']."sec" : $time['minutes']."min".$time['seconds']."sec") : $time['hours']."h".$time['minutes']."min".$time['seconds']."sec")."\n";