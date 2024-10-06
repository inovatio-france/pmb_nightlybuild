<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_controller.class.php,v 1.4 2024/10/04 13:03:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/indexation_stack.class.php");

class indexation_controller extends lists_controller {
	
// 	protected static $model_class_name = 'indexation_model';
	
	protected static $list_ui_class_name = 'list_indexation_ui';
	
	public static function proceed($id=0) {
		global $action;
		
		$id = intval($id);
		switch ($action) {
// 			case 'list_manual_indexation':
// 				$list_ui_class_name = static::$list_ui_class_name;
// 				$list_ui_class_name::run_action_list('manual_indexation');
// 				static::redirect_display_list();
// 				break;
			default:
				parent::proceed($id);
				break;
		}
	}
	
	protected static function get_list_ui_instance($filters=array(), $pager=array(), $applied_sort=array()) {
	    global $name;
	    global $field;
	    global $id;
	    
	    $entity_type = 0;
	    if (!empty($name)) {
	        switch ($name) {
	            case 'notices':
	                $entity_type = TYPE_NOTICE;
	                break;
	            default:
	                $const = authority::get_const_type_object($name);
	                if (array_search($const, authority::$type_table) !== false) {
	                    $entity_type = array_search($const, authority::$type_table);
	                }
	                break;
	        }
	    }
	    $field = intval($field);
	    $id = intval($id);
	    switch (static::$list_ui_class_name) {
	        case 'list_indexation_fields_ui':
	            global $indexation_fields_ui_entity_type;
	            if (!empty($indexation_fields_ui_entity_type)) {
	                $entity_type = $indexation_fields_ui_entity_type;
	                list_indexation_fields_ui::set_indexation_name(entities::get_string_from_const_type($entity_type));
	                
	            } else {
	                list_indexation_fields_ui::set_indexation_name($name);
	            }
	            return new static::$list_ui_class_name(array('entity_type' => $entity_type));
	        case 'list_indexation_entities_ui':
	            global $indexation_entities_ui_entity_type;
	            if (!empty($indexation_entities_ui_entity_type)) {
	                $entity_type = $indexation_entities_ui_entity_type;
	                list_indexation_entities_ui::set_indexation_name(entities::get_string_from_const_type($entity_type));
	            } else {
	                list_indexation_entities_ui::set_indexation_name($name);
	            }
	            return new static::$list_ui_class_name(array('entity_type' => $entity_type, 'field' => $field, 'id' => $id));
	        default:
	            return new static::$list_ui_class_name();
	    }
	}
}