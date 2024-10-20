<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ChunkXML.php,v 1.9 2024/08/07 07:07:55 dbellamy Exp $

namespace Pmb\ImportExport\Models\Chunks\ChunkXML;

use XMLReader;
use Pmb\ImportExport\Models\Chunks\Chunk;
use Pmb\ImportExport\Models\Chunks\ChunkInterface;

class ChunkXML extends Chunk implements ChunkInterface
{

    /**
     * Element racine
     * @var string
     */
    protected $rootElement = "";

    /**
     * Tableau de tags à lire (tag|chemin)
     * @var array
     */
    protected $entities = [];

    /**
     * object XMLReader
     * @var XMLReader
     */
    protected $xmlReader = null;

    /**
     * Chemin du noeud courant
     * @var array
     */
    protected $currentNodePath = [];

    /**
     * Entete XML de sortie
     * @var string
     */
    const DEFAULT_XML_HEADER = '<?xml version="1.0" encoding="utf-8"?>' . "\n";

    protected $xmlHeader = '<?xml version="1.0" encoding="utf-8"?>' . "\n";


    protected function initialize()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;

            $this->initializeStream();
            if ($this->context) {
                libxml_set_streams_context($this->context);
            }

            $this->xmlReader = new \XMLReader();
            $this->rootElement = $this->parameters["rootElement"] ?? '';
            if (count($this->parameters['entitiesElements'])) {
                foreach ($this->parameters['entitiesElements'] as $v) {
                    $this->entities[] = $v['value'];
                }
            }
            $this->xmlHeader = $this->parameters["xmlHeader"] ?? static::DEFAULT_XML_HEADER;
        }
    }


    public function next()
    {
        $this->initialize();

        $opened = $this->xmlReader->open($this->uri, null, LIBXML_NOBLANKS);
        if (!$opened) {
            return;
        }

        $currentContent = '';

        while ($this->xmlReader->read()) {

            if ($this->xmlReader->nodeType === XMLReader::ELEMENT) {
                array_push($this->currentNodePath, $this->xmlReader->name);
            }
            if ($this->xmlReader->nodeType === XMLReader::END_ELEMENT) {
                array_pop($this->currentNodePath);
            }

            $path = '/' . implode('/', $this->currentNodePath);
            $name = $this->xmlReader->name;

            if ($this->xmlReader->nodeType === XMLReader::ELEMENT && in_array($name, $this->entities)) {
                $currentContent .= $this->xmlReader->readOuterXML();
                yield $this->xmlHeader . $currentContent;
                $currentContent = '';
            }
            if ($this->xmlReader->nodeType === XMLReader::ELEMENT && in_array($path, $this->entities)) {
                $currentContent .= $this->xmlReader->readOuterXML();
                yield $this->xmlHeader . $currentContent;
                $currentContent = '';
            }
        }

        $this->xmlReader->close();
    }
}
