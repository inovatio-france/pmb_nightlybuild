<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProfileImportOrm.php,v 1.2 2024/07/26 11:45:37 dgoron Exp $

namespace Pmb\ImportExport\Orm;

use Pmb\Common\Orm\Orm;

class ProfileImportOrm extends Orm
{

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "import_export_profiles_import";
	
	/**
	 * 
	 * @var string
	 */
	public static $idTableName = "id_profile";

	/**
	 * 
	 * @var integer
	 */
	protected $id_profile = 0;
	
	/**
	 *
	 * @var string
	 */
	protected $profile_name = '';
	
	/**
	 *
	 * @var string
	 */
	protected $profile_comment = '';
	
	/**
	 *
	 * @var string
	 */
	protected $profile_type = '';
	
	/**
	 *
	 * @var string
	 */
	protected $profile_settings = '';

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;
}