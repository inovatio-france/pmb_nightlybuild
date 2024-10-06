<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProfileImportEntityOrm.php,v 1.1 2024/07/26 11:45:37 dgoron Exp $

namespace Pmb\ImportExport\Orm;

use Pmb\Common\Orm\Orm;

class ProfileImportEntityOrm extends Orm
{

    /**
     * Prefix des champs de la table
     */
    public const PREFIX = "entity";
    
	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "import_export_profiles_import_entities";
	
	/**
	 * 
	 * @var string
	 */
	public static $idTableName = "id_entity";

	/**
	 * 
	 * @var integer
	 */
	protected $id_entity = 0;
	
	/**
	 *
	 * @var string
	 */
	protected $entity_type = '';
	
	/**
	 *
	 * @var string
	 */
	protected $entity_settings = '';
	
	/**
	 *
	 * @var integer
	 */
	protected $num_profile = 0;

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;
}