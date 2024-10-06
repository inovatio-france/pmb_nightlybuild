<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Source.php,v 1.20 2024/08/01 08:48:35 dgoron Exp $

namespace Pmb\ImportExport\Models\Sources;

use Pmb\Common\Helper\ParserMessage;
use Pmb\ImportExport\Models\Chunks\Chunk;
use Pmb\ImportExport\Models\Transformers\TransformerInterface;
use Pmb\ImportExport\Models\ImportExportParserDirectory;
use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class Source implements \Iterator
{
    use ParserMessage;

    protected static $transformersManifests = null;
    protected static $sourcesTypes = null;

    public $id = 0;
    public $sourceName = '';
    public $sourceComment = '';
    public $sourceType = '';
    public $sourceSettings = null;
    public $numScenario = 0;

    protected $RDFTransformer = null;
    protected $transformers = [];

    protected $outFormat;
    protected $chunk;
    protected $baseParameters;
    protected $contextParameters;

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public function setRDFTransformer(RDFTransformer $RDFTransformer)
    {
        $this->RDFTransformer = $RDFTransformer;
    }

    public function setChunk(Chunk $chunk)
    {
        $this->chunk = $chunk;
    }

    public function getChunk()
    {
        return $this->chunk;
    }
    
    public function getTransformers()
    {
        return $this->transformers;
    }

    public function addTransformer(TransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    public function current()
    {
        if (is_object($this->chunk)) {
            $chunk = $this->chunk->current();
            if (is_null($chunk)) {
                return null;
            }
            foreach ($this->transformers as $transformer) {
                $chunk = $transformer->transform($chunk);
            }
            return $chunk;
        }
    }

    public function key()
    {
        if (is_object($this->chunk)) {
            return $this->chunk->key();
        }
    }

    public function next(): void
    {
        if (is_object($this->chunk)) {
            $this->chunk->next();
        }
    }

    public function rewind(): void
    {
        if (is_object($this->chunk)) {
            $this->chunk->rewind();
        }
    }

    public function valid(): bool
    {
        if (is_object($this->chunk)) {
            $valid = $this->chunk->valid();
            if (!$valid) {
                $this->RDFTransformer->generateTriplesDescriptions();
                $this->getOntology();
            }
            return $valid;
        }
    }

    public function initSync()
    {
    }

    public function closeSync()
    {
    }

    public function getOntology()
    {
        $onto = $this->RDFTransformer->getStore()->getOntology();
        /*$propertyUri = $this->RDFTransformer->getStore()->getUri($this->RDFTransformer->getPrefix(), 'PMB_PROPERTY');
         $entityUri = $this->RDFTransformer->getStore()->getUri($this->RDFTransformer->getPrefix(), 'PMB_ENTITY');
         $property = $onto->getPropertyByName($propertyUri);
         $entity = $onto->getEntityByName($entityUri);
         $entity->addProperty($property);
         $property->addRange($entity);
         $onto->saveToStore();*/
        return $onto;
    }

    public function setBaseParameters(array $parameters)
    {
        $this->baseParameters = $parameters;
    }

    public function getBaseParameters()
    {
    }

    public function setContextParameters(array $parameters)
    {
        $this->contextParameters = $parameters;
    }

    public function getContextParameters()
    {
    }

    public function setNumScenario(int $numScenario)
    {
        $this->numScenario = $numScenario;
    }

    public function toTriples($entity)
    {
        $this->RDFTransformer->toTriples($entity);
    }

    /**
     * Retourne la liste des types de sources
     *
     * @return array
     */
    final public static function getSourcesTypes()
    {
        if (!is_null(static::$sourcesTypes)) {
            return static::$sourcesTypes;
        }

        static::$sourcesTypes = [];
        $parser = ImportExportParserDirectory::getInstance();
        $manifests = $parser->getManifests(str_replace('\\', '/', __NAMESPACE__));
        foreach ($manifests as $manifest) {
            if (!empty($manifest->type) && strpos($manifest->type, "Source") !== false) {
                if (method_exists($manifest->namespace, "getSourceType")) {
                    static::$sourcesTypes[] = $manifest->namespace::getSourceType();
                    continue;
                }
            }
        }
        return static::$sourcesTypes;
    }

    /**
     * Retourne le type de source
     * Peut se deriver pour certains types de sources
     *
     * @return array
     */
    public static function getSourceType()
    {
        $parser = ImportExportParserDirectory::getInstance();
        $manifest = $parser->getManifestByNamespace(static::class);
        $messages = $manifest->namespace::getMessages();
        $compatibility = $parser->getCompatibility($manifest->namespace);
        $compatiblesFormats = array();
        if (!empty($compatibility["chunk"])) {
            if (is_array($compatibility["chunk"])) {
                foreach ($compatibility["chunk"] as $chunk) {
                    $chunkManifest = $parser->getManifestByNamespace($chunk);

                    $compatiblesFormats[] = array(
                        "format" => $chunkManifest->format,
                        "namespace" => $chunkManifest->namespace,
                        "settings" => $chunkManifest->settings,
                        "msg" =>  $chunkManifest->namespace::getMessages(),
                        "transformers" => static::getCompatiblesTransformers($chunk)
                    );
                }
            } else {
                $chunkManifest = $parser->getManifestByNamespace($compatibility["chunk"]);
                $compatiblesFormats[] = array(
                    "format" => $chunkManifest->format,
                    "namespace" => $chunkManifest->namespace,
                    "settings" => $chunkManifest->settings,
                    "msg" =>  $chunkManifest->namespace::getMessages(),
                    "transformers" => static::getCompatiblesTransformers($compatibility["chunk"])
                );
            }
        }
        return array(
            'type' => $manifest->type,
            'namespace' => $manifest->namespace,
            'settings' => $manifest->settings,
            'contextParameters' => $chunkManifest->contextSettings,
            'msg' =>  $messages,
            'formats' => $compatiblesFormats
        );
    }

    /**
     * Retourne la liste des transformers compatibles avec le chunk passé en parametre
     *
     * @param string $chunk : Chunk namespace
     * @return array
     */
    public static function getCompatiblesTransformers($chunk)
    {
        if (is_null(static::$transformersManifests)) {

            static::$transformersManifests = [];

            $parser = ImportExportParserDirectory::getInstance();
            $transformersManifests = $parser->getManifests("Pmb/ImportExport/Models/Transformers");
            foreach ($transformersManifests as $transformerManifest) {
                $compatibility = $parser->getCompatibility($transformerManifest->namespace);
                if (!empty($compatibility["chunk"])) {
                    $transformer = array(
                        "namespace" => $transformerManifest->namespace,
                        "settings" => $transformerManifest->settings,
                        "contextParameters" => $transformerManifest->contextSettings,
                        "msg" =>  $transformerManifest->namespace::getMessages()
                    );
                    if (!is_countable($compatibility["chunk"])) {
                        static::$transformersManifests[$compatibility["chunk"]][] = $transformer;
                        continue;
                    }
                    foreach ($compatibility["chunk"] as $compatibleChunk) {
                        static::$transformersManifests[$compatibleChunk][] = $transformer;
                    }
                }
            }
        }

        if (array_key_exists($chunk, static::$transformersManifests)) {
            return static::$transformersManifests[$chunk];
        }

        return [];
    }
}
