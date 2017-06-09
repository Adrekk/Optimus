<?php

/**
 * Admin-Optimus.php
 *
 * @package Optimus
 * @link http://custom.simplemachines.org/mods/index.php?mod=2659
 * @author Bugo http://dragomano.ru/mods/optimus
 * @copyright 2010-2017 Bugo
 * @license http://opensource.org/licenses/artistic-license-2.0 Artistic-2.0
 *
 * @version 2.0 beta
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Раздел настроек мода в админке
 *
 * @param  array &$admin_areas [массив кнопок в меню админки]
 */
function addOptimusAdminArea(&$admin_areas)
{
	global $txt;

	loadCSSFile('optimus.css');

	$counter = array_search('featuresettings', array_keys($admin_areas['config']['areas'])) + 1;

	$admin_areas['config']['areas'] = array_merge(
		array_slice($admin_areas['config']['areas'], 0, $counter, true),
		array(
			'optimus' => array(
				'label'    => $txt['optimus_title'],
				'function' => 'addOptimusAreaSettings',
				'icon'     => 'optimus',
				'subsections' => array(
					'base'         => array($txt['optimus_common_title']),
					'extra'        => array($txt['optimus_extra_title']),
					'verification' => array($txt['optimus_verification_title']),
					'counters'     => array($txt['optimus_counters']),
					'robots'       => array($txt['optimus_robots_title']),
					'map'          => array($txt['optimus_sitemap_title']),
				),
			),
		),
		array_slice($admin_areas['config']['areas'], $counter, count($admin_areas['config']['areas']), true)
	);
}

// Здесь подключаем все имеющиеся функции с настройками мода
function addOptimusAreaSettings()
{
	global $sourcedir, $context, $txt;

	require_once($sourcedir . '/ManageSettings.php');

	$context['page_title'] = $txt['optimus_main'];

	loadTemplate('Optimus');

	$subActions = array(
		'base'         => 'addOptimusBaseSettings',
		'extra'        => 'addOptimusExtraSettings',
		'verification' => 'addOptimusVerificationSettings',
		'counters'     => 'addOptimusCountersSettings',
		'robots'       => 'addOptimusRobotsSettings',
		'map'          => 'addOptimusMapSettings',
	);

	loadGeneralSettingParameters($subActions, 'base');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['optimus_title'],
		'tabs' => array(
			'base' => array(
				'description' => $txt['optimus_common_desc'],
			),
			'extra' => array(
				'description' => $txt['optimus_extra_desc'],
			),
			'verification' => array(
				'description' => $txt['optimus_verification_desc'],
			),
			'counters' => array(
				'description' => $txt['optimus_counters_desc'],
			),
			'robots' => array(
				'description' => $txt['optimus_robots_desc'],
			),
			'map' => array(
				'description' => $txt['optimus_sitemap_desc'],
			),
		),
	);

	call_helper($subActions[$_REQUEST['sa']]);
}

// Страница настроек - Общие настройки
function addOptimusBaseSettings($return_config = false)
{
	global $context, $txt, $scripturl;

	$context['sub_template'] = 'base';
	$context['page_title']  .= ' - ' . $txt['optimus_common_title'];
	$context['post_url']     = $scripturl . '?action=admin;area=optimus;sa=base;save';

	$config_vars = array(
		array('int',   'optimus_portal_compat'),
		array('text',  'optimus_portal_index'),
		array('text',  'optimus_forum_index'),
		array('text',  'optimus_description'),
		array('check', 'optimus_topic_description'),
		array('check', 'optimus_404_status'),
	);

	$templates = array();
	foreach ($txt['optimus_templates'] as $name => $template) {
		$templates[$name] = array(
			'name' => isset($_POST['' . $name . '_name']) ? $_POST['' . $name . '_name'] : '',
			'site' => isset($_POST['' . $name . '_site']) ? $_POST['' . $name . '_site'] : '',		
		);
	}

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		saveDBSettings($config_vars);
		updateSettings(array('optimus_templates' => serialize($templates)));

		redirectexit('action=admin;area=optimus;sa=common');
	}

	prepareDBSettingContext($config_vars);
}

// Страница настроек - Дополнительно
function addOptimusExtraSettings()
{
	global $context, $txt, $scripturl;

	$context['page_title'] .= ' - ' . $txt['optimus_extra_title'];
	$context['post_url']    = $scripturl . '?action=admin;area=optimus;sa=extra;save';
	
	$config_vars = array(
		array('title', 'optimus_extra_title'),
		array('check', 'optimus_remove_last_bc_item'),
		array('check', 'optimus_og_image'),
	);

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		$save_vars = $config_vars;
		saveDBSettings($save_vars);

		redirectexit('action=admin;area=optimus;sa=extra');
	}

	prepareDBSettingContext($config_vars);
}

// Страница настроек - Проверочные мета-теги
function addOptimusVerificationSettings($return_config = false)
{
	global $context, $txt, $scripturl;

	$context['sub_template'] = 'verification';
	$context['page_title'] .= ' - ' . $txt['optimus_verification_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=verification;save';

	$config_vars = array();

	$meta = array();
	foreach ($txt['optimus_search_engines'] as $engine => $data) {
		if (!empty($_POST['' . $engine . '_content'])) {
			$meta[$engine] = array(
				'name'    => isset($_POST['' . $engine . '_name']) ? $_POST['' . $engine . '_name'] : $data[0],
				'content' => $_POST['' . $engine . '_content'],
			);
		}
	}

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		saveDBSettings($config_vars);
		updateSettings(array('optimus_meta' => serialize($meta)));

		redirectexit('action=admin;area=optimus;sa=verification');
	}

	prepareDBSettingContext($config_vars);
}

// Страница настроек - Счётчики
function addOptimusCountersSettings($return_config = false)
{
	global $context, $txt, $scripturl;

	$context['sub_template'] = 'counters';
	$context['page_title']  .= ' - ' . $txt['optimus_counters'];
	$context['post_url']     = $scripturl . '?action=admin;area=optimus;sa=counters;save';

	$config_vars = array(
    	array('large_text', 'optimus_head_code'),
		array('large_text', 'optimus_stat_code'),
		array('text', 'optimus_ignored_actions'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		saveDBSettings($config_vars);

		redirectexit('action=admin;area=optimus;sa=counters');
	}

	prepareDBSettingContext($config_vars);
}

// Страница настроек - Файл robots.txt
function addOptimusRobotsSettings()
{
	global $context, $txt, $scripturl, $robots_path;

	$context['sub_template'] = 'robots';
	$context['page_title']  .= ' - ' . $txt['optimus_robots_title'];
	$context['post_url']     = $scripturl . '?action=admin;area=optimus;sa=robots;save';

	$robots_path = $_SERVER['DOCUMENT_ROOT'] . "/robots.txt";
	$context['robots_content'] = file_exists($robots_path) ? @file_get_contents($robots_path) : '';

	getOptimusRobotsCreate();

	if (isset($_GET['save'])) {
		checkSession();

		if (isset($_POST['robots'])) {
			$robots = stripslashes($_POST['robots']);
			file_put_contents($robots_path, $robots);
		}

		redirectexit('action=admin;area=optimus;sa=robots');
	}
}

// Страница настроек - Карта форума
function addOptimusMapSettings()
{
	global $context, $txt, $scripturl, $boarddir, $modSettings;

	$context['page_title']  .= ' - ' . $txt['optimus_sitemap_title'];
	$context['post_url']     = $scripturl . '?action=admin;area=optimus;sa=map;save';

	$config_vars = array(
		array('title', 'optimus_sitemap_xml_link'),
		array('check', 'optimus_sitemap_enable'),
		array('check', 'optimus_sitemap_link', 'disabled' => file_exists($boarddir . '/sitemap.xml') ? false : true),
		array('check', 'optimus_sitemap_boards', 'disabled' => empty($modSettings['optimus_sitemap_enable']) ? true : false),
		array('int',   'optimus_sitemap_topics', 'disabled' => empty($modSettings['optimus_sitemap_enable']) ? true : false),
		array('check', 'optimus_sitemap_gallery', 'disabled' => empty($modSettings['optimus_sitemap_enable']) ? true : false),
		array('check', 'optimus_sitemap_classifieds', 'disabled' => empty($modSettings['optimus_sitemap_enable']) ? true : false)
	);

	clearstatcache();

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		getOptimusSitemap();

		$save_vars = $config_vars;
		saveDBSettings($save_vars);

		redirectexit('action=admin;area=optimus;sa=map');
	}

	prepareDBSettingContext($config_vars);
}

// Функция создания файла robots.txt
function getOptimusRobotsCreate()
{
	global $boardurl, $smcFunc, $boarddir, $modSettings, $context, $sourcedir, $txt, $scripturl;

	$url_path = @parse_url($boardurl, PHP_URL_PATH);

	// Запрашиваем все имеющиеся права доступа для гостей
	$yes = array();

	$request = $smcFunc['db_query']('', '
		SELECT ps.permission
		FROM {db_prefix}permissions AS ps
		WHERE ps.id_group = -1',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$yes[$row['permission']] = true;

	$smcFunc['db_free_result']($request);

	// SimplePortal
	$sp = isset($modSettings['sp_portal_mode']) && $modSettings['sp_portal_mode'] == 1 && function_exists('sportal_init');
	// Файл, используемый SP при автономном режиме
	$autosp = !empty($modSettings['sp_standalone_url']) ? substr($modSettings['sp_standalone_url'], strlen($boardurl)) : '';

	// PortaMx
	$pm = !empty($modSettings['pmx_frontmode']) && function_exists('PortaMx');
	// Проверяем, не является ли экшен forum алиасом для community (в PortaMx)
	$alias = !empty($modSettings['pmxsef_aliasactions']) && strpos($modSettings['pmxsef_aliasactions'], 'forum');

	// Aeva Media
	$aeva = file_exists($sourcedir . '/Aeva-Subs.php') && isset($yes['aeva_access']);

	// SMF Gallery
	$gal = file_exists($sourcedir . '/Gallery2.php') && isset($yes['smfgallery_view']);

	// SMF Arcade
	$arc = file_exists($sourcedir . '/Subs-Arcade.php') && isset($yes['arcade_view']);

	// FAQ mod
	$faq = file_exists($sourcedir . '/Subs-Faq.php') && isset($yes['faqperview']);

	// PMXBlog
	$blog = file_exists($sourcedir . '/PmxBlog.php') && !empty($modSettings['pmxblog_enabled']);

	// SMF Project Tools
	$pj = file_exists($sourcedir . '/Project.php') && in_array('pj', $context['admin_features']) && isset($yes['project_access']);

	// Simple Classifieds
	$sc = file_exists($sourcedir . '/Classifieds/Classifieds-Subs.php') && isset($yes['view_classifieds']);

	// SC Light
	$scl = file_exists($sourcedir . '/Subs-SCL.php') && !empty($modSettings['scl_mode']);

	// Topic Rating Bar
	$trb = file_exists($sourcedir . '/Subs-TopicRating.php');

	// Downloads System
	$ds = file_exists($sourcedir . '/Downloads2.php') && isset($yes['downloads_view']);

	// SMF Links
	$sl = isset($txt['smflinks_menu']) && isset($yes['view_smflinks']);

	// Pretty URLs enabled?
	$pretty = file_exists($sourcedir . '/PrettyUrls-Filters.php') && !empty($modSettings['pretty_enable_filters']);

	// SimpleSEF enabled?
	$simplesef = !empty($modSettings['simplesef_enable']) && file_exists($sourcedir . '/SimpleSEF.php');

	$sef = $pretty || $simplesef;

	// Проверяем существование файла sitemap
	$map = 'sitemap.xml';
	$path_map = $boardurl . '/' . $map;
	clearstatcache();
	$temp_map = file_exists($boarddir . '/' . $map);
	if (!$temp_map)
		$map = '';
	else
		$map = $path_map;

	// Заполняем основной массив
	$robots = array(
		"User-agent: Googlebot-Image",
		$aeva ? "Allow: " . $url_path . "/*media*item" : "",
		$aeva ? "Allow: " . $url_path . "/MGalleryItem.php" : "",
		$gal ? "Allow: " . $url_path . "/*gallery*view" : "",
		"Disallow: " . $url_path . "/",
		"|",
		"User-agent: YandexImages",
		$aeva ? "Allow: " . $url_path . "/*media*item" : "",
		$aeva ? "Allow: " . $url_path . "/MGalleryItem.php" : "",
		$gal ? "Allow: " . $url_path . "/*gallery*view" : "",
		"Disallow: " . $url_path . "/",
		"|",
		"User-agent: msnbot-MM",
		$aeva ? "Allow: " . $url_path . "/*media*item" : "",
		$aeva ? "Allow: " . $url_path . "/MGalleryItem.php" : "",
		$gal ? "Allow: " . $url_path . "/*gallery*view" : "",
		"Disallow: " . $url_path . "/",
		"|",
		"User-agent: MediaPartners-Google",
		"Allow: " . $url_path . "/",
		"|",
		substr($txt['lang_locale'], 0, 2) == 'ru' ? "User-agent: Baiduspider\nDisallow: " . $url_path . "/\n|" : "",
		// Правила для всех остальных пауков
		"User-agent: *",
		// Main
		"Allow: " . $url_path . "/$",
		// action=forum
		$sp ? "Allow: " . $url_path . "/*forum$" : "",
		// SimplePortal
		isset($modSettings['sp_portal_mode']) && $modSettings['sp_portal_mode'] == 3 && file_exists($boarddir . $autosp) ? "Allow: " . $url_path . $autosp : "",
		$sp ? "Allow: " . $url_path . "/*page*page" : "",
		// PortaMx
		$pm && $alias ? "Allow: " . $url_path . "/*forum$" : "",
		$pm && !$alias ? "Allow: " . $url_path . "/*community$" : "",
		// Aeva Media
		$aeva ? "Allow: " . $url_path . "/*media$\nAllow: " . $url_path . "/*media*album\nAllow: " . $url_path . "/*media*item\nAllow: " . $url_path . "/MGalleryItem.php?id" : "",
		// SMF Gallery mod
		$gal ? "Allow: " . $url_path . "/*gallery$\nAllow: " . $url_path . "/*gallery*cat\nAllow: " . $url_path . "/*gallery*view" : "",
		// RSS
		!empty($modSettings['xmlnews_enable']) ? "Allow: " . $url_path . "/*.xml" : "",
		// Sitemap
		!empty($map) || file_exists($sourcedir . '/Sitemap.php') ? "Allow: " . $url_path . "/*sitemap" : "",
		// SMF Arcade
		$arc ? "Allow: " . $url_path . "/*arcade$\nAllow: " . $url_path . "/*arcade*game" : "",
		// FAQ
		$faq ? "Allow: " . $url_path . "/*faq" : "",
		// PMXBlog
		$blog ? "Allow: " . $url_path . "/*pmxblog" : "",
		// Project Tools
		$pj ? "Allow: " . $url_path . "/*project\nAllow: " . $url_path . "/*issue" : "",
		// SC Light
		$scl ? "Allow: " . $url_path . "/*scl" : "",
		// Simple Classifieds
		$sc ? "Allow: " . $url_path . "/*bbs" : "",
		// Topic Rating Bar
		$trb ? "Allow: " . $url_path . "/*rating" : "",
		// Downloads System
		$ds ? "Allow: " . $url_path . "/*downloads" : "",
		// SMF Links
		$sl ? "Allow: " . $url_path . "/*links" : "",

		// Special rules for Pretty URLs
		$sef ? "Disallow: /attachments/
Disallow: /avatars/
Disallow: /Packages/
Disallow: /Smileys/
Disallow: /Sources/
Disallow: /Themes/
Disallow: /*msg
Disallow: /*profile
Disallow: /*help
Disallow: /*search
Disallow: /*mlist
Disallow: /*sort
Disallow: /*recent
Disallow: /*register
Disallow: /*groups
Disallow: /*stats
Disallow: /*unread
Disallow: /*topicseen
Disallow: /*showtopic
Disallow: /*prev_next
Disallow: /*all" : "",

		"Disallow: " . $url_path . "/*action",
		!empty($modSettings['queryless_urls']) || $sef ? "" : "Disallow: " . $url_path . "/*topic=*.msg\nDisallow: " . $url_path . "/*topic=*.new",
		$sef ? "" : "Disallow: " . $url_path . "/*;",
		"Disallow: " . $url_path . "/*PHPSESSID",
		// Content
		!empty($modSettings['queryless_urls'])
			? ($sef ? "" : "Allow: " . $url_path . "/*board*.html$\nAllow: " . $url_path . "/*topic*.html$")
			: ($sef ? "" : "Allow: " . $url_path . "/*board\nAllow: " . $url_path . "/*topic"),
		// Все остальные страницы
		$sef ? "" : "Disallow: " . $url_path . "/",
		// Sitemap XML
		!empty($map) ? "Sitemap: " . $map : "",
		file_exists($sourcedir . '/Sitemap.php') ? "Sitemap: " . $scripturl . "?action=sitemap;xml" : "",
		// Delay for spiders
		"Crawl-delay: 5",
		// for Yandex only
		substr($txt['lang_locale'], 0, 2) == 'ru' ? "Clean-param: PHPSESSID " . $url_path . "/index.php" : "",
		substr($txt['lang_locale'], 0, 2) == 'ru' && !empty($_SERVER['HTTP_HOST']) ? "Host: " . $_SERVER['HTTP_HOST'] : ""
	);

	$new_robots = array();
	foreach ($robots as $line) {
		if (!empty($line)) $new_robots[] = $line;
	}

	$new_robots = implode("<br />", str_replace("|", "", $new_robots));

	$context['new_robots_content'] = parse_bbc('[code]' . $new_robots . '[/code]');
}

?>