<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SourceFile.php,v 1.6 2024/08/02 08:44:10 dbellamy Exp $

namespace Pmb\ImportExport\Models\Sources\SourceFile;

use Pmb\ImportExport\Models\ImportExportParserDirectory;
use Pmb\ImportExport\Models\Sources\Source;
use Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystem;

class SourceFile extends Source
{
    protected FileSystem $fileSystem;

    public function initSync()
    {
        $this->fileSystem = new $this->baseParameters["fileSystem"]();
        $this->fileSystem->setBaseParameters($this->baseParameters[$this->baseParameters["fileSystem"]]);
        if (isset($this->contextParameters[$this->baseParameters["fileSystem"]])) {
            $this->fileSystem->setContextParameters($this->contextParameters[$this->baseParameters["fileSystem"]]);
        }
        if ($this->fileSystem->connect()) {
            $this->chunk = new $this->baseParameters["entryFormat"]($this->fileSystem->getResource(), $this->baseParameters[$this->baseParameters["entryFormat"]]);
            return true;
        }
        return false;
    }

    public static function getSourceType()
    {
        $sourceType = parent::getSourceType();

        $parser = ImportExportParserDirectory::getInstance();
        $sourceType["fileSystems"] = array();
        $manifests = $parser->getManifests(str_replace('\\', '/', __NAMESPACE__));

        foreach ($manifests as $manifest) {
            if ($manifest->namespace == static::class) {
                continue;
            }

            $fileSystem = array();
            $fileSystem["namespace"] = $manifest->namespace;
            $fileSystem["type"] = $manifest->type;
            $fileSystem["settings"] = $manifest->settings;
            $fileSystem["contextParameters"] = $manifest->contextSettings;
            $fileSystem["msg"] = $manifest->namespace::getMessages();

            $sourceType["fileSystems"][] = $fileSystem;
        }
        return $sourceType;
    }

    public function closeSync()
    {
        $this->fileSystem->disconnect();
    }
}
