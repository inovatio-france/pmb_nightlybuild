<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SourceMaker.php,v 1.13 2024/08/01 08:48:35 dgoron Exp $

namespace Pmb\ImportExport\Models\Sources;

use Pmb\ImportExport\Models\ImportExportRoot;
use Pmb\ImportExport\Models\Chunks\Chunk;

use Pmb\Common\Helper\Helper;


class SourceMaker extends ImportExportRoot
{
    protected static $transformersManifests = null;
    protected static $sourcesTypes = null;

    public $id = 0;
    public $idSource = 0;
    public $sourceName = '';
    public $sourceComment = '';
    public $sourceType = '';
    public $sourceSettings = null;
    public $numScenario = 0;

    protected $ormName = "Pmb\ImportExport\Orm\SourceOrm";

    protected $source = null;
    protected $caller = null;


    /**
     * Constructeur
     *
     * @param integer $id : id source
     * @param [type] $caller : object appellant
     * @param boolean $runMode : mode execution
     */
    public function __construct(int $id = 0, $caller = null, bool $runMode = false)
    {
        parent::__construct($id);
        $this->caller = $caller;

        if ($id && $runMode) {
            $this->instantiateSource();
        }
    }

    /**
     * Instanciation source
     *
     * @return void
     */
    protected function instantiateSource()
    {
        $sourceClass = $this->sourceType ?? '';

        if ($sourceClass && class_exists($sourceClass)) {
            $sourceObject = new $sourceClass($this->id);
            $sourceSettings = Helper::toArray($this->sourceSettings);
            $sourceObject->setBaseParameters($sourceSettings);
            $this->source = $sourceObject;

            $this->instantiateTransformers();
            $this->instantiateChunk();
            
        }
    }

    public function getSource()
    {
        return $this->source;
    }

    protected function getRDFChunkClass() {
        $chunkClass = $this->sourceSettings->entryFormat ?? '';
        $transformers = $this->source->getTransformers();
        if (!empty($transformers)) {
            $chunksTypes = chunk::getChunksList();
            foreach ($transformers as $transformer) {
                if (!empty($transformer->getSettings()['outFormat'])) {
                    foreach ($chunksTypes as $chunkType) {
                        if ($chunkType['format'] == $transformer->getSettings()['outFormat']) {
                            $chunkClass = $chunkType['namespace'];
                        }
                    }
                }
            }
        }
        return $chunkClass;
    }
    
    /**
     * Instanciation du Chunk de la source
     *
     * @return void
     */
    protected function instantiateChunk()
    {
        if (empty($this->sourceSettings->entryFormat)) {
            return;
        }
        $chunkClass = $this->sourceSettings->entryFormat ?? '';
        $chunkSettings = Helper::toArray($this->sourceSettings->{$this->sourceSettings->entryFormat} ?? []);
        $resource = null;
        if (class_exists($chunkClass)) {
            $chunkObject = new $chunkClass($resource, $chunkSettings);
            $this->source->setChunk($chunkObject);

            $rdfChunkClass = $this->getRDFChunkClass();
            $this->instantiateRDFTransformer($rdfChunkClass);
        }
    }

    /**
     * Instanciation du RDFTransformer de la source
     *
     * @param string $chunkClass : namespace Chunk
     * @return void
     */
    protected function instantiateRDFTransformer(string $chunkClass = '')
    {
        $RDFTransformerClass = '';
        $chunksTypes = chunk::getChunksList();
        foreach ($chunksTypes as $chunkType) {
            if ($chunkType['namespace'] == $chunkClass) {
                $RDFTransformerClass = $chunkType['rdfTransformer'];
                break;
            }
        }
        if ($RDFTransformerClass && class_exists($RDFTransformerClass)) {
            $RDFTransformerObject = new $RDFTransformerClass($this->id);
            $this->source->setRDFTransformer($RDFTransformerObject);
        }
    }

    /**
     * Instanciation des transformers de la source
     *
     * @return void
     */
    protected function instantiateTransformers()
    {
        if (empty($this->sourceSettings->transformers)) {
            return;
        }

        foreach ($this->sourceSettings->transformers as $transformer) {

            $transformerClass = $transformer->namespace ?? '';
            $transformerSettings =  Helper::toArray($transformer->settings ?? []);
            if ($transformerClass && class_exists($transformerClass)) {
                $this->source->addTransformer(new $transformerClass($transformerSettings));
            }
        }
    }

    public function setFromForm(object $data)
    {
        $this->sourceName = $data->sourceName ?? '';
        $this->sourceComment = $data->sourceComment ?? '';
        $this->sourceType = $data->sourceType ?? '';
        $this->sourceSettings = $data->sourceSettings ?? null;
    }

    public function save()
    {
        $orm = new $this->ormName($this->id);

        $orm->source_name = $this->sourceName;
        $orm->source_comment = $this->sourceComment;
        $orm->source_type = $this->sourceType;
        $orm->num_scenario = $this->numScenario;
        $orm->source_settings = \encoding_normalize::json_encode($this->sourceSettings);
        $orm->save();
        if (!$this->id) {
            $this->id = $orm->id_source;
        }
        return $orm;
    }

    public function remove()
    {
        $orm = new $this->ormName($this->id);
        try {
            $orm->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function duplicate()
    {
        $newSource = clone $this;

        $newSource->id = 0;
        $newSource->sourceName .= " - copy";
        $newSource->save();

        return $newSource;
    }

    public function iterate()
    {
    }
}
