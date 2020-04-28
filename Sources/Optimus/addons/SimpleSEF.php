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
 * @version 2.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Support for SimpleSEF
 */
class SimpleSEF
{
	/**
	 * Make compatibility with Optimus
	 *
	 * @return void
	 */
	public static function meta()
	{
		global $modSettings;

		if (!empty($modSettings['simplesef_enable']) && !empty($modSettings['optimus_remove_index_php']))
			updateSettings(array('optimus_remove_index_php' => 0));
	}
}
