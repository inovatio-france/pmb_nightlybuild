<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StepOrm.php,v 1.2 2024/07/10 15:02:52 rtigero Exp $

namespace Pmb\ImportExport\Orm;

use Pmb\Common\Orm\Orm;

class StepOrm extends Orm
{

	/**
	 * Prefix des champs de la table
	 */
	public const PREFIX = "step";

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "import_export_steps";

	/**
	 *
	 * @var string
	 */
	public static $idTableName = "id_step";

	/**
	 *
	 * @var integer
	 */
	protected $id_step = 0;

	/**
	 *
	 * @var string
	 */
	protected $step_name = '';

	/**
	 *
	 * @var string
	 */
	protected $step_comment = '';

	/**
	 *
	 * @var string
	 */
	protected $step_type = '';

	/**
	 *
	 * @var string
	 */
	protected $step_settings = '';

	/**
	 *
	 * @var integer
	 */
	protected $step_order = 0;

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
}
