<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ScenarioOrm.php,v 1.2 2024/07/10 15:02:52 rtigero Exp $

namespace Pmb\ImportExport\Orm;

use Pmb\Common\Orm\Orm;

class ScenarioOrm extends Orm
{

	/**
	 * Prefix des champs de la table
	 */
	public const PREFIX = "scenario";

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "import_export_scenarios";

	/**
	 *
	 * @var string
	 */
	public static $idTableName = "id_scenario";

	/**
	 *
	 * @var integer
	 */
	protected $id_scenario = 0;

	/**
	 *
	 * @var string
	 */
	protected $scenario_name = '';

	/**
	 *
	 * @var string
	 */
	protected $scenario_comment = '';

	/**
	 *
	 * @var string
	 */
	protected $scenario_settings = '';

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;

	// 	/**
	// 	 * @Relation n0
	//      * @Orm Pmb\ImportExport\Orm\SourceOrm
	//      * @Table import_export_sources
	//      * @RelatedKey id_source
	//      * @ForeignKey num_scenario
	// 	 */
	// 	protected $sources = null;

	// 	/**
	// 	 *
	// 	 * @Relation n0
	// 	 * @Orm Pmb\ImportExport\Orm\StepOrm
	// 	 * @Table import_export_steps
	//      * @RelatedKey id_step
	//      * @ForeignKey num_scenario
	// 	 */
	// 	protected $steps = null;
}
