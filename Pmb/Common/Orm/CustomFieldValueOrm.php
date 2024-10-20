<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CustomFieldValueOrm.php,v 1.10 2024/10/07 15:27:58 tsamson Exp $

namespace Pmb\Common\Orm;

use Pmb\Common\Helper\Helper;

abstract class CustomFieldValueOrm extends Orm
{
    /**
     *
     * @var integer
     */
    protected $champ = 0;
    
    /**
     *
     * @var integer
     */
    protected $origine = 0;
    
    /**
     *
     * @var string
     */
    protected $small_text = "";
    
    /**
     *
     * @var string
     */
    protected $text = "";
    
    /**
     *
     * @var integer
     */
    protected $integer = 0;
    
    /**
     *
     * @var \DateTime
     */
    protected $date = "";
    
    /**
     *
     * @var string
     */
    protected $float = "";
    
    /**
     *
     * @var integer
     */
    protected $order = 0;
    
    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = [];
    
    // On d�rive car on n'a pas de cl� primaire
    public function delete()
    {
        return false;
    }

    /**
     *
     * @return array
     */
    public static function find($field, $value, $orderby = '')
    {
        $query = "SELECT * FROM " . static::$tableName ." WHERE $field = $value" . ($orderby ? " ORDER BY $orderby": '');
        $result = pmb_mysql_query($query);
        $instances = array();
        if (pmb_mysql_num_rows($result)) {
            foreach ($result as $row) {
                $className = static::class;
                $instance = new $className(0);
                $instance->feedObject($row);
                $instances[] = $instance;
            }
        }
        return $instances;
    }

    /**
     *
     * @return array
     */
    public static function findAll()
    {
        $query = "SELECT * FROM " . static::$tableName;
        $result = pmb_mysql_query($query);
        $instances = array();
        if (pmb_mysql_num_rows($result)) {
            $className = static::class;
            foreach ($result as $row) {
                $instance = new $className(0);
                $instance->feedObject($row);
                $instances[] = $instance;
            }
        }
        return $instances;
    }

    /**
     *
     * @param array $data
     * @return \Pmb\Common\Orm\Orm
     */
    public function feedObject(array $data)
    {
        foreach ($data as $key => $value) {
            $rowField = str_replace(static::$tablePrefix . '_custom_', '', $key);
            $this->{$rowField} = $value;
        }
        return $this;
    }
    
    public function __get($label)
    {
        $label = str_replace(static::$tablePrefix . '_custom_', '', $label);
        
        if (static::$reflectionClass[static::class]->hasMethod(Helper::camelize("get " . $label))) {
            return $this->{Helper::camelize("get " . $label)}();
        }
        
        if (in_array($label, array_keys(static::$relations[static::class]))) {
            return $this->getRelated($label);
        }
        if (static::$reflectionClass[static::class]->hasProperty($label)) {
            return $this->{$label};
        }
        throw new \Exception("Unknown property");
    }
    
}