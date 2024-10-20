<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ChunkJSON.php,v 1.4 2024/08/07 07:07:55 dbellamy Exp $

namespace Pmb\ImportExport\Models\Chunks\ChunkJSON;

use Pmb\ImportExport\Models\Chunks\Chunk;
use Pmb\ImportExport\Models\Chunks\ChunkInterface;
use \JsonMachine\Items;
use \JsonMachine\JsonDecoder\ExtJsonDecoder;

class ChunkJSON extends Chunk implements ChunkInterface
{

    /**
     * Tableau de tags à lire (chemin)
     * @var array
     */
    protected $entities = [];

    protected function initialize()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;

            $this->initializeStream();

            if (count($this->parameters['entitiesElements'])) {
                foreach ($this->parameters['entitiesElements'] as $v) {
                    $this->entities[] = $v['value'];
                }
            }
        }
    }



    public function next()
    {

        $this->initialize();
        $stream = fopen($this->uri, $this->mode, false, $this->context);

        $options = ['pointer' => $this->entities];
        $options['decoder'] = new ExtJsonDecoder(true);

        try {
            $entities = Items::fromStream($stream, $options);
            foreach ($entities as $entity) {
                yield $entity;
            }
        } catch (\Exception $e) {
            fclose($stream);
            return;
        }

        fclose($stream);
    }
}
