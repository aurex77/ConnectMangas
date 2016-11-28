<section>
  <div class="inner">
    <h1><?= $manga->title; ?></h1>
    <p>Public : <?= $manga->public; ?></p>
    <p>Nombre de tomes : <?= $manga->nb_tomes; ?></p>
    <p>Année : <?= $manga->year; ?></p>
    <p>Publication JP : <?= $manga->publication_jp; ?></p>
    <p>Publication FR : <?= $manga->publication_fr; ?></p>
    <p>Editeur JP : <?= $manga->editeur_jp; ?></p>
    <p>Editeur FR : <?= $manga->editeur_fr; ?></p>
    <p><?= $manga->synopsis; ?></p>
    <p>Genres : <?= str_replace(',', ', ', $manga->genres); ?></p>
    <p>Thèmes : <?= str_replace(',', ', ', $manga->themes); ?></p>
  </div>
</section>
