<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ParserXML.php,v 1.1 2024/06/21 14:25:20 rtigero Exp $

namespace Pmb\Common\Library\Parser;

class ParserXML
{
    protected $xml = '';

    protected $error = false;

    protected $error_msg = [];

    protected $result = array();

    protected $rootElement = "";

    public function loadFromXmlFile($file_path = '')
    {
        if (is_readable($file_path) && filesize($file_path)) {
            $this->xml = file_get_contents($file_path, 'r');
            if ($this->xml) {
                $this->parse();
            }
        } else {
            $this->error = true;
            $this->error_msg[] = sprintf('Erreur lors de la lecture du fichier : \"%s\"', $file_path);
        }
    }

    public function loadFromXmlString($xml = '')
    {
        $this->xml = $xml;
        if ($this->xml) {
            $this->parse();
        }
    }

    protected function parse()
    {
        $vals = array();
        $index = array();

        if ($this->xml) {
            $simple = $this->xml;
            $rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";

            if (preg_match($rx, $simple, $m)) {
                $encoding = strtoupper($m[1]);
            } else {
                $encoding = "UTF-8";
            }

            //encodages supportés par les fonctions suivantes
            if (($encoding != "ISO-8859-1") && ($encoding != "UTF-8") && ($encoding != "US-ASCII")) {
                $encoding = "UTF-8";
            }
            $p = xml_parser_create($encoding);
            xml_parser_set_option($p, XML_OPTION_TARGET_ENCODING, $encoding);
            xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
            xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 1);
            if (xml_parse_into_struct($p, $simple, $vals, $index) == 1) {

                // Libération de la mémoire
                xml_parser_free($p);

                $param = array();
                $tag_count = array();
                $indice = 0;

                $this->recursive($indice, 1, $param, $tag_count, $vals);
            } else {
                $this->error = true;
                $this->error_msg[] = xml_error_string(xml_get_error_code($p)) . " " . xml_get_current_line_number($p);
            }

            $p = null;
            unset($vals, $index);
            if (isset($param) && is_array($param)) {
                if ($this->rootElement) {
                    if (count($param[$this->rootElement]) != 1) {
                        $this->error = true;
                        $this->error_msg[] = "Erreur, ceci n'est pas un fichier {$this->rootElement} !";
                        exit;
                    }
                    $this->result = $param[$this->rootElement][0];
                } else {
                    $this->result = $param;
                }
            }
        }
        return $this->result;
    }

    protected function recursive(&$indice, $niveau, &$param, &$tag_count, &$vals)
    {
        $nb_vals = count($vals);
        if ($indice > $nb_vals) {
            exit;
        }

        while ($indice < $nb_vals) {

            $val = $vals[$indice];
            $indice++;

            if (!isset($tag_count[$val["tag"]])) {
                $tag_count[$val["tag"]] = 0;
            } else {
                $tag_count[$val["tag"]]++;
            }

            if (isset($val["attributes"])) {
                $attributs = $val["attributes"];
                foreach ($attributs as $key_att => $val_att) {
                    $param[$val["tag"]][$tag_count[$val["tag"]]][$key_att] = $val_att;
                }
            }

            if ($val["type"] == "open") {
                $tag_count_next = array();
                $this->recursive($indice, $niveau + 1, $param[$val["tag"]][$tag_count[$val["tag"]]], $tag_count_next, $vals);
            }

            if ($val["type"] == "close" && $niveau > 2) {
                break;
            }

            if ($val["type"] == "complete") {
                if (isset($val["value"])) {
                    $param[$val["tag"]][$tag_count[$val["tag"]]]["value"] = $val["value"];
                } else {
                    $param[$val["tag"]][$tag_count[$val["tag"]]]["value"] = '';
                }
            }
        }
    }

    public function setRootElement($rootElement)
    {
        $this->rootElement = $rootElement;
    }

    public function getResult()
    {
        return $this->result;
    }
}
