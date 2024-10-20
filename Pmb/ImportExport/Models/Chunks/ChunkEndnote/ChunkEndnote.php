<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ChunkEndnote.php,v 1.1 2024/08/08 13:05:00 pmallambic Exp $

namespace Pmb\ImportExport\Models\Chunks\ChunkEndnote;

use Pmb\ImportExport\Models\Chunks\Chunk;
use Pmb\ImportExport\Models\Chunks\ChunkInterface;


class ChunkEndnote extends Chunk implements ChunkInterface
{
    protected $buffer = "";


    protected $firstElement = "%0";

    protected function initialize()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;

            $this->initializeStream();

            if (!empty($this->parameters["firstElement"])) {
                $this->firstElement = $this->parameters["firstElement"] . '  - ';
            }
        }
    }


    public function next()
    {
        $this->initialize();
        $resource = fopen($this->uri, $this->mode, false, $this->context);
        if (!$resource) {
            return;
        }

        $currentContent = "";
        while ($currentContent = $this->getNextEntity($resource)) {
            yield $currentContent;
            $currentContent = '';
        }
        fclose($resource);
    }

    protected function getNextEntity($resource)
    {
        $entity = $this->buffer;
        $pos_deb = false;

        while (true) {
            $pos_deb = strpos($this->buffer, $this->firstElement);
            if (false !== $pos_deb) {
                break;
            }
            if ((false === $pos_deb) && feof($resource)) {
                break;
            }
            $this->buffer .= fread($resource, static::MAX_LENGTH);
        }
        if (false === $pos_deb) {
            return $entity;
        }

        $this->buffer = substr($this->buffer, $pos_deb);

        $pos_end = false;
        $offset = strlen($this->firstElement);

        while (true) {
            $pos_end = strpos($this->buffer, $this->firstElement, $offset);
            if ((false === $pos_end) && feof($resource)) {
                break;
            }
            if (false !== $pos_end) {
                break;
            }
            $this->buffer .= fread($resource, static::MAX_LENGTH);
        }

        if (false !== $pos_end) {
            $entity = substr($this->buffer, 0, $pos_end);
            $this->buffer = substr($this->buffer, $pos_end);
        } else {
            $entity = $this->buffer;
            $this->buffer = '';
        }

        return $entity;
    }
}