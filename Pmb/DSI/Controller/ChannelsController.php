<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ChannelsController.php,v 1.27 2024/09/05 08:20:39 gneveu Exp $
namespace Pmb\DSI\Controller;

use Pmb\DSI\Models\Channel\Mail\MailChannel;
use Pmb\DSI\Models\Channel\RootChannel;
use Pmb\DSI\Models\Diffusion;
use Pmb\DSI\Models\DSIParserDirectory;
use Pmb\Common\Helper\GlobalContext;
use Pmb\DSI\Models\Channel\HumHub\HumHubChannel;
use Pmb\DSI\Orm\ChannelOrm;
use Pmb\DSI\Orm\DiffusionOrm;

class ChannelsController extends CommonController
{

	protected const VUE_NAME = "dsi/channels";
    /**
	 *
	 * {@inheritdoc}
	 * @see CommonController::getBreadcrumb
	 */
	protected function getBreadcrumb(): string
    {
		global $msg;
		return "{$msg['dsi_menu']} {$msg['menu_separator']} {$msg['dsi_channels']}";
	}

	protected function defaultAction()
	{
		$channel = RootChannel::getInstance();
		$this->render([
			"list" => $channel->getList(["model" => "1"]),
			"channelTypeList" => $this->getTypeList()
		]);
	}

	protected function editAction()
	{
		global $id;

		$id = intval($id);
		if (ChannelOrm::exist($id)) {
			$this->render([
				"channel" => RootChannel::getInstance($id),
				"channelTypeList" => $this->getTypeList()
			]);
        } else {
            global $msg;
			$this->notFound(
				sprintf($msg['channel_not_found'], strval($id)),
				"./dsi.php?categ=channels"
			);
        }
	}

	protected function addAction()
	{
		$this->render([
			"channel" => new RootChannel(),
			"channelTypeList" => $this->getTypeList()
		]);
	}

	public function save()
	{
		$this->data->id = intval($this->data->id);

		$channel = RootChannel::getInstance($this->data->id);
		$result = $channel->check($this->data);
		if ($result['error']) {
			$this->ajaxError($result['errorMessage']);
			exit();
		}
		$channel->setFromForm($this->data);

		if (0 == $this->data->id) {
			$channel->create();
		} else {
			$channel->update();
		}

		$this->ajaxJsonResponse($channel);
		exit();
	}

	public function delete()
	{
		$channel = RootChannel::getInstance($this->data->id);
		$result = $channel->delete();

		if ($result['error']) {
			$this->ajaxError($result['errorMessage']);
			exit();
		}
		$this->ajaxJsonResponse([
			'success' => true
		]);
		exit();
	}

	public function getMailList()
	{
		$data = MailChannel::getMailList();
		$this->ajaxJsonResponse($data);
	}

	private function getTypeList(): array
    {
		$channelTypeList = [];
		$manifests = DSIParserDirectory::getInstance()->getManifests("Pmb/DSI/Models/Channel/");
		foreach ($manifests as $manifest) {
			$message = $manifest->namespace::getMessages();
			$channelTypeList[] = [
				"id" => RootChannel::IDS_TYPE[$manifest->namespace],
				"namespace" => $manifest->namespace,
				"name" => $message['name'],
				"msg" => $message,
				"manually" => $manifest->manually,
			    "compatibility" => $manifest->compatibility
			];
		}

		return $channelTypeList;
	}

	public function getTypeListAjax()
	{
		$this->ajaxJsonResponse($this->getTypeList());
	}

	public function getModels()
	{
		$this->ajaxJsonResponse($this->fetchModels());
	}

	protected function fetchModels(): array
    {
		return (new RootChannel())->getList(["model" => "1"]);
	}

    /**
     * @param int $idModel
     */
    public function getModel(int $idModel)
	{
		$this->ajaxJsonResponse(RootChannel::getInstance($idModel));
	}

	public function getRequirements(int $type): array
    {
		$namespace = array_search($type, RootChannel::IDS_TYPE);
		if ($namespace !== false) {
			$this->ajaxJsonResponse($namespace::CHANNEL_REQUIREMENTS);
		}
		return array();
	}

	/**
	 * relie un tag a l'entite
     */
	public function unlinkTag()
	{
		$channel = RootChannel::getInstance();
		$delete = $channel->unlinkTag($this->data->numTag, $this->data->numEntity);
		$this->ajaxJsonResponse($delete);
	}

	/**
	 * Supprime le lien entre un tag et une entite
     */
	public function linkTag()
	{
		$channel = RootChannel::getInstance();
		$link = $channel->linkTag($this->data->numTag, $this->data->numEntity);
		$this->ajaxJsonResponse($link);
	}

	public function getPortalChannelOpacURL($idDiffusion = 0, $idHistory = 0)
	{
	    $url = "";
		if($idDiffusion) {
			if(! $idHistory && DiffusionOrm::exist($idDiffusion)) {
				$diffusion = new Diffusion($idDiffusion);
				$lastHistory = $diffusion->getLastHistorySent("Pmb\\DSI\\Models\\Channel\\Portal\\PortalChannel");
				$idHistory = $lastHistory->id;
			}
			$url = GlobalContext::get("opac_url_base");
			$url .= "index.php?lvl=dsi&diff=$idDiffusion";
			if($idHistory) {
				$url .= "&hist=$idHistory";
			}
		}
		$this->ajaxJsonResponse($url);
	}

	public function getHumHubContainers($idChannel = 0)
	{
		$containers = array();
		$channel = RootChannel::getInstance($idChannel);
		if($channel instanceof HumHubChannel) {
			$containers = $channel->getContainers();
		}
		$this->ajaxJsonResponse($containers);
	}

	public function duplicate()
    {
        if($this->data->id != 0) {
            $channelToDuplicate = RootChannel::getInstance($this->data->id);
            $newChannel = $channelToDuplicate->duplicate();
            $this->ajaxJsonResponse($newChannel);
        }
    }

	public function deleteAll()
    {
        foreach($this->data->ids as $id) {
            $channel = RootChannel::getInstance($id);
            $result = $channel->delete();
            if($result["error"]) {
                $this->ajaxError($result['errorMessage']);
            }
        }
        $this->ajaxJsonResponse([ 'success' => true ]);
    }

	public function exportModel($idModel = 0)
    {
		$idModel = intval($idModel);

        if($idModel != 0) {
            $modelToExport = RootChannel::getInstance($idModel);
			$modelToExport->export();
        }
    }

	public function importModel()
    {
		global $msg;

		if(isset($this->data->file) && !empty($this->data->file)) {
            $model = @unserialize($this->data->file);

			if($model && $model instanceof RootChannel) {
				$model->create();
				$this->ajaxJsonResponse($model);
			}
		}

		$this->ajaxError($msg["dsi_model_import_error"]);
    }

	public function importModelTags()
	{
		$channel = RootChannel::getInstance($this->data->numEntity);
		$channel->importModelTags();
		$this->ajaxJsonResponse($channel->tags);
	}
}

