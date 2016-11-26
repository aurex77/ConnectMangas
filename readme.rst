Pour lancer le projet correctement, il faut faire un vhost !

Dans votre fichier apache/conf/extra/httpd-vhosts.conf (pour Windows), ajouter :
Dans votre fichier Applications/MAMP/conf/apache/extra/httpd-vhosts.conf (pour Mac avec MAMP), ajouter :

<VirtualHost *:80>
	DocumentRoot "D://Programmes/xampp/htdocs/connectmangas" // Mettre le chemin de votre projet

	ServerName my.connectmangas.com

	ServerAlias my.connectmangas.com

</VirtualHost>



Ensuite, modifier le fichier hosts (C:/Windows/System32/drivers/etc/) pour pouvoir lancer my.connectmangas.com :
127.0.0.1 my.connectmangas.com

Relancer le service apache, relancer votre navigateur, aller sur my.connectmangas.com et enjoy :)