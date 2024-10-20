<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RootSort.php,v 1.3 2023/11/07 16:10:00 rtigero Exp $

namespace Pmb\DSI\Models\Sort;

use Pmb\DSI\Models\Root;

class RootSort extends Root
{

	public const TYPE_QUERY = 1;

	public const TYPE_OTHER = 2;

	protected const DIRECTIONS = [
		"ASC",
		"DESC"
	];

	public static $fields = array();

	protected $type = "";

	protected $fieldType = "";

	public function __construct($data = null)
	{
		$this->data = $data;
	}

	/**
	 * Tri appel� pendant la requ�te pour ordonner les r�sultats
	 *
	 * @return string
	 */
	public function getSortQuery()
	{
		if ($this->type == self::TYPE_QUERY) {
			switch ($this->fieldType) {
				case "datetime":
				case "string":
				default:
					return "ORDER BY " . $this->field . " " . $this->direction;
			}
		}
		return "";
	}

	/**
	 * Tri appel� apr�s que la requ�te aura �t� jou�e
	 *
	 * @param array $data
	 *        	Tableau � trier
	 * @return array
	 */
	public function getSortOther($data = array())
	{
		if ($this->type == self::TYPE_OTHER) {
			return $this->sortData($data);
		}
		return $data;
	}

	/**
	 * Tri de type autre � effectuer
	 * A d�river dans les sous-classes
	 *
	 * @param array $data
	 * @return array
	 */
	protected function sortData($data = array())
	{
		return $data;
	}

	/**
	 * Permet d'inclure une jointure dans la requ�te du s�lecteur
	 * @return string
	 */
	public function getJoinClause()
	{
		return "";
	}
}