<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: custom_fields.class.php,v 1.2 2024/02/12 15:05:52 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class custom_fields {
	
    protected static function get_chk_values($name) {
        global ${$name};
        return ${$name};
    }
    
    protected static function has_chk_mandatory() {
        return false;
    }
    
    public static function chk($field,&$check_message) {
        global $msg;
        
        $name=$field['NAME'];
        global ${$name};
        $val=static::get_chk_values($name);
        
        if(static::has_chk_mandatory()) {
            if ($field['MANDATORY']==1) {
                if ((!count($val))||((count($val)==1)&&($val[0]==""))) {
                    $check_message=sprintf($msg["parperso_field_is_needed"],$field['ALIAS']);
                    return 0;
                }
            }
        }
        
        $check_datatype_message = "";
        $val_1 = chk_datatype($field, $val, $check_datatype_message);
        if (!empty($check_datatype_message)) {
            $check_message = $check_datatype_message;
            return 0;
        }
        ${$name} = $val_1;
        return 1;
    }
    
//     public static function val($field, $value) {
        
//     }
    
    public static function get_formatted_label_aff_filter($label) {
        return $label;
    }
        
    public static function aff_filter($field,$varname,$multiple) {
        global $charset;
        
        $ret="<select id=\"".$varname."\" name=\"".$varname;
        $ret.="[]";
        $ret.="\" ";
        if ($multiple) $ret.="size=5 multiple";
        $ret.=" data-form-name='".$varname."' >\n";
        
        $values=$field['VALUES'];
        if ($values=="") $values=array();
        $options=$field['OPTIONS'][0];
        if (($options['UNSELECT_ITEM'][0]['VALUE']!="")||($options['UNSELECT_ITEM'][0]['value']!="")) {
            $ret.="<option value=\"".htmlentities($options['UNSELECT_ITEM'][0]['VALUE'],ENT_QUOTES,$charset)."\"";
            if ($options['UNSELECT_ITEM'][0]['VALUE']==$options['DEFAULT_VALUE'][0]['value']) $ret.=" selected";
            $ret.=">".htmlentities($options['UNSELECT_ITEM'][0]['value'],ENT_QUOTES,$charset)."</option>\n";
        }
        $resultat=pmb_mysql_query($options['QUERY'][0]['value']);
        while ($r=pmb_mysql_fetch_row($resultat)) {
            $ret.="<option value=\"".htmlentities($r[0],ENT_QUOTES,$charset)."\"";
            $as=array_search($r[0],$values);
            if (($as!==FALSE)&&($as!==NULL)) $ret.=" selected";
            $ret.=">".htmlentities(static::get_formatted_label_aff_filter($r[0]),ENT_QUOTES,$charset)."</option>\n";
            
        }
        $ret.= "</select>\n";
        return $ret;
    }
    
}