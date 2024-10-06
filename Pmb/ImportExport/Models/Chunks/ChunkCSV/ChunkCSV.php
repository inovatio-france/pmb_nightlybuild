<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ChunkCSV.php,v 1.6 2024/08/07 07:07:55 dbellamy Exp $

namespace Pmb\ImportExport\Models\Chunks\ChunkCSV;

use encoding_normalize;
use Pmb\ImportExport\Models\Chunks\Chunk;
use Pmb\ImportExport\Models\Chunks\ChunkInterface;

class ChunkCSV extends Chunk implements ChunkInterface
{

    protected $headerLine = 0;
    protected $headers = [];

    protected function initialize()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;

            $this->initializeStream();

            $this->headerLine = intval($this->parameters["headerline"] ?? 0);
        }
    }


    public function next()
    {
        $this->initialize();

        $resource = fopen($this->uri, $this->mode, false, $this->context);
        if (!$resource) {
            return;
        }

        if($this->headerLine) {
            $i = max(0,$this->headerLine - 1);
            while (!feof($resource) &&  $i) {
                fgets($resource);
            }

            if(!feof($resource)) {
                $row = fgetcsv($resource, null, $this->parameters["separator"], $this->parameters["enclosure"]);
                if(is_array($row)) {
                    array_walk($row, function (&$value, $key) {
                        $value = encoding_normalize::charset_normalize($value, encoding_normalize::detect_encoding($value, ["UTF-8", "ISO-8859-1", "ISO-8859-15", "cp1252"]));
                        $value = strip_empty_chars($value);
                        $value = preg_replace("/\s/", "_", $value);
                        if ($value == "") {
                            $value = "col_" . (intval($key) + 1);
                        }
                    });
                    $this->headers = $row;
                }
            }
        }

       $currentContent = [];

        while (!feof($resource)) {
            $row = fgetcsv($resource, null, $this->parameters["separator"], $this->parameters["enclosure"]);

            if ($row !== false) {
                if (count($this->headers) == count($row)) {
                    $currentContent = array_combine($this->headers, $row) ;
                } else {
                    $currentContent = $row;
                }
                yield $currentContent;
                $currentContent = [];
            }

        }

        fclose($resource);

    }

}
