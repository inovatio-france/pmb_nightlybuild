<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: WYSIWYGView.php,v 1.23 2024/10/17 08:23:25 jparis Exp $
namespace Pmb\DSI\Models\View\WYSIWYGView;

use Pmb\DSI\Models\View\RootView;
use Pmb\DSI\Models\Item\Item;
use Pmb\DSI\Orm\ViewOrm;
use Pmb\DSI\Models\View\WYSIWYGView\Render\ {
	HTML5Renderer,
	XHTMLRenderer
};

class WYSIWYGView extends RootView
{

	protected $entityId = 0;

	protected $limit = 0;

	protected $context = "";

	public const BLOCK_TYPES = array(
		"block" => 1,
		"textInput" => 2,
		"imageInput" => 3,
		"videoInput" => 4,
		"listInput" => 5,
		"textEditorInput" => 6,
		"viewInput" => 7,
		"viewImportInput" => 8
	);

	private $associatedItem = null;

	public function preview(Item $item, int $entityId, int $limit, string $context)
	{
		return $this->formatHTMLPreview($this->render($item, $entityId, $limit, $context), $this->settings->displayChoice ? false : true);
	}

	public function render(Item $item, int $entityId, int $limit, string $context)
	{
		$this->entityId = $entityId;
		$this->limit = $limit;
		$this->context = $context;

		$rootElement = $this->settings->layer->blocks[0];
		if (empty($rootElement)) {
			return "";
		}

		if ($this->getSetting("displayChoice", false)) {
			$renderer = new HTML5Renderer($this, $item);
		} else {
			$renderer = new XHTMLRenderer($this, $item);
		}
		return $renderer->render($rootElement);
	}

	public function read()
	{
		parent::read();

		if (! empty($this->settings) && ! empty($this->settings->layer->blocks)) {
			$this->checkChildViews($this->settings->layer->blocks[0]->blocks);
		}
	}

	/**
	 * Traitements de verification des blocks de la vue wysiwyg
	 *
	 * @param array $blocks
	 */
	protected function checkChildViews(&$blocks = [])
	{
		foreach ($blocks as $block) {
			switch ($block->type) {
				case self::BLOCK_TYPES["viewInput"]:
					if (! empty($block->viewSelected) && ! ViewOrm::exist(intval($block->viewSelected))) {
						$block->content = "";
						$block->viewSelected = "";
					}
					break;
			}
			if (count($block->blocks)) {
				$this->checkChildViews($block->blocks);
			}
		}
	}

	/**
	 * Mise � jour de l'id des vues et items associ�s
	 *
	 * @param int $oldIdChild
	 * @param int $idChild
	 */
	public function updateViewChild($oldIdChild = 0, $idChild = 0)
	{
		//Mise � jour des items associ�s aux vues enfant
		$block = $this->getViewBlockById($oldIdChild, $this->settings->layer->blocks);
		if ($block) {
			if(isset($block->content->viewId)) {
				$block->content->viewId = $idChild;
			} else {
				$block->viewSelected = $idChild;
			}
			//R�cup�ration de l'item dupliqu�
			if(!is_null($this->associatedItem) && !empty($block->itemSelected)) {
				$newItem = $this->associatedItem->getItemFromOldId($block->itemSelected);
				if (! empty($newItem)) {
					$block->itemSelected = $newItem->id;
				}
			}
		}
		//Mise � jour des conditions d'affichage de la sous vue
		$this->updateViewConditions($oldIdChild, $idChild, $this->settings->layer->blocks);
	}

	/**
	 *
	 * @param mixed $param
	 *        	Vue parente
	 */
	public function duplicate($param = null, $changeName = true)
	{
		$newEntity = static::getInstance($this->id);

		if($changeName) {
			$newEntity->name = $this->getDuplicateName($this->name);
		}

		if (! empty($param)) {
			$oldViewId = $newEntity->id;
			$newEntity->numParent = $param->id;
		}
		$newEntity->id = 0;
		$newEntity->create();
		//On passe l'item associ� pour le r�cup�rer r�cursivement
		$newEntity->associatedItem = $this->associatedItem;

		if (isset($oldViewId)) {
			//Mise � jour de la vue parente avec le nouvel enfant
			$param->updateViewChild($oldViewId, $newEntity->id);
			$param->update();
		}
		if ($newEntity->id != 0) {
			if (empty($this->settings->locked)) {
				$newEntity->childs = array();
				foreach ($this->childs as $child) {
					$newEntityChild = $child->duplicate($newEntity, $changeName);
					if ($newEntityChild !== false) {
						$newEntity->childs[] = $newEntityChild;
					}
				}
			}
			return $newEntity;
		}
		return false;
	}

	/**
	 * R�cup�re un bloc de type vue d'un tableau r�cursif de blocs par l'id.
	 *
	 * @param int $viewId
	 *        	L'id de la vue � rechercher.
	 * @param array $blocks
	 *        	Le tableau de blocs dans lequel effectuer la recherche.
	 * @return mixed|null Le bloc s'il est trouv�, null sinon.
	 */
	public function getViewBlockById($viewId, $blocks)
	{
		$toDuplicateTypes = [self::BLOCK_TYPES["viewInput"], self::BLOCK_TYPES["viewImportInput"]];
		foreach ($blocks as $block) {
			if (in_array($block->type, $toDuplicateTypes)) {
				if((isset($block->viewSelected) && $block->viewSelected == $viewId) || (isset($block->content->viewId) && $block->content->viewId == $viewId)) {
					return $block;
				}
			}

			if (! empty($block->blocks)) {
				$foundBlock = $this->getViewBlockById($viewId, $block->blocks);
				if (isset($foundBlock)) {
					return $foundBlock;
				}
			}
		}

		return null;
	}

	/**
	 * Permet d'associer un item � la vue.
	 * N�cessaire pour dupliquer la vue wysiwyg
	 *
	 * @param AggregatorItem $item
	 */
	public function setAssociatedItem($item)
	{
		if (is_a($item, "Pmb\DSI\Models\Item\Aggregator\AggregatorItem")) {
			$this->associatedItem = $item;
		}
	}

	/**
	 * Mise � jour des conditions d'affichage de la sous vue
	 *
	 * @param int $oldIdChild
	 * @param int $idChild
	 * @param array $blocks
	 */
	protected function updateViewConditions($oldIdChild, $idChild, $blocks)
	{
		foreach ($blocks as $block) {
			if ($block->type == self::BLOCK_TYPES["block"]) {
				if(isset($block->conditions) && ! empty($block->conditions->emptyAssociatedItem)) {
					$key = array_search($oldIdChild, $block->conditions->emptyAssociatedItem->views);
					if($key !== false) {
						$block->conditions->emptyAssociatedItem->views[$key] = $idChild;
					}
				}
			}
			if(count($block->blocks)) {
				$this->updateViewConditions($oldIdChild, $idChild, $block->blocks);
			}
		}
	}
}