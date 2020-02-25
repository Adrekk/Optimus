<?php

namespace Bugo\Optimus\Addons;

/**
 * SimpleSEF.php
 *
 * @package Optimus
 * @link https://custom.simplemachines.org/mods/index.php?mod=2659
 * @author Bugo https://dragomano.ru/mods/optimus
 * @copyright 2010-2020 Bugo
 * @license https://opensource.org/licenses/artistic-license-2.0 Artistic-2.0
 *
 * @version 2.6.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Support for SimpleSEF
 */
class SimpleSEF
{
	/**
	 * Make preparing of sitemap content before creating
	 *
	 * @param array $links
	 * @return void
	 */
	public static function sitemap(&$links)
	{
		global $modSettings, $sourcedir;

		if (!empty($modSettings['simplesef_enable']) && file_exists($sourcedir . '/SimpleSEF.php')) {
			require_once($sourcedir . '/SimpleSEF.php');

			$simple_sef = new \SimpleSEF;

			foreach ($links as $id => $entry)
				$links[$id]['url'] = $simple_sef->create_sef_url($entry['url']);
		}
	}
}
