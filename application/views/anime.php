<section>
  <div class="inner">
    <h1><?= $anime->title; ?></h1>
    <p>Public : <?= $anime->public; ?></p>
    <p>Nombre d'épisodes : <?= $anime->nb_episodes; ?></p>
    <p>Durée : <?= $anime->duration; ?></p>
    <p>Saison : <?= $anime->season; ?></p>
    <p>Mois : <?= $anime->month; ?></p>
    <p>Année : <?= $anime->year; ?></p>
    <p>Diffusion : <?= $anime->diffusion; ?></p>
    <p>Studio : <?= $anime->studio; ?></p>
    <p><?= $anime->synopsis; ?></p>
    <p>Genres : <?= str_replace(',', ', ', $anime->genres); ?></p>
    <p>Thèmes : <?= str_replace(',', ', ', $anime->themes); ?></p>
  </div>
</section>
