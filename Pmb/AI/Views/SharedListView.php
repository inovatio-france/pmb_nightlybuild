<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SharedListView.php,v 1.2 2024/06/17 12:06:20 jparis Exp $

namespace Pmb\AI\Views;

use Pmb\Common\Views\VueJsView;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class SharedListView extends VueJsView
{

}
