<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SourceOrm.php,v 1.3 2024/07/16 14:37:57 rtigero Exp $

namespace Pmb\ImportExport\Orm;

use encoding_normalize;
use Pmb\Common\Helper\GlobalContext;
use Pmb\Common\Orm\Orm;

class SourceOrm extends Orm
{

	/**
	 * Prefix des champs de la table
	 */
	public const PREFIX = "source";

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "import_export_sources";

	/**
	 *
	 * @var string
	 */
	public static $idTableName = "id_source";


	/**
	 *
	 * @var integer
	 */
	protected $id_source = 0;

	/**
	 *
	 * @var string
	 */
	protected $source_name = '';

	/**
	 *
	 * @var string
	 */
	protected $source_comment = '';

	/**
	 *
	 * @var string
	 */
	protected $source_type = '';

	/**
	 *
	 * @var string
	 */
	protected $source_settings = '';

	/**
	 *
	 * @var integer
	 */
	protected $num_scenario = 0;

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;

	/**
	 * Verifie que le source n'est pas utilisee par un step du scenario associe
	 * @return bool
	 */
	protected function checkBeforeDelete()
	{
		$fields["num_scenario"] = [
			'value' =>  $this->num_scenario,
			'operator' => '='
		];
		$result = StepOrm::finds($fields);
		foreach ($result as $stepOrm) {
			if ($stepOrm->step_settings == "") {
				continue;
			}
			$stepSettings = encoding_normalize::json_decode($stepOrm->step_settings);
			if (!empty($stepSettings->source) && $stepSettings->source == $this->id_source) {
				return false;
			}
		}
		return true;
	}

	public function delete()
	{
		try {
			parent::delete();
		} catch (\Exception $e) {
			throw new \Exception(GlobalContext::msg('ie_error_source_used'));
		}
	}
}
