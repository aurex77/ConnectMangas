<!-- <?php echo form_open('api/action/register'); ?>
  <input type="text" name="username" id="usermane" placeholder="Username" value="" required />
  <input type="password" name="password" id="password" placeholder="Password" value="" required />

  <input type="submit" name="submit" value="Envoyer" />
</form> -->

<section id="section-home">
  <div class="inner">

    <div class="title">
      <h1>ConnectMangas</h1>
    </div>

    <div class="presentation-globale">
      <p>
        ConnectMangas est une plateforme communautaire lié au domaine du manga et de l'anime japonais !<br/><br/>
        Envie de commencer à lire un nouveau manga mais vous ne voulez pas acheter tout de suite ? Trouvez un autre membre proche de chez vous et échangez le tome qu'il vous faut !<br/><br/>
        Vous suivez tellement d'anime que vous ne savez plus ce que vous avez regardé ou non ? Regardez votre calendrier personnalisé !
      </p>

      <a href="<?= base_url(); ?>"><button class="cta cta-commencer"><span>Commencer maintenant</span></button></a>
    </div>

  </div>
</section>

<section id="section-fonctionnalites">
  <div class="inner">

    <!-- <div class="title">
      <h1>Fonctionnalités</h1>
    </div> -->

    <div class="presentation-fonctionnalites">
      <ul>
        <li>
          <div class="icons">
            <i class="fa fa-book" aria-hidden="true"></i>
          </div>
          <p>Plus de 6000 mangas et 2000 animes !</p>
        </li><!--
      --><li>
          <div class="icons">
            <i class="fa fa-user" aria-hidden="true"></i>
          </div>
          <p>Une communauté de passionnés !</p>
        </li><!--
      --><li class="last">
          <div class="icons">
            <i class="fa fa-map-marker" aria-hidden="true"></i>
          </div>
          <p>Échangez grâce à la géolocalisation !</p>
        </li><!--
      --><li class="last">
          <div class="icons">
            <i class="fa fa-mobile" aria-hidden="true"></i>
          </div>
          <p>Disponible aussi sur mobile !</p>
        </li>
      </ul>
    </div>

  </div>
</section>

<section id="section-contact">
  <div class="inner">

    <div class="title">
      <h1>Contact</h1>
    </div>

    <div id="formulaire">
      <?php echo form_open('api/action/contact'); ?>
        <div class="form-el">
          <input type="text" name="name" id="name" placeholder="Nom" required />
        </div>
        <div class="form-el">
          <input type="email" name="email" id="email" placeholder="Email" required />
        </div>
        <div class="form-el">
          <textarea name="message" id="message" placeholder="Message" rows="5" required></textarea>
        </div>
        <div class="form-el">
          <button type="submit" class="cta cta-submit"><span>Envoyer</span></button>
        </div>
      </form>
    </div>

  </div>
</section>
