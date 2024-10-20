-- +-------------------------------------------------+
-- � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
-- +-------------------------------------------------+
-- $Id: readme.txt,v 1.7 2021/05/03 10:13:12 dbellamy Exp $

------------------------------------------------------------------------------------------------------------------

Descripci�n de los archivos
bibli.sql : estructura de la base de datos sola, sin datos

minimum.sql : usuario admin/admin, par�metros de la aplicaci�n

feed_essential.sql : lo que se necesita para inciiar la aplicaci�n en modo inicio r�pido :
	Datos de la aplicaci�n rellenados, modificables.
	Un juego de copia de seguridad listo a emplear
	Un juego de par�metros de Z3950.
	
data_test.sql : una peque�a selecci�n de datos de registros, usuarios, para poder probar PMB.
	Registros, usuarios, pr�stamos, ejemplares, publicaciones peri�dicas
	Se basa en los datos de la aplicaci�n inclu�dos tambi�n en feed_essential.sql
	Debe cargarse el tesauro UNESCO_FR unesco_fr.sql
	
Tesauros : se proponen 3 tesauros :
	unesco_fr.sql : tesauro jer�rquico de la UNESCO, bastante importante y bien hecho.
	grumeau.sql : un poco m�s peque�o, m�s sencillo pero bien constru�do tambi�n.
	environnement : un tesauro �til para un fondo documental relacionado con el Medio ambiente.
	
Indexations internes : se proponen 4 indexaciones :
	indexint_100.sql : 100 casos del saber o margarita de colores, indexaci�n decimal 
	style Dewey simplificada para educaci�n
	indexint_chambery.sql : indexaci�n de estilo Dewey de la BM de Chamb�ry, bien concebido pero poco adaptada
	a bibliotecas peque�as
	indexint_dewey.sql : indexaci�n estilo Dewey
	indexint_small_en.sql : indexaci�n estilo Dewey reducida y en ingl�s
	

************************************************************************************************
________________________________________________________________________________________________
Atenci�n, si est�s haciendo una actualizaci�n de una versi�n anterior :
------------------------------------------------------------------------------------------------
*********** A realizar con cada instalaci�n o actualizaci�n de la aplicaci�n  ****************
Cuando instalas una nueva versi�n
sobre una versi�n anterior debes, obligatoriamente,
antes de copiar los arhivos nuevos contenidos en el archivo zip
al servidor web :

comprobar que los par�metros incluidos en :
./includes/db_param.inc.php
./opac_css/includes/opac_db_param.inc.php

corresponden a tu configuraci�n (haz una copia antes !)

Adem�s :
Debes hacer una actualizaci�n de la base de datos.
No se perder� nada.

Con�ctate de forma habitual a PMB, el estilo gr�fico puede ser diferente, 
ausente (visualizaci�n sin colores ni im�gines)

Ve a Administraci�n > Herramientas > act base de datos para actualizar la
base de datos.

Una serie de mensajes te ir�n indicando las actualizaciones sucesivas, 
para continuar la actualizaci�n haz clic en el enlace de la parte inferior de la p�gina 
hasta que aparezca el mensaje 'Tu base de datos est� al d�a con la versi�n...'

Puedes editar tu cuenta de usuario para modificar tus preferencias, cambiando
el estilo de visualizaci�n.

No dudes en hacernos llegar tus dudas, problemas o sugerencias por correo
electr�nico : pmb@sigb.net

Por otro lado, estaremos encantados de contar contigo como uno de nuestros
usuarios, y si nos facilitaras algunos datos como n�mero de usuarios, de obras
de CD... junto con los datos de tu establecimiento (o a t�tulo particular) 
nos ayudar�s a conocerte mejor.

Encontrar�s m�s informaci�n en el directorio ./doc o bien 
en nuestra p�gina web http://www.sigb.net

El equipo de desarrolladores.

