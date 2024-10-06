<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ImportexportRouterRest.php,v 1.10 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\REST;

use Pmb\Common\Helper\Helper;

class ImportexportRouterRest extends RouterRest
{

	/**
	 *
	 * @const string
	 */
	protected const CONTROLLER = "\\Pmb\\ImportExport\\Controller\\ImportExportController";

	/**
	 *
	 * {@inheritdoc}
	 * @see \Pmb\REST\RouterRest::generateRoutes()
	 */
	protected function generateRoutes()
	{
		//POST
		$this->post('{controller}/save', 'save');
		$this->post('{controller}/duplicate', 'duplicate');
		$this->post('{controller}/remove', 'remove');
		$this->post('{controller}/saveStepsOrder/{numScenario}', 'saveStepsOrder');
		$this->post('{controller}/callback/{callback}', 'callback');
		$this->post('{controller}/execute', 'execute');

		//GET
		$this->get('{controller}/callback/{callback}', 'callback');
	}

	/**
	 *
	 * @param RouteRest $route
	 * @return mixed
	 */
	protected function call(RouteRest $route)
	{
		global $data;

		$data = \encoding_normalize::json_decode(stripslashes($data ?? ''));
		if (empty($data) || !is_object($data)) {
			$data = new \stdClass();
		}

		$args = $route->getArguments();
		$className = $this->foundController($route);
		if (false === $className) {
			$className = static::CONTROLLER;
		} elseif (count($args) > 0) {
			array_splice($args, 0, 1);
		}

		$callback = [
			new $className($data),
			$route->getMethod(),
		];
		if (is_callable($callback)) {
			return call_user_func_array($callback, $args);
		}
	}

	private function foundController(RouteRest $route)
	{
		$args = $route->getArguments();
		$controller = $args[0] ?? "";

		$namespace = "Pmb\\ImportExport\\Controller\\" . Helper::pascalize("{$controller}_controller");
		if (class_exists($namespace)) {
			return $namespace;
		}
		return false;
	}
}
