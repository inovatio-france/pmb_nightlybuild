<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frbr_sort_fields.class.php,v 1.5 2020/03/06 15:50:33 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once "$class_path/fields/sort_fields.class.php";

class frbr_sort_fields extends sort_fields {
    protected function get_id_authperso() {
        if (!empty($this->details["authperso_id"])) {
            return $this->details["authperso_id"];
        }
        global $num_page, $object_id, $elem, $element;
        if (isset($object_id)) {
            if (!is_object($element)) {
                $element = new $elem($object_id);
            }
            $object = $element;
            if (method_exists($object, "get_num_datanode")) {
                $object = new frbr_entity_authperso_datanode($element->get_num_datanode());
            }
            if (!empty($object->get_datasource()['data']->authperso_id)) {
                return $object->get_datasource()['data']->authperso_id;
            }
        }
        if (!empty($num_page) && intval($num_page)) {
            $frbr_page = new frbr_page($num_page);
            return $frbr_page->get_parameter_value("authperso");
        }
        return 0;
    }
}