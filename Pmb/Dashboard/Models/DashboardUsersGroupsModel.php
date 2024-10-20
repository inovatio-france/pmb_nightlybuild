<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DashboardUsersGroupsModel.php,v 1.3 2024/01/31 10:17:12 jparis Exp $

namespace Pmb\Dashboard\Models;

use Pmb\Common\Models\Model;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class DashboardUsersGroupsModel extends Model
{
    protected $ormName = "Pmb\Dashboard\Orm\DashboardUsersGroupsOrm";

    public $numDashboard = 0;
    public $numUsersGroups = 0;

	public function __construct(int $numDashboard = 0, int $numUsersGroups = 0)
	{
		$this->numDashboard = $numDashboard;
		$this->numUsersGroups = $numUsersGroups;

		$this->fetchData();
	}

	/**
	 * Cr�e un nouveau DashboardUsersGroups dans la base de donn�es.
	 * 
	 * @return void
	 */
	public function create()
	{
		$orm = new $this->ormName();

		$orm->num_dashboard = $this->numDashboard;
		$orm->num_users_groups = $this->numUsersGroups;
		
		$orm->save();
	}

	/**
	 * Supprime les DashboardUsersGroups par l'id du dashboard.
	 * 
	 * @return void
	 */
	public function deleteByIdDashboard()
	{
		$this->ormName::deleteWhere("num_dashboard", $this->numDashboard);
	}

	/**
     * D�finit les propri�t�s de l'objet � partir des donn�es du formulaire.
     *
     * @param mixed $data Les donn�es du formulaire � d�finir
     */
    public function setFromForm($data)
    {
		$this->numUsersGroups = $data;
    }
}

