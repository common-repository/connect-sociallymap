=== Sociallymap Connect ===
Plugin Name: Sociallymap Connect
Contributors: sociallymap
Plugin URI: https://wordpress.org/plugins/connect-sociallymap/
Description: A plugin that let the Sociallymap users post on their blog from their mapping
Version: 3.0.10
Tags: sociallymap, autopublish
Requires at least: 5.1
Stable tag: 3.0.10
Tested up to: 5.1
Requires PHP: 5.6
Author: Sociallymap
Author URI: https://www.sociallymap.com/
License: Alhena © 2021

Wordpress Sociallymap connect vous permet de publier automatiquement vos articles depuis votre mapping Sociallymap.

== Description ==
Wordpress Sociallymap connect est un plugin permettant l'automatisation de publication d'articles. Plus d'informations concernant l'application sociallymap, consultez notre site https://www.sociallymap.com/.

Les options disponible du plugin sont les suivantes :
Label :
Renseigner le nom que vous souhaitez donner à votre entité.

Identifiant de l'entité :
Pour remplir cette case, rendez-vous sur votre mapping et récupérez le code "Identifiant à indiquer dans votre plugin" et collez-le.

Publier en mode :
Publication Directe
Publication en mode « Brouillon »
Publication « En attente de relecture »
Publication « Privée »

Image :
Publier l’image de l’article en tant que « Image à la une ».
Publier l’image dans l’article.
Publier l’image en tant que « Image à la une » ET dans l’article.

Afficher les articles dans une fenêtre modale :
Permet d’ouvrir l’article publié dans une fenêtre modale : vos visiteurs peuvent lire l’article publié sur le site source à travers votre site, à la fermeture de la « Fenêtre Modale » les visiteurs restent sur votre site. Cela vous permet de conserver le trafic sur votre site web et de réduire le taux de rebond sur votre site.

Inclure les balises de lien canonique :
Permet d’inclure, ou non les balises de lien canonique.

Ne pas suivre les liens (nofollow) :
Empêche au moteur de recherche de suivre les liens.

Libellé suite d'article :
Libellé de la phrase permettant de lire la suite de l’article. Cette phrase sera présente dans l’article en lui-même et non sur les boutons de votre site.

== Installation ==
1. installer le plugin à travers le menu de gestion de plugin Wordpress directement ou ajouter le plugin dans le dossier `/wp-content/plugins/`.
2. Activer le plugin dans le menu des extensions du plugin.
3. Ajouter une nouvelle entité correspond à la configuration de votre mapping Sociallymap.
4. Suivez les instructions au travers de la documentation du plugin disponible dans le menu "documentation".


== Screenshots ==
1. Options d'une entité
2. Exemple d'article publié

== Changelog ==
= 3.0.10 =
* Fix medias display

= 3.0.9 =
* Message content has priority over link summary

= 3.0.8 =
* Fix

= 3.0.7 =
* Fix if missing media
* Add Enum
* Add Config

= 3.0.6 =
* Fix clean log
* Modify requester
* Add exceptions handler
* Add errors handler

= 3.0.5 =
* Fix error log
* Fix require upgrade.php

= 3.0.4 =
* Fix slug homepage

= 3.0.3 =
* Fix file missing

= 3.0.2 =
* Fix typo

= 3.0.1 =
* Fix tag

= 3.0 =
* Refonte complète du plugin.
* Ajout d'un export de log.
* Compatible avec Yoast SEO.

= 2.1.2 =
* Ajout de plus d'information dans les logs d'erreur

= 2.1 =
* Correction des urls Sociallymap

= 1.9.7.1 =
* Correction pour PHP 5.3

= 1.9.7 =
* Ajout de vérifications de la plateforme et alerte dans le backoffice

= 1.9.6 =
* Corrections pour SSL

= 1.9.5 =
* Meilleur gestion des réponses 302

= 1.9.4 =
* Corrections mineures

= 1.9.3 =
* Correction connections sécurisées

= 1.9.1 =
* Ajout de l'image de présentation sur le wordpress plugin store.

= 1.8 =
* Restructuration du système d'affichage de la modal pour un bug survenant lors de l'affichage de certains sites

= 1.7 =
* Réajustement d'un bug qui afficher une boite de dialog lors de l'ouverture de la modale

= 1.6 =
* Fonctionnement de la modal en cas d'utilisation de google alert

= 1.5 =
* Ajustement de la description de l'application

= 1.4 =
* Ajustement du style pour la version 3.x de wordpress

= 1.3 =
* Switch sur la version 1.3

= 1.2 =
* Switch sur la version 1.2

= 1.1 =
* Mise à jour de l'option readmore

== Upgrade Notice ==

= 1.9.3 =
Mise à jour nécessaire pour suivre le passage en connexions sécurisées
