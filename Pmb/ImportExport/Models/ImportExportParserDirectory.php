<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ImportExportParserDirectory.php,v 1.2 2024/07/23 15:14:02 rtigero Exp $

namespace Pmb\ImportExport\Models;

use Pmb\Common\Library\Parser\ParserDirectory;

class ImportExportParserDirectory extends ParserDirectory
{
    protected $baseDir = __DIR__;

    protected $parserManifest = "\Pmb\ImportExport\Models\ImportExportParserManifest";

    /**
     *
     * @param string $namespace
     * @return array
     */
    public function getCompatibility(string $namespace)
    {
        $manifest = $this->getManifestByNamespace($namespace);
        return $manifest ? $manifest->compatibility : [];
    }

    /**
     *
     * @param string $namespace
     * @return ParserManifest|NULL
     */
    public function getManifestByNamespace(string $namespace)
    {
        return !empty($this->manifest[$namespace]) ? $this->manifest[$namespace] : null;
    }

    //TODO 13/06/2024 : passer le namespace en clé dans la classe parente
    protected function parse()
    {
        $path = $this->baseDir;
        $manifests = $this->loadManifests($path);

        foreach ($manifests as $manifest) {
            $this->manifest[$manifest->namespace] = $manifest;
        }
        $this->parsed = true;
    }
}
