<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_contribution_datatype_text_ui.class.php,v 1.6 2023/08/28 14:04:12 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path.'/templates/onto/contribution/onto_contribution_datatype_ui.tpl.php');

/**
 * class onto_contribution_datatype_text_ui
 */
class onto_contribution_datatype_text_ui extends onto_common_datatype_ui {

	/**
	 * 
	 * @param string $item_uri
	 * @param onto_common_property $property la propri�t� concern�e
	 * @param onto_restriction $restrictions le tableau des restrictions associ�es � la propri�t� 
	 * @param array $datas le tableau des datatypes
	 * @param string $instance_name nom de l'instance
	 * @param string $flag Flag
	 * @return string
	 */
	public static function get_form($item_uri, $property, $restrictions, $datas, $instance_name,$flag) {
	    global $charset, $ontology_contribution_tpl, $msg;

		$form=$ontology_contribution_tpl['form_row'];
		$form=str_replace("!!onto_row_label!!",htmlentities(encoding_normalize::charset_normalize($property->get_label(), 'utf-8') ,ENT_QUOTES,$charset) , $form);
		
		$tab_lang = array();
		if ($property->multilingue && $property->is_cp()) {
		    // Champ perso multilingue
		    $lang = new marc_list('lang');
		    if (!empty($lang->table)) {
		        $tab_lang = $lang->table;
		    }
		    // Sans langue
		    $tab_lang[''] = htmlentities($msg['onto_common_datatype_ui_no_lang'], ENT_QUOTES, $charset);
		}

		if ($property->multilingue && $property->use_lang_concept) {
		    $tab_lang =  onto_common_datatype_ui::get_concepts_liste_trad();
		}
		
		$content='';
		if (!empty($datas)) {
			$i=1;
			$first=true;
			$new_element_order=max(array_keys($datas));
			
			$form=str_replace("!!onto_new_order!!",$new_element_order , $form);
			
			foreach($datas as $key=>$data){
				$row=$ontology_contribution_tpl['form_row_content'];
				
				if($data->get_order()){
					$order=$data->get_order();
				}else{
					$order=$key;
				}
				$inside_row=$ontology_contribution_tpl['form_row_content_text'];
				
				$inside_row=str_replace("!!onto_row_content_text_value!!",htmlentities(encoding_normalize::utf8_decode($data->get_formated_value()) ,ENT_QUOTES,$charset) ,$inside_row);
				
				$multilingue = "";
				if ($property->multilingue) {
				    $multilingue = self::get_combobox_lang($instance_name.'_'.$property->pmb_name.'['.$order.'][lang]',$instance_name.'_'.$property->pmb_name.'_'.$order.'_lang',$data->get_lang(), 1, '', $tab_lang);
				} 
				$inside_row=str_replace("!!onto_row_combobox_lang!!", $multilingue, $inside_row);
				$inside_row=str_replace("!!onto_row_content_text_range!!",$property->range[0] , $inside_row);
				
				$row=str_replace("!!onto_inside_row!!",$inside_row , $row);
				
				$input='';
				if($first){
					if($restrictions->get_max() < $i || $restrictions->get_max()===-1){
						$input=$ontology_contribution_tpl['form_row_content_input_add'];
					}
				}else{
					$input=$ontology_contribution_tpl['form_row_content_input_del'];
				}
				
				$row=str_replace("!!onto_row_inputs!!",$input , $row);
				$row=str_replace("!!onto_row_order!!",$order , $row);
				
				$content.=$row;
				$first=false;
				$i++;
			}
		}else{
			$form=str_replace("!!onto_new_order!!","0" , $form);
			
			$row=$ontology_contribution_tpl['form_row_content'];
			
			$inside_row=$ontology_contribution_tpl['form_row_content_text'];
			
			$inside_row=str_replace("!!onto_row_content_text_value!!","" ,$inside_row);
			$multilingue = "";
			if ($property->multilingue) {
			    $multilingue = self::get_combobox_lang($instance_name.'_'.$property->pmb_name.'[0][lang]',$instance_name.'_'.$property->pmb_name.'_0_lang', "", 1, '', $tab_lang);
			}
			$inside_row=str_replace("!!onto_row_combobox_lang!!", $multilingue, $inside_row);
			$inside_row=str_replace("!!onto_row_content_text_range!!",$property->range[0] , $inside_row);
			
			$row=str_replace("!!onto_inside_row!!",$inside_row , $row);
			$input='';
			if($restrictions->get_max()!=1){
				$input=$ontology_contribution_tpl['form_row_content_input_add'];
			}
			$row=str_replace("!!onto_row_inputs!!",$input , $row);
			
			$row=str_replace("!!onto_row_order!!","0" , $row);
			
			$content.=$row;
		}
		
		$form=str_replace("!!onto_rows!!",$content ,$form);
		$form = str_replace("!!onto_row_scripts!!", static::get_scripts(), $form);
		$form = self::get_form_with_special_properties($property, $datas, $instance_name, $form);
		$form=str_replace("!!onto_row_id!!",$instance_name.'_'.$property->pmb_name , $form);
		
		return $form;
	} // end of member function get_form

	/**
	 * 
	 *
	 * @param onto_common_datatype datas Tableau des valeurs � afficher associ�es � la propri�t�
	 * @param property property la propri�t� � utiliser
	 * @param string instance_name nom de l'instance
	 * 
	 * @return string
	 * @access public
	 */
	public function get_display($datas, $property, $instance_name) {
		
		$display='<div id="'.$instance_name.'_'.$property->pmb_name.'">';
		$display.='<p>';
		$display.=$property->get_label().' : ';
		foreach($datas as $data){
			$display.=$data->get_formated_value();
		}
		$display.='</p>';
		$display.='</div>';
		return $display;
		
	} // end of member function get_display

} // end of onto_common_datatype_ui