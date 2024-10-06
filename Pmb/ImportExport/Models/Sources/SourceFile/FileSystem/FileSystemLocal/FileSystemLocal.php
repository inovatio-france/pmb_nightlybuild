<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FileSystemLocal.php,v 1.8 2024/07/26 08:07:32 rtigero Exp $

namespace Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystemLocal;

use Pmb\Common\Helper\GlobalContext;
use Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystem;

class FileSystemLocal extends FileSystem
{

    protected $filePath = "";

    protected function getFilePath()
    {
        if (empty($this->filePath) && isset($this->contextParameters["file"])) {
            $fileName = substr($this->contextParameters["file"], 0, 10) . intval(microtime(true)) . ".tmp";
            $tempFile = GlobalContext::get("base_path") . "/temp/" . $fileName;
            file_put_contents($tempFile, base64_decode($this->contextParameters["file"]));
            $this->filePath = $tempFile;
        }
        return $this->filePath;
    }

    public function disconnect()
    {
        parent::disconnect();
        @unlink($this->filePath);
    }

    /**
     * Retourne la liste des encodages pour un selecteur
     * @return array
     */
    public static function getEncodingsList()
    {
        $result = array();
        $encodings = mb_list_encodings();
        foreach ($encodings as $encoding) {
            $result[] = [
                "value" => $encoding,
                "label" => $encoding
            ];
        }
        return $result;
    }
}
