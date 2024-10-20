-- +-------------------------------------------------+
-- � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
-- +-------------------------------------------------+
-- $Id: readme.txt,v 1.5 2021/05/03 10:13:12 dbellamy Exp $

------------------------------------------------------------------------------------------------------------------

Descri��o dos arquivos
bibli.sql : estrutura da base de dados, sem dados

minimum.sql : utilizador admin/admin, par�metros da aplica��o

feed_essential.sql : o que se necessita para iniciar a aplica��o em modo in�cio r�pido :
	Dados da aplica��o preenchidos, modific�veis.
	Um conjunto de c�pia de seguran�a pronto a empregar
	Um conjunto de par�metros de Z3950.
	
data_test.sql : uma pequena selec��o de dados de registos, utilizadores, para poder experimentar o PMB.
	Registos, utilizadores, empr�stimos, exemplares, publica��es peri�dicas
	Baseia-se nos dados da aplica��o inclu�dos tamb�m em feed_essential.sql
	Deve-se carregar o thesaurus UNESCO_FR unesco_fr.sql
	
Tesauros : prop�em-se 3 thesaurus :
	unesco_fr.sql : thesaurus hier�rquico da UNESCO, bastante importante e bem constru�do.
	agneaux.sql : um poco mais pequeno, mais simples m�s tamb�m bem constru�do.
	environnement : um thesaurus �til para un fundo documental relacionado com o meio ambiente.
	
Indexations internes : prop�em-se 4 indexa��es :
	indexint_100.sql : 100 casos do saber ou margarida de cores, indexa��o decimal 
	style Dewey simplificada para educa��o
	indexint_chambery.sql : indexa��o de estilo Dewey da BM de Chamb�ry, bem concebido mas pouco adaptada
	a bibliotecas pequenas
	indexint_dewey.sql : indexa��o estilo Dewey
	indexint_small_en.sql : indexa��o estilo Dewey reduzida e em ingl�s
	

************************************************************************************************
________________________________________________________________________________________________
Aten��o, se est� a fazer uma actualiza��o de uma vers�o anterior :
------------------------------------------------------------------------------------------------
*********** A realizar a cada instala��o ou actualiza��o da aplica��o  ****************
Quando instala uma nova vers�o
sobre uma vers�o anterior deve, obrigatoriamente,
antes de copiar os arquivos novos contidos no arquivo zip
no servidor web :

comprovar que os par�metros inclu�dos em :
./includes/db_param.inc.php
./opac_css/includes/opac_db_param.inc.php

correspondem � sua configura��o (fa�a uma c�pia antes !)

Adicionalmente :
Deve fazer uma actualiza��o da base de dados.
N�o se perder� nada.

Ligue-se da forma habitual ao PMB, o est�lo gr�fico pode ser diferente, 
ausente (visualiza��o sem cores nem imagens)

V� a Administra��o > Ferramentas > act base de dados para actualizar a
base de dados.

Uma s�rie de mensagens ir�o indicando as actualiza��es sucessivas, 
para continuar a actualiza��o clique no link da parte inferior da p�gina 
at� que apare�a a mensagem 'A sua base de dados est� actualizada com a vers�o...'

Pode editar a sua conta de utilizador para modificar as suas prefer�ncias, mudando
o estilo de visualiza��o.

Fa�a chegar as suas d�vidas, problemas ou sugest�es por correio
electr�nico : pmb@sigb.net

Por outro lado, gostaremos de contar consigo como um dos nossos
utilizadores, e se nos facilitar alguns dados como o n�mero de utilizadores, de obras
de CD... junto com os dados do seu estabelecimento (ou a t�tulo particular) 
nos ajudar� a conhec�-lo melhor.

Encontrar� mais informa��o no direct�rio ./doc ou 
na nossa p�gina web http://www.sigb.net

A equipa de desenvolvimento.

