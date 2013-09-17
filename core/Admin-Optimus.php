<?php
/**************************************************************
* Optimus Brave � 2010-2013, Bugo
***************************************************************
* Admin-Optimus.php
***************************************************************
* License http://opensource.org/licenses/artistic-license-2.0
* Support and updates for this software can be found at
* http://dragomano.ru/page/optimus-brave and
* http://custom.simplemachines.org/mods/index.php?mod=2659
**************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

// ������ �������� ���� � �������
function optimus_admin_areas(&$admin_areas)
{
	global $txt;
	
	$counter = array_search('featuresettings', array_keys($admin_areas['config']['areas'])) + 1;

	$admin_areas['config']['areas'] = array_merge(
		array_slice($admin_areas['config']['areas'], 0, $counter, true),
		array(
			'optimus' => array(
				'label' => $txt['optimus_title'],
				'function' => create_function(null, 'optimus_area_settings();'),
				'icon' => 'maintain.gif',
				'subsections' => array(
					'common' => array($txt['optimus_common_title']),
					'verification' => array($txt['optimus_verification_title']),
					'counters' => array($txt['optimus_counters']),
					'robots' => array($txt['optimus_robots_title']),
				),
			),
		),
		array_slice($admin_areas['config']['areas'], $counter, count($admin_areas['config']['areas']), true)
	);
}

// ����� ���������� ��� ��������� ������� � ����������� ����
function optimus_area_settings()
{
	global $sourcedir, $context, $txt;

	require_once($sourcedir . '/ManageSettings.php');
	
	$context['page_title'] = $txt['optimus_main'];

	$subActions = array(
		'common' => 'optimus_common_settings',
		'verification' => 'optimus_verification_settings',
		'counters' => 'optimus_counters_settings',
		'robots' => 'optimus_robots_settings'
	);
	
	loadGeneralSettingParameters($subActions, 'common');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['optimus_title'],
		'tabs' => array(
			'common' => array(
				'description' => $txt['optimus_common_desc'],
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
		),
	);
	
	call_user_func($subActions[$_REQUEST['sa']]);
}

// ������ �������� ��������
function optimus_common_settings()
{
	global $context, $txt, $scripturl;
	
	loadTemplate('Optimus', 'optimus');
	$context['sub_template'] = 'common';
	$context['page_title'] .= ' - ' . $txt['optimus_common_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=common;save';

	$config_vars = array(
		array('int', 'optimus_portal_compat'),
		array('text', 'optimus_portal_index'),
		array('text', 'optimus_forum_index'),
		array('text', 'optimus_description'),
		array('check', 'optimus_board_description'),
		array('check', 'optimus_topic_description'),
		array('check', 'optimus_404_status'),
		array('check', 'optimus_sitemap_enable'),
		array('check', 'optimus_sitemap_link'),
		array('int', 'optimus_sitemap_topic_size')
	);
	
	$templates = array();
	foreach ($txt['optimus_templates'] as $name => $template)
	{
		$templates[$name] = array(
			'name' => isset($_POST['' . $name . '_name']) ? $_POST['' . $name . '_name'] : '',
			'page' => isset($_POST['' . $name . '_page']) ? $_POST['' . $name . '_page'] : '',
			'site' => isset($_POST['' . $name . '_site']) ? $_POST['' . $name . '_site'] : '',
		);
	}
	
	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		optimus_sitemap();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		updateSettings(array('optimus_templates' => serialize($templates)));
		redirectexit('action=admin;area=optimus;sa=common');
	}

	prepareDBSettingContext($config_vars);
}

// ������ �������� ��������
function optimus_verification_settings()
{
	global $context, $scripturl, $txt;

	loadTemplate('Optimus', 'optimus');
	$context['sub_template'] = 'verification';
	$context['page_title'] .= ' - ' . $txt['optimus_verification_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=verification;save';
	
	$config_vars = array();
	
	$meta = array();
	foreach ($txt['optimus_search_engines'] as $engine => $data) {
		if (!empty($_POST['' . $engine . '_content'])) {
			$meta[$engine] = array(
				'name' => isset($_POST['' . $engine . '_name']) ? $_POST['' . $engine . '_name'] : $data[0],
				'content' => $_POST['' . $engine . '_content']
			);
		}
	}
	
	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		updateSettings(array('optimus_meta' => serialize($meta)));
		redirectexit('action=admin;area=optimus;sa=verification');
	}

	prepareDBSettingContext($config_vars);
}

// ������ �������� ��������
function optimus_counters_settings()
{
	global $context, $txt, $scripturl, $settings;
	
	loadTemplate('Optimus', array('codemirror', 'optimus'));
	$context['sub_template'] = 'counters';	
	$context['page_title'] .= ' - ' . $txt['optimus_counters'];
	$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=counters;save';
	
	$config_vars = array(
    	array('large_text', 'optimus_head_code'),
		array('large_text', 'optimus_stat_code'),
		array('large_text', 'optimus_count_code'),
		array('large_text', 'optimus_count_code_css'),
		array('text', 'optimus_ignored_actions')
	);
	
	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=optimus;sa=counters');
	}

	prepareDBSettingContext($config_vars);
}

// ��������� �������� ��������
function optimus_robots_settings()
{
	global $context, $txt, $scripturl, $robots_path;

	loadTemplate('Optimus', 'optimus');
	$context['sub_template'] = 'robots';
	$context['page_title'] .= ' - ' . $txt['optimus_robots_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=robots;save';
	
	$robots_path = $_SERVER['DOCUMENT_ROOT'] . "/robots.txt";
	$context['robots_content'] = file_exists($robots_path) ? @file_get_contents($robots_path) : '';
	
	optimus_robots_create();

	if (isset($_GET['save']))
	{
		checkSession();
		if (isset($_POST['robots']))
		{
			$robots = stripslashes($_POST['robots']);
			file_put_contents($robots_path, $robots);
		}
		redirectexit('action=admin;area=optimus;sa=robots');
	}
}

// ������� �������� ����� robots.txt
function optimus_robots_create()
{
	global $boardurl, $smcFunc, $boarddir, $modSettings, $adkportal, $ultimateportalSettings, $ezpSettings, $context, $sourcedir, $txt, $scripturl;
	
	$url_path = @parse_url($boardurl, PHP_URL_PATH);
	
	// ����������� ��� ��������� ����� ������� ��� ������
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
	// ����, ������������ SP ��� ���������� ������
	$autosp = !empty($modSettings['sp_standalone_url']) ? substr($modSettings['sp_standalone_url'], strlen($boardurl)) : '';
	// PortaMx
	$pm = !empty($modSettings['pmx_frontmode']) && function_exists('PortaMx');
	// ���������, �� �������� �� ����� forum ������� ��� community (� PortaMx)
	$alias = !empty($modSettings['pmxsef_aliasactions']) && strpos($modSettings['pmxsef_aliasactions'], 'forum');
	// Adk Portal
	$ap = !empty($adkportal['adk_enable']) && function_exists('adkportalSettings');
	// ����, ������������ AP ��� ���������� ������	
	$autoap = $ap && !empty($adkportal['adk_stand_alone_url']) && $adkportal['adk_enable'] == 2 ? substr($adkportal['adk_stand_alone_url'], strlen($boardurl)) : '';
	// Dream Portal
	$dp = function_exists('dp_main') && isset($yes['dream_portal_view']) ? in_array('dp', $context['admin_features']) : false;
	// TinyPortal
	$tp = function_exists('TPortal_init');
	// EzPortal
	$ez = !empty($ezpSettings['ezp_portal_enable']) && function_exists('EzPortalMain');
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
	
	// ��������� ������������� ������ sitemap
	$map = 'sitemap.xml';
	$path_map = $boardurl . '/' . $map;
	$temp_map = file_exists($boarddir . '/' . $map);
	if (!$temp_map) $map = '';
	else $map = $path_map;
	
	// ��������� �������� ������
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
		"User-agent: Googlebot-Mobile",
		"Allow: " . $url_path . "/*wap",
		"Disallow: " . $url_path . "/",
		"|",
		"User-agent: YandexImageResizer",
		"Allow: " . $url_path . "/*wap",
		"Disallow: " . $url_path . "/",
		"|",
		"User-agent: MediaPartners-Google",
		"Allow: " . $url_path . "/",
		"|",
		substr($txt['lang_locale'], 0, 2) == 'ru' ? "User-agent: Baiduspider\nDisallow: " . $url_path . "/\n|" : "", // ����� ��� ������� �� ������? :)
		// ������� ��� ���� ��������� ������
		"User-agent: *",
		// Main
		"Allow: " . $url_path . "/$",
		// action=forum
		$sp || $tp || $ez ? "Allow: " . $url_path . "/*forum$" : "",
		// SimplePortal
		isset($modSettings['sp_portal_mode']) && $modSettings['sp_portal_mode'] == 3 && file_exists($boarddir . $autosp) ? "Allow: " . $url_path . $autosp : "",
		$sp ? "Allow: " . $url_path . "/*page*page" : "",
		// PortaMx
		$pm && $alias ? "Allow: " . $url_path . "/*forum$" : "",
		$pm && !$alias ? "Allow: " . $url_path . "/*community$" : "",
		// Adk Portal
		$ap && !$autoap ? "Allow: " . $url_path . "/*forum$" : "",
		isset($adkportal['adk_enable']) && $adkportal['adk_enable'] == 2 && file_exists($boarddir . $autoap) ? "Allow: " . $url_path . $autoap : "",
		$ap || $dp || $tp || $ez ? "Allow: " . $url_path . "/*page" : "",
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
Disallow: /*imode
Disallow: /*wap
Disallow: /*all" : "",

		"Disallow: " . $url_path . "/*action",
		$sef ? "" : "Disallow: " . $url_path . "/*board=*wap\nDisallow: " . $url_path . "/*board=*imode\nDisallow: " . $url_path . "/*topic=*wap\nDisallow: " . $url_path . "/*topic=*imode",
		!empty($modSettings['queryless_urls']) || $sef ? "" : "Disallow: " . $url_path . "/*topic=*.msg\nDisallow: " . $url_path . "/*topic=*.new",
		$sef ? "" : "Disallow: " . $url_path . "/*;",
		"Disallow: " . $url_path . "/*PHPSESSID",
		// Content
		!empty($modSettings['queryless_urls'])
			? ($sef ? "" : "Allow: " . $url_path . "/*board*.html$\nAllow: " . $url_path . "/*topic*.html$")
			: ($sef ? "" : "Allow: " . $url_path . "/*board\nAllow: " . $url_path . "/*topic"),
		// ��� ��������� ��������
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
	foreach ($robots as $line)
		if (!empty($line)) $new_robots[] = $line;
	$new_robots = implode("<br />", str_replace("|", "", $new_robots));
	
	$context['new_robots_content'] = parse_bbc('[code]' . $new_robots . '[/code]');
}

?>