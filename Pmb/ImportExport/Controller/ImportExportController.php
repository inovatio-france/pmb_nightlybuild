<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ImportExportController.php,v 1.11 2024/07/23 10:29:43 rtigero Exp $

namespace Pmb\ImportExport\Controller;

use Pmb\Common\Controller\Controller;
use Pmb\Common\Helper\Helper;
use Pmb\ImportExport\Models\ImportExportParserDirectory;
use Pmb\Common\Views\VueJsView;

class ImportExportController extends Controller
{
    protected const VUE_NAME = '';

    protected const MODEL_PATH = "";

    public function proceed()
    {
        $classname = $this->foundController();
        if (!empty($classname) && class_exists($classname)) {
            $controller = new $classname($this->data);
            return $controller->proceed();
        }
        $this->defaultAction();
    }

    private function foundController()
    {
        if (empty($this->data->categ)) {
            return "";
        }

        $explode = explode("\\", static::class);
        array_pop($explode);
        $explode[] = Helper::pascalize("{$this->data->categ}_controller");
        return implode("\\", $explode);
    }

    protected function defaultAction()
    {
        global $include_path, $lang;
        switch ($this->data->categ) {
            default:
                $filepath = "$include_path/messages/help/$lang/import_export.txt";
                if (file_exists($filepath)) {
                    include($filepath);
                }
                break;
        }
    }

    /**
     * Generation vue
     *
     * @param array $data
     */
    protected function render(array $data = [])
    {
        global $pmb_url_base;
        $vueJsView = new VueJsView(static::VUE_NAME, array_merge(Helper::toArray($this->data), [
            //             "breadcrumb" => $this->getBreadcrumb(),
            "url_webservice" => $pmb_url_base . "rest.php/importexport/",
            "url_base" => $pmb_url_base
        ], Helper::toArray($data)));
        print $vueJsView->render();
    }

    public function callback(string $callback)
    {
        $manifests = ImportExportParserDirectory::getInstance()->getManifests(static::MODEL_PATH);
        foreach ($manifests as $manifest) {
            if (method_exists($manifest->namespace, $callback)) {
                $this->ajaxJsonResponse($manifest->namespace::{$callback}($this->data));
            }
        }
        $this->ajaxJsonResponse([]);
    }
}
