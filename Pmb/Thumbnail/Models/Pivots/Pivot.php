<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Pivot.php,v 1.6 2022/12/15 09:34:58 tsamson Exp $
namespace Pmb\Thumbnail\Models\Pivots;

interface Pivot
{

    /**
     * Permet de savoir si la notice match avec ce pivot
     *
     * @param object $objectId
     * @return bool
     */
    public function check(object $entity);

    /**
     * Retourne les donn�es brutes d�finies en base
     *
     * @return mixed
     */
    public function getData();

    /**
     * Retourne le nom du pivot
     *
     * @return string
     */
    public function getName();

    /**
     * Retourne les donn�e format� pour le formulaire
     *
     * @return array
     */
    public function getDataForForm();

    /**
     * Permet d'aller chercher les infos dans l'entit�
     *
     * @param object $objectId
     */
    public function getPivotData(object $entity);

    /**
     * V�rifie et d�finis les propri�t�s de la classe avec les donn�es du formulaire
     *
     * @param string $pivot
     */
    public function setDataFromForm(object $pivot);
    
    /**
     * Permet de r�cup�rer et de formater les donn�es en base pour le formulaire
     * 
     * @return array
     */
    public static function getViewData();
}

