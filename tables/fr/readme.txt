-- +-------------------------------------------------+
-- � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
-- +-------------------------------------------------+
-- $Id: readme.txt,v 1.11 2021/05/03 10:13:12 dbellamy Exp $

------------------------------------------------------------------------------------------------------------------

Description des fichiers
bibli.sql : structure de la base de donn�es uniquement, pas de donn�es

minimum.sql : utilisateur admin/admin, param�tres de l'application

feed_essential.sql : ce dont vous avez besoin pour utiliser l'application en mode quick-start :
	Donn�es de l'application pr�remplies, modifiables.
	Un jeu de sauvegarde pr�t � l'emploi
	Un jeu de param�trage de Z3950.
	
data_test.sql : une petite s�lection de donn�es de notices, lecteurs, afin de pouvoir tester de suite PMB.
	Notices, lecteurs, pr�teurs, exemplaires, p�riodiques
	Se base sur les donn�es de l'application fournies dans feed_essential.sql
	Doit charger le th�saurus UNESCO_FR unesco_fr.sql
	
Th�saurus : 3 th�saurus vous sont propos�s :
	unesco.sql : th�saurus hi�rarchis� de l'UNESCO, assez important et bien fait.
	agneaux.sql : plus petit, plus simple mais bien fait aussi.
	environnement : un th�saurus possible pour un fonds documentaire ax� Environnement.
	
Indexations internes : 4 indexations sont propos�es :
	indexint_100.sql : 100 cases du savoir ou marguerite des couleurs, indexation d�cimale 
	style Dewey simplifi�e pour l'�ducation
	indexint_chambery.sql : indexation style Dewey de la BM de Chamb�ry, tr�s bien con�ue
	mais peu adapt�e � des petites biblioth�ques
	indexint_dewey.sql : indexation style Dewey
	indexint_small_en.sql : indexation style Dewey r�duite et en anglais
	

************************************************************************************************
________________________________________________________________________________________________
Attention, si vous faites une mise � jour d'une base existante :
------------------------------------------------------------------------------------------------
*********** A faire suite � chaque installation ou mise � jour de l'application ****************
Quand vous installez une nouvelle version 
sur une version pr�c�dente, vous devez imp�rativement, 
apr�s la copie des fichiers contenus dans cette archive 
sur le serveur web :

v�rifiez que les param�tres contenus dans :
./includes/db_param.inc.php
./opac_css/includes/opac_db_param.inc.php

correspondent � votre configuration (faites une sauvegarde avant !)

En outre :
Vous devez faire la mise � jour du noyau de la base de donn�es.
Rien ne sera perdu.

Connectez-vous de mani�re habituelle � PMB, le style graphique peut 
�tre diff�rent, voire absent (affichage assez d�cousu sans couleur ni images)

Passez en Administration > Outils > maj base pour mettre � jour le noyau de
votre base de donn�es.

Une s�rie de messages vous indiqueront les mises � jour successives, 
poursuivez la mise � jour de la base par le lien en bas de page jusqu'� voir 
s'afficher 'Votre base est � jour en version...'

Vous pouvez alors �diter votre compte pour modifier �ventuellement 
vos pr�f�rences, notamment le style d'affichage.

N'h�sitez pas � nous faire part de vos probl�mes ou id�es 
par mail : pmb@sigb.net

En outre, nous serions heureux de vous compter parmi nos utilisateurs et
quelques chiffres tels que nombre de lecteurs, d'ouvrages, de CD... avec les
coordonn�es de votre �tablissement (ou � titre particulier) nous suffiront
pour mieux vous connaitre.

Plus d'informations dans le r�pertoire ./doc ou bien 
sur notre site http://www.sigb.net

L'�quipe des d�veloppeurs.

