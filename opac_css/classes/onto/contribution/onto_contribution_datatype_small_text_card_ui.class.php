<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_contribution_datatype_small_text_card_ui.class.php,v 1.2 2023/07/26 12:12:58 dbellamy Exp $
if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/**
 * class onto_contribution_datatype_small_text_card_ui
 */
class onto_contribution_datatype_small_text_card_ui extends onto_common_datatype_small_text_card_ui
{

    private static $default_type = "http://www.w3.org/2000/01/rdf-schema#Literal";

    /**
     *
     * @param onto_property $property la propri�t� concern�e
     * @param onto_restriction $restrictions le tableau des restrictions associ�es � la propri�t�
     * @param array $datas le tableau des datatypes
     * @param string $instance_name nom de l'instance
     * @param string $flag Flag
     * @return string
     */
    public static function get_form($item_uri, $property, $restrictions, $datas, $instance_name, $flag)
    {
        global $msg, $charset, $ontology_tpl, $msg;

        // $tab_lang=array(0=>$msg["onto_common_datatype_ui_no_lang"],'fr'=>$msg["onto_common_datatype_ui_fr"],'en'=>$msg["onto_common_datatype_ui_en"]);
        $max = $restrictions->get_max();

        $form = $ontology_tpl['form_row_card'];
        $form = str_replace("!!onto_row_label!!", htmlentities(encoding_normalize::charset_normalize($property->get_label(), 'utf-8'), ENT_QUOTES, $charset), $form);
        $form = str_replace("!!onto_input_type!!", htmlentities(self::$default_type, ENT_QUOTES, $charset), $form);

        $tab_lang = array();
        if ($property->multilingue && $property->is_cp()) {
            // Champ perso multilingue
            $lang = new marc_list('lang');
            if (! empty($lang->table)) {
                $tab_lang = $lang->table;
            }
            // Sans langue
            $tab_lang[''] = htmlentities($msg['onto_common_datatype_ui_no_lang'], ENT_QUOTES, $charset);
        }

        $content = '';
        $multilingue = "";

        if ( !empty($datas) && is_array($datas) ) {
            $i = 1;
            $first = true;
            $new_element_order = max(array_keys($datas));

            $form = str_replace("!!onto_new_order!!", $new_element_order, $form);

            foreach ($datas as $key => $data) {

                $row = $ontology_tpl['form_row_content'];

                if ($data->get_order()) {
                    $order = $data->get_order();
                } else {
                    $order = $key;
                }
                $inside_row = $ontology_tpl['form_row_content_small_text_card'];
                $inside_row .= $ontology_tpl['form_row_content_type'];

                $inside_row = str_replace("!!onto_row_content_small_text_value!!", htmlentities($data->get_formated_value(), ENT_QUOTES, $charset), $inside_row);
                $multilingue = "";
                if ($property->multilingue) {
                    $multilingue = self::get_combobox_lang($instance_name . '_' . $property->pmb_name . '[' . $order . '][lang]', $instance_name . '_' . $property->pmb_name . '_' . $order . '_lang', $data->get_lang(), 1, '', $tab_lang);
                }
                $inside_row = str_replace("!!onto_row_combobox_lang!!", $multilingue, $inside_row);
                $inside_row = str_replace("!!onto_row_content_range!!", $property->range[0], $inside_row);

                $row = str_replace("!!onto_inside_row!!", $inside_row, $row);

                $input = '';
                if ($first) {
                    if ($restrictions->get_max() < $i || $restrictions->get_max() === - 1) {
                        $input = $ontology_tpl['form_row_content_input_add'];
                    }
                } else {
                    $input = $ontology_tpl['form_row_content_input_del'];
                }

                $row = str_replace("!!onto_row_inputs!!", $input, $row);

                $row = str_replace("!!onto_row_order!!", $order, $row);

                $content .= $row;
                $first = false;
                $i ++;
            }
        } else {
            $form = str_replace("!!onto_new_order!!", "0", $form);

            // Un champ sans langue par d�faut
            $row = $ontology_tpl['form_row_content'];
            $inside_row = $ontology_tpl['form_row_content_small_text_card'];
            $inside_row .= $ontology_tpl['form_row_content_type'];

            $inside_row = str_replace("!!onto_row_content_small_text_value!!", "", $inside_row);
            $multilingue = "";
            if ($property->multilingue) {
                $multilingue = self::get_combobox_lang($instance_name . '_' . $property->pmb_name . '[0][lang]', $instance_name . '_' . $property->pmb_name . '_0_lang', "", 1, '', $tab_lang);
            }
            $inside_row = str_replace("!!onto_row_combobox_lang!!", $multilingue, $inside_row);
            $inside_row = str_replace("!!onto_row_content_range!!", $property->range[0], $inside_row);
            $row = str_replace("!!onto_inside_row!!", $inside_row, $row);

            $input = '';
            if ($restrictions->get_max() != 1) {
                $input = $ontology_tpl['form_row_content_input_add'];
            }
            $row = str_replace("!!onto_row_inputs!!", $input, $row);

            $row = str_replace("!!onto_row_inputs!!", $input, $row);
            $row = str_replace("!!onto_row_order!!", "0", $row);
            $content .= $row;
        }

        $onto_rows = "";
        $onto_rows .= $content;

        $form = str_replace("!!input_add!!", $ontology_tpl['form_row_content_input_add_card'], $form);
        $form = str_replace("!!onto_row_max_card!!", $max, $form);
        $form = str_replace("!!onto_rows!!", $onto_rows, $form);
        $form = str_replace("!!onto_row_scripts!!", static::get_scripts(), $form);
        $form = self::get_form_with_special_properties($property, $datas, $instance_name, $form);
        $form = str_replace("!!onto_row_id!!", $instance_name . '_' . $property->pmb_name, $form);

        return $form;
    }
}