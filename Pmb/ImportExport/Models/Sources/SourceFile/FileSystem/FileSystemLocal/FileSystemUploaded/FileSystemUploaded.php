<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FileSystemUploaded.php,v 1.5 2024/07/26 08:22:28 rtigero Exp $

namespace Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystemLocal\FileSystemUploaded;

use Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystem;
use Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystemLocal\FileSystemLocal;

class FileSystemUploaded extends FileSystemLocal
{
    protected $filePath = "";

    public static function getPMBUploadFolders()
    {
        $result = array();
        $folders = \upload_folder::get_upload_folders();

        foreach ($folders as $folder) {
            $result[] = [
                'value' => $folder['id'],
                'label' => $folder['name']
            ];
        }

        return $result;
    }

    protected function getFilePath()
    {
        if ($this->filePath == "" && isset($this->baseParameters["PMBFolderId"]) && isset($this->baseParameters["filePath"])) {
            $uploadFolder = new \upload_folder($this->baseParameters["PMBFolderId"]);
            $this->filePath = $uploadFolder->repertoire_path . $this->baseParameters["filePath"];
        }
        return $this->filePath;
    }

    public function disconnect()
    {
        FileSystem::disconnect();
    }
}
