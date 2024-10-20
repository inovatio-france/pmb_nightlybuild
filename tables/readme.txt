-- +-------------------------------------------------+
-- � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
-- +-------------------------------------------------+
-- $Id: readme.txt,v 1.11 2021/05/03 10:13:12 dbellamy Exp $

************************************************************************************************

Description des fichiers

*************************************
bibli.sql : Structure de la base de donn�es uniquement, pas de donn�es

Version unique
Moteur MyISAM

Cr�ation du fichier :
mysqldump -u bibli -pbibli -d --quote-names=FALSE --skip-add-drop-table bibli | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > bibli.sql

Cr�ation du fichier sans le moteur MyISAM :
mysqldump -u bibli -pbibli -d --quote-names=FALSE --skip-add-drop-table bibli | sed 's/ ENGINE=MyISAM DEFAULT AUTO_INCREMENT=[0-9]*\b//g' > bibli_no_engine.sql
		
Penser � ajouter la ligne de commentaire originale du fichier afin de conserver la version CVS du fichier
		
*************************************

minimum.sql : Donn�es minimales 
Utilisateur admin/admin, 
Param�tres de l'application, 
Jeu de sauvegarde pr�t � l'emploi, 
Jeu de param�trage serveur Z3950.
    
Versions en fran�ais, espagnol, italien, anglais et portuguais

La liste des tables � inclure �volue � chaque version.
Faire une installation minimum et r�pertorier les tables non vides pour mettre � jour la liste.

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob bibli abts_periodicites abts_status anim_price_types anim_registration_origins anim_registration_status anim_status authorities authorities_statuts bannette_tpl categories classements cms_editorial_types contact_forms contribution_area_status empr_statut explnum_statut grids_generic lenders lignes_actes_statuts noeuds notice_statut origine_notice origin_authorities parametres parametres_uncached pclassement sauv_sauvegardes sauv_tables scan_request_status serialcirc_tpl suggestions_categ thesaurus users z_attr z_bib > minimum.sql

Penser � ajouter la ligne de commentaire originale du fichier afin de conserver la version CVS du fichier

*************************************

feed_essential.sql : Donn�es n�cessaires pour utiliser l'application rapidement.

Versions en francais, italien, espagnol et portuguais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob bibli arch_emplacement arch_statut arch_type caddie caddie_procs docs_codestat docs_location docs_section docs_statut docs_type docsloc_section empr_caddie empr_caddie_procs empr_categ empr_codestat etagere etagere_caddie expl_custom expl_custom_lists facettes infopages notice_tpl notice_tplcode procs procs_classements search_perso statopac_request statopac_vues statopac_vues_col > feed_essential.sql

Penser � ajouter la ligne de commentaire originale du fichier afin de conserver la version CVS du fichier

*************************************

data_test.sql : S�lection de donn�es afin de pouvoir tester de suite PMB.
Notices, lecteurs, pr�teurs, exemplaires, p�riodiques...
Se base sur les donn�es de l'application fournies dans feed_essential.sql
Doit charger le th�saurus unesco.sql

Version unique

Cr�ation du fichier :

Param�tres � modifier :
notice_enrichment, show_social_network
notices_format_onglets =5

Penser � ajouter la ligne de commentaire originale du fichier afin de conserver la version CVS du fichier

*************************************

unesco.sql 	: Th�saurus de l'UNESCO, orient� �ducation.

Version unique multilingue fran�ais, anglais, espagnol

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob bibli thesaurus noeuds categories voir_aussi > unesco.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

agneaux.sql : Th�saurus orient� m�diath�que.

Version unique en fran�ais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob bibli thesaurus noeuds categories voir_aussi > agneaux.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

environnement.sql : Th�saurus pour fonds documentaire orient� environnement.

Version unique en fran�ais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob bibli thesaurus noeuds categories voir_aussi > environnement.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************
	
indexint_100.sql : 100 cases du savoir ou marguerite des couleurs, 
indexation d�cimale style Dewey simplifi�e pour l'�ducation

Versions en fran�ais, portuguais et anglais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob  bibli pclassement indexint > indexint_100.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

indexint_dewey.sql : Indexation style Dewey

Version unique en fran�ais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob  bibli pclassement indexint > indexint_dewey.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

indexint_chambery.sql : Indexation style Dewey de la BM de Chamb�ry, tr�s bien con�ue mais peu adapt�e � des petites biblioth�ques
	
Version unique en fran�ais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob  bibli pclassement indexint > indexint_dewey.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

bibliportail.sql : Base compl�te portail Pag�o

Version unique en fran�ais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob  bibli  > bibliportail.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

biblizen.sql : Base compl�te portail Zen

Version unique en fran�ais

Cr�ation du fichier :
mysqldump -u bibli -pbibli -t --skip-extended-insert --complete-insert --hex-blob  bibli  > biblizen.sql

Penser � conserver la ligne de commentaire originale du fichier minimum.sql afin de conserver la version CVS du fichier

*************************************

dataref.inc.php : Fichier liste des index des tables 
pour utilisation en  Administration >> Outils >> Maintenance MySQL >> V�rifier la pr�sence des index sur les tables

Version unique 

Cr�ation du fichier :
php genere_dataref.php

*************************************


