<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FileSystem.php,v 1.11 2024/08/02 08:44:10 dbellamy Exp $

namespace Pmb\ImportExport\Models\Sources\SourceFile\FileSystem;

use Pmb\ImportExport\Models\Sources\Source;
use Pmb\ImportExport\Models\Transformers\TransformerInterface;

class FileSystem extends Source
{
    protected $file;
    protected $fileFormat;
    protected $chunk;
    protected $baseParameters;
    protected $contextParameters;

    /**
     * Connexion a la source
     * @return bool
     */
    public function connect()
    {
        if (is_readable($this->getFilePath())) {
            return true;
        }
        return false;
    }

    /**
     * Recuperation de la resource a ouvrir
     * @return array
     */
    public function getResource()
    {
        return [
            'type' => 'file',
            'uri' => $this->getFilePath(),
            'mode' => 'r',
            'context' => null
        ];
    }

    /**
    * Initialisation de la lecture du fichier
    * @return Resource
    */
    public function read()
    {
        $this->file = fopen($this->getFilePath(), 'r');
        return $this->file;
    }


    /**
     * Deconnexion de la source
     */
    public function disconnect()
    {
        if ($this->file) {
            @fclose($this->file);
        }
    }

    public function setBaseParameters($parameters)
    {
        $this->baseParameters = $parameters;
    }

    public function getBaseParameters()
    {
        return $this->baseParameters;
    }

    public function setContextParameters($parameters)
    {
        $this->contextParameters = $parameters;
    }

    public function getContextParameters()
    {
        return $this->contextParameters;
    }

    public function addTransformer(TransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    public function initSync()
    {
    }

    public function closeSync()
    {
    }

    public function toTriples($entity)
    {
    }

    /**
     * Retourne le chemin complet complet avec le nom du fichier
     * A deriver
     * @return string
     */
    protected function getFilePath()
    {
        return "";
    }
}
