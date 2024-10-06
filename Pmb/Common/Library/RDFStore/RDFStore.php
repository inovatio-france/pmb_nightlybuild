<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RDFStore.php,v 1.1 2024/06/21 14:25:20 rtigero Exp $

namespace Pmb\Common\Library\RDFStore;

class RDFStore extends \sparql
{
	public function storeTriples($arrayTriples, $graph = "pmb")
	{
		if (count($arrayTriples)) {
			$q = $this->get_prefix_text() . "INSERT INTO <" . $graph . "> {";
			$q .= implode(" .\n", $arrayTriples);
			$q .= "}";
			$r = $this->query($q);
			if(! empty( $this->errors)) {
				var_dump($q, $r, $this->errors);

			}
		}
	}

	public function query($query)
	{
		$query = $this->get_prefix_text() . $query;
		return parent::query($query);
	}
}
