<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FileSystemFTP.php,v 1.5 2024/08/02 08:44:10 dbellamy Exp $

namespace Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystemFTP;

use Pmb\Common\Helper\GlobalContext;
use Pmb\ImportExport\Models\Sources\SourceFile\FileSystem\FileSystem;

class FileSystemFTP extends FileSystem
{
    protected const TIMEOUT_FTP = 90;

    protected $baseParameters;
    protected $file;
    protected $connection;
    private $tempFilePath = "";

    public function connect()
    {
        $this->connection = static::getFtpConnection($this->baseParameters);
        if ($this->connection) {
            return true;
        }
        return false;
    }

    public function read()
    {
        if ($this->baseParameters->filePath == "") {
            $this->baseParameters->filePath = "/";
        }
        $dir = @ftp_chdir($this->connection, $this->baseParameters->filePath);
        if ($dir) {
            $this->tempFilePath = GlobalContext::get("base_path") . "/temp/" . $this->baseParameters->fileName;
            $this->file = fopen($this->tempFilePath, "w+");
            @ftp_fget($this->connection, $this->file, $this->baseParameters->fileName);
        }
        return $this->file;
    }

    /**
     * Recuperation de la resource a ouvrir
     * @return array
     */
    public function getResource()
    {
        if ($this->baseParameters->filePath == "") {
            $this->baseParameters->filePath = "/";
        }
        $dir = @ftp_chdir($this->connection, $this->baseParameters->filePath);
        if ($dir) {
            $this->tempFilePath = GlobalContext::get("base_path") . "/temp/" . $this->baseParameters->fileName;
            $this->file = fopen($this->tempFilePath, "w+");
            @ftp_fget($this->connection, $this->file, $this->baseParameters->fileName);
            @fclose($this->file);
        }

        return [
            'type' => 'file',
            'uri' => $this->tempFilePath,
            'mode' => 'r',
            'context' => null
        ];
    }

    public function disconnect()
    {
        if ($this->connection) {
            @ftp_close($this->connection);
            @unlink($this->tempFilePath);
        }
    }

    /**
     * Teste la connexion FTP avec les parametres fournis dans $data
     * @param object $data
     * @return bool
     */
    public static function testFTPConnection($data)
    {
        $connection = static::getFtpConnection($data);
        if ($connection) {
            @ftp_close($connection);
            return true;
        }
        return false;
    }

    /**
     * Retourne le contenu du dossier FTP avec les parametres fournis dans $data
     * @param object $data
     * @return mixed
     */
    public static function showFTPContent($data)
    {
        $connection = static::getFtpConnection($data);
        if ($connection) {
            if ($data->filePath == "") {
                $data->filePath = "/";
            }
            $dir = @ftp_chdir($connection, $data->filePath);
            if ($dir) {
                $files = @ftp_rawlist($connection, $data->filePath);
                if ($files) {
                    @ftp_close($connection);
                    return $files;
                }
            }
        }
        return false;
    }

    /**
     * Retourne la connexion FTP avec les parametres fournis dans $data
     * @param object $data
     * @return mixed
     */
    protected static function getFtpConnection($data)
    {
        $connection = @ftp_connect($data->host, $data->port, static::TIMEOUT_FTP);
        if ($connection) {
            $login = @ftp_login($connection, $data->login, $data->password);
            ftp_pasv($connection, boolval($data->pasv));
            if ($login) {
                return $connection;
            }
        }
        return false;
    }
}
