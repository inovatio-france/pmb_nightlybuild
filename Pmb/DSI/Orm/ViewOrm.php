<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ViewOrm.php,v 1.6 2023/02/02 09:52:22 rtigero Exp $
namespace Pmb\DSI\Orm;

use Pmb\Common\Orm\Orm;

class ViewOrm extends Orm
{

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "dsi_view";

	/**
	 * Primary Key
	 *
	 * @var string
	 */
	public static $idTableName = "id_view";

	/**
	 *
	 * @var integer
	 */
	protected $id_view = 0;

	/**
	 *
	 * @var string
	 */
	protected $name = "";

	/**
	 *
	 * @var boolean
	 */
	protected $model = false;

	/**
	 *
	 * @var string
	 */
	protected $settings = "";

	/**
	 *
	 * @var integer
	 */
	protected $type = 0;

	/**
	 *
	 * @var integer
	 */
	protected $num_model = 0;

	/**
	 *
	 * @Relation 0n
	 * @Orm Pmb\DSI\Orm\ViewOrm
	 * @RelatedKey num_model
	 */
	protected $view_model = null;

	/**
	 *
	 * @var integer
	 */
	protected $num_parent = 0;

	/**
	 *
	 * @Relation 0n
	 * @Orm Pmb\DSI\Orm\ViewOrm
	 * @RelatedKey num_parent
	 */
	protected $parent_view = null;

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;
	
	protected static $relations = array();
}