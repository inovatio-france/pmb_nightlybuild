<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: TransformerPMB.php,v 1.4 2024/08/01 08:48:35 dgoron Exp $

namespace Pmb\ImportExport\Models\Transformers\TransformerPMB;

use Pmb\ImportExport\Models\Transformers\Transformer;

class TransformerPMB extends Transformer
{

    public function transform($inEntity)
    {
        if (!empty($this->settings['converterFunction'])) {
            $indexConverterFunction = static::getIndexConverterFunction($this->settings['converterFunction']);
            if ($indexConverterFunction !== false) {
                $convert = new \convert($inEntity, $indexConverterFunction);
                $convert->transform();
                return $convert->output_notice;
            }
        }
        return $inEntity;
    }

    public static function pmbFunctions()
    {
        global $base_path;

        $functions = array();
        //Lecture des différents imports possibles
        if (file_exists($base_path . "/admin/convert/imports/catalog_subst.xml")) {
            $fic_catal = $base_path . "/admin/convert/imports/catalog_subst.xml";
        } else {
            $fic_catal = $base_path . "/admin/convert/imports/catalog.xml";
        }
        $parser = _parser_text_no_function_(file_get_contents($fic_catal), "CATALOG");
        foreach ($parser['ITEM'] as $item) {
            if (!isset($item['visible']) || $item['visible'] != 'no') {
                $functions[] = ['value' => $item['PATH'], 'label' => $item['NAME']];
            }
        }
        return $functions;
    }
    
    protected static function getIndexConverterFunction($name) 
    {
        $functions = static::pmbFunctions();
        foreach ($functions as $index=>$function) {
            if ($function['value'] == $name) {
                return $index;
            }
        }
        return false;
    }
}
