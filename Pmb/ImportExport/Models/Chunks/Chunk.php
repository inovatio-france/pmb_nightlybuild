<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Chunk.php,v 1.10 2024/08/07 07:07:55 dbellamy Exp $

namespace Pmb\ImportExport\Models\Chunks;

use Pmb\Common\Helper\ParserMessage;
use Pmb\ImportExport\Models\ImportExportParserDirectory;

abstract class Chunk
{

    use ParserMessage;

    const MAX_LENGTH = 8192;

    /**
     * Indicateur d'initialisation
     * @var boolean
     */
    protected $isInitialized = false;

    /**
     * Tableau de parametres bruts
     * @var array
     */
    protected $parameters = [];

    /**
     * Resource
     * Contient la description du flux à lire :
     * array (
     *     'uri' => 'chemin du flux',
     *     'mode' => 'mode',
     *     'context' => array(
     *         'options' => array(),
     *         'params' => array(),
     *     ),
     * )
     *
     * @var resource
     *
     */
    protected $resource = [];

    /**
     * URI du flux
     *
     * @var string
     */
    protected $uri = '';

    /**
     * Mode d'ouverture du flux
     *
     * @var string
     */
    protected $mode = 'r';

    /**
     * Contexte du flux
     *
     * @var string
     */


    protected $context = null;

    protected static $chunksList = null;

    public function __construct($resource, $parameters)
    {
        $this->resource = $resource;
        $this->parameters = $parameters;
    }

    /**
     * Initialisation flux + contexte
     *
     * @return void
     */
    protected function initializeStream()
    {
        $this->uri = $this->resource['uri'] ?? '';
        if (!empty($this->resource['mode'])) {
            $this->mode = $this->resource['mode'] ?? '';
        }
        if (!empty($this->resource['context'])) {
            $options = $this->resource['context']['options'] ?? [];
            $params = $this->resource['context']['params'] ?? [];
            $this->context = stream_context_create($options, $params);
        }
    }

    /**
     * Retourne la liste des types de chunks
     *
     * @return array
     */
    public static function getChunksList()
    {
        if (!is_null(static::$chunksList)) {
            return static::$chunksList;
        }
        static::$chunksList = array();
        $parser = ImportExportParserDirectory::getInstance();
        $manifests = $parser->getManifests(str_replace('\\', '/', __NAMESPACE__));
        foreach ($manifests as $manifest) {
            if (!empty($manifest->type) && strpos($manifest->type, 'Chunk') !== false) {
                $messages = $manifest->namespace::getMessages();
                static::$chunksList[] = array(
                    'type' => $manifest->type,
                    'format' => $manifest->format,
                    'namespace' => $manifest->namespace,
                    'settings' => $manifest->settings,
                    'rdfTransformer' => $manifest->rdfTransformer,
                    'msg' =>  $messages
                );
            }
        }
        return static::$chunksList;
    }

    public static function getChunksSelectorOptions()
    {
        $selectorOptions = [];
        $chunksList = static::getChunksList();
        foreach ($chunksList as $chunk) {
            $selectorOptions[] = ['value' => $chunk['format'], 'label' => $chunk['msg']['name']];
        }
        return $selectorOptions;
    }
}
