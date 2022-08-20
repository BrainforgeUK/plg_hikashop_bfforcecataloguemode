<?php
/**
 * @package   Forces catalogue product display, regardless of Hikashop configuration.
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\CMSPlugin;

class plgHikashopBfforcecataloguemode extends CMSPlugin
{
	public $params;
	protected static $_modeForced = null;

	public function onHikashopBeforeDisplayView(&$view)
	{
		$app = Factory::getApplication();
		if ($app->isClient('administrator'))
		{
			return;
		}

		if (!empty($view->cart_type)) return;

		$viewClassName = get_class($view);
		switch($viewClassName)
		{
			case 'ProductViewProduct':
				if (plgHikashopBfforcecataloguemode::$_modeForced !== null) return;

				$user = $app->getIdentity();
				$usergroups = array_intersect($this->params->get('usergroups', array()), Access::getGroupsByUser($user->id));
				$mode = $this->params->get('mode', 1);

				if (empty($usergroups))
				{
					if (empty($mode)) return;
				}
				else
				{
					if (!empty($mode)) return;
				}

				$view->config->set('catalogue', true);
				$view->plgHikashopBfforcecataloguemode = true;

				plgHikashopBfforcecataloguemode::$_modeForced = true;
				$this->loadModule($this->params->get('onHikashopBeforeDisplayView'));
				return;
			default:
				return;
		}
	}

	public function onHikashopAfterDisplayView(&$view)
	{
		$app = Factory::getApplication();
		if ($app->isClient('administrator'))
		{
			return;
		}

		if (!empty($view->cart_type)) return;

		switch(get_class($view))
		{
			case 'ProductViewProduct':
				if (empty($view->plgHikashopBfforcecataloguemode)) return;
				if (plgHikashopBfforcecataloguemode::$_modeForced === null) return;

				$this->loadModule($this->params->get('onHikashopAfterDisplayView'));
				return;
			default:
				return;
		}
	}

	public function hasBeenForced()
	{
		return !empty(plgHikashopBfforcecataloguemode::$_modeForced);
	}

	protected function loadModule($position, $style='none')
	{
		if (empty($position)) return;

		if (empty(intval($position)))
		{
			$modules = ModuleHelper::getModules($position);
			if (empty($modules)) return;
		}
		else
		{
			$modules = ModuleHelper::getModuleById($position);
			if (empty($modules->id)) return;
			$modules = array($modules);
		}

		$params   = array('style' => $style);
		$renderer = Factory::getApplication()->getDocument()->loadRenderer('module');

		foreach ($modules as $module) {
			if (empty($module->id)) continue;
			echo $renderer->render($module, $params);
		}
	}
}