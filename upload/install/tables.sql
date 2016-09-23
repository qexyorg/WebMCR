CREATE TABLE IF NOT EXISTS `mcr_comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL DEFAULT '0',
  `text_html` text NOT NULL,
  `text_bb` text NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_files` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniq` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `oldname` varchar(255) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`uniq`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `~ug~` (
  `~ug_id~` int(10) NOT NULL AUTO_INCREMENT,
  `~ug_title~` varchar(32) NOT NULL DEFAULT '',
  `~ug_text~` varchar(255) NOT NULL DEFAULT '',
  `~ug_color~` varchar(24) NOT NULL DEFAULT '',
  `~ug_perm~` text NOT NULL,
  PRIMARY KEY (`~ug_id~`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
#line
INSERT INTO `~ug~` (`~ug_id~`, `~ug_title~`, `~ug_text~`, `~ug_color~`, `~ug_perm~`) VALUES
(0, 'Заблокированный', 'Группа заблокированных пользователей', '', '{"color":0,"sys_debug":false,"sys_max_file_size":0,"sys_max_ratio":0,"sys_monitoring":true,"sys_share":true,"sys_search":true,"sys_restore":true,"sys_register":true,"sys_profile":true,"sys_profile_del_skin":false,"sys_profile_del_cloak":false,"sys_profile_skin":false,"sys_profile_cloak":false,"sys_profile_settings":false,"sys_news_list":true,"sys_news_full":true,"sys_comment_list":false,"sys_comment_add":false,"sys_comment_edt":false,"sys_comment_edt_all":false,"sys_comment_del":false,"sys_comment_del_all":false,"sys_auth":false,"sys_adm_main":false,"sys_adm_news":false,"sys_adm_news_cats":false,"sys_adm_news_views":false,"sys_adm_news_votes":false,"sys_adm_comments":false,"sys_adm_menu":false,"sys_adm_menu_adm":false,"sys_adm_menu_groups":false,"sys_adm_menu_icons":false,"sys_adm_users":false,"sys_adm_groups":false,"sys_adm_permissions":false,"sys_adm_statics":false,"sys_adm_info":false,"sys_adm_settings":false,"sys_adm_monitoring":false,"sys_adm_modules":false,"sys_search_news":false,"sys_search_comments":false,"sys_news_like":false,"sys_adm_m_g_main":false,"sys_adm_m_g_news":false,"sys_adm_m_g_users":false,"sys_adm_m_g_menu":false,"sys_adm_m_g_settings":false,"sys_adm_m_i_news":false,"sys_adm_m_i_news_cats":false,"sys_adm_m_i_comments":false,"sys_adm_m_i_news_views":false,"sys_adm_m_i_news_votes":false,"sys_adm_m_i_users":false,"sys_adm_m_i_groups":false,"sys_adm_m_i_permissions":false,"sys_adm_m_i_menu":false,"sys_adm_m_i_menu_adm":false,"sys_adm_m_i_menu_groups_adm":false,"sys_adm_m_i_icons":false,"sys_adm_m_i_statics":false,"sys_adm_m_i_settings":false,"sys_adm_m_i_monitor":false,"sys_adm_m_i_info":false,"sys_adm_m_i_modules":false,"sys_adm_m_i_logs":false,"sys_adm_manager":false,"sys_adm_logs":false,"sys_adm_permissions_add":false,"sys_adm_permissions_edit":false,"sys_adm_permissions_delete":false,"sys_adm_comments_add":false,"sys_adm_comments_edit":false,"sys_adm_comments_delete":false,"sys_adm_groups_add":false,"sys_adm_groups_edit":false,"sys_adm_groups_delete":false,"sys_adm_menu_add":false,"sys_adm_menu_edit":false,"sys_adm_menu_delete":false,"sys_adm_menu_adm_add":false,"sys_adm_menu_adm_edit":false,"sys_adm_menu_adm_delete":false,"sys_adm_menu_groups_add":false,"sys_adm_menu_groups_edit":false,"sys_adm_menu_groups_delete":false,"sys_adm_menu_icons_add":false,"sys_adm_menu_icons_edit":false,"sys_adm_menu_icons_delete":false,"sys_adm_monitoring_add":false,"sys_adm_monitoring_edit":false,"sys_adm_monitoring_delete":false,"sys_adm_news_add":false,"sys_adm_news_edit":false,"sys_adm_news_delete":false,"sys_adm_news_cats_add":false,"sys_adm_news_cats_edit":false,"sys_adm_news_cats_delete":false,"sys_adm_statics_add":false,"sys_adm_statics_edit":false,"sys_adm_statics_delete":false,"sys_adm_users_add":false,"sys_adm_users_edit":false,"sys_adm_users_delete":false,"sys_adm_users_ban":false,"sys_adm_modules_edit":false,"sys_adm_news_views_delete":false,"sys_adm_news_votes_delete":false,"sys_adm_blocks":false,"sys_adm_m_i_blocks":false,"sys_adm_blocks_edit":false,"block_online":false,"block_banner":false}'),
(1, 'Непроверенный', 'Группа непроверенных пользователей', '', '{"color":0,"sys_debug":false,"sys_max_file_size":1024,"sys_max_ratio":0,"sys_monitoring":true,"sys_share":true,"sys_search":true,"sys_restore":true,"sys_register":true,"sys_profile":true,"sys_profile_del_skin":false,"sys_profile_del_cloak":false,"sys_profile_skin":false,"sys_profile_cloak":false,"sys_profile_settings":false,"sys_news_list":true,"sys_news_full":true,"sys_comment_list":true,"sys_comment_add":false,"sys_comment_edt":false,"sys_comment_edt_all":false,"sys_comment_del":false,"sys_comment_del_all":false,"sys_auth":true,"sys_adm_main":false,"sys_adm_news":false,"sys_adm_news_cats":false,"sys_adm_news_views":false,"sys_adm_news_votes":false,"sys_adm_comments":false,"sys_adm_menu":false,"sys_adm_menu_adm":false,"sys_adm_menu_groups":false,"sys_adm_menu_icons":false,"sys_adm_users":false,"sys_adm_groups":false,"sys_adm_permissions":false,"sys_adm_statics":false,"sys_adm_info":false,"sys_adm_settings":false,"sys_adm_monitoring":false,"sys_adm_modules":false,"sys_search_news":true,"sys_search_comments":false,"sys_news_like":false,"sys_adm_m_g_main":false,"sys_adm_m_g_news":false,"sys_adm_m_g_users":false,"sys_adm_m_g_menu":false,"sys_adm_m_g_settings":false,"sys_adm_m_i_news":false,"sys_adm_m_i_news_cats":false,"sys_adm_m_i_comments":false,"sys_adm_m_i_news_views":false,"sys_adm_m_i_news_votes":false,"sys_adm_m_i_users":false,"sys_adm_m_i_groups":false,"sys_adm_m_i_permissions":false,"sys_adm_m_i_menu":false,"sys_adm_m_i_menu_adm":false,"sys_adm_m_i_menu_groups_adm":false,"sys_adm_m_i_icons":false,"sys_adm_m_i_statics":false,"sys_adm_m_i_settings":false,"sys_adm_m_i_monitor":false,"sys_adm_m_i_info":false,"sys_adm_m_i_modules":false,"sys_adm_m_i_logs":false,"sys_adm_manager":false,"sys_adm_logs":false,"sys_adm_permissions_add":false,"sys_adm_permissions_edit":false,"sys_adm_permissions_delete":false,"sys_adm_comments_add":false,"sys_adm_comments_edit":false,"sys_adm_comments_delete":false,"sys_adm_groups_add":false,"sys_adm_groups_edit":false,"sys_adm_groups_delete":false,"sys_adm_menu_add":false,"sys_adm_menu_edit":false,"sys_adm_menu_delete":false,"sys_adm_menu_adm_add":false,"sys_adm_menu_adm_edit":false,"sys_adm_menu_adm_delete":false,"sys_adm_menu_groups_add":false,"sys_adm_menu_groups_edit":false,"sys_adm_menu_groups_delete":false,"sys_adm_menu_icons_add":false,"sys_adm_menu_icons_edit":false,"sys_adm_menu_icons_delete":false,"sys_adm_monitoring_add":false,"sys_adm_monitoring_edit":false,"sys_adm_monitoring_delete":false,"sys_adm_news_add":false,"sys_adm_news_edit":false,"sys_adm_news_delete":false,"sys_adm_news_cats_add":false,"sys_adm_news_cats_edit":false,"sys_adm_news_cats_delete":false,"sys_adm_statics_add":false,"sys_adm_statics_edit":false,"sys_adm_statics_delete":false,"sys_adm_users_add":false,"sys_adm_users_edit":false,"sys_adm_users_delete":false,"sys_adm_users_ban":false,"sys_adm_modules_edit":false,"sys_adm_news_views_delete":false,"sys_adm_news_votes_delete":false,"sys_adm_blocks":false,"sys_adm_m_i_blocks":false,"sys_adm_blocks_edit":false,"block_online":true,"block_banner":false}'),
(2, 'Пользователь', 'Зарегистрированные и проверенные пользователи', '', '{"color":0,"sys_debug":false,"sys_max_file_size":1024,"sys_max_ratio":1,"sys_monitoring":true,"sys_share":true,"sys_search":true,"sys_restore":true,"sys_register":true,"sys_profile":true,"sys_profile_del_skin":true,"sys_profile_del_cloak":true,"sys_profile_skin":true,"sys_profile_cloak":true,"sys_profile_settings":true,"sys_news_list":true,"sys_news_full":true,"sys_comment_list":true,"sys_comment_add":true,"sys_comment_edt":true,"sys_comment_edt_all":false,"sys_comment_del":false,"sys_comment_del_all":false,"sys_auth":true,"sys_adm_main":false,"sys_adm_news":false,"sys_adm_news_cats":false,"sys_adm_news_views":false,"sys_adm_news_votes":false,"sys_adm_comments":false,"sys_adm_menu":false,"sys_adm_menu_adm":false,"sys_adm_menu_groups":false,"sys_adm_menu_icons":false,"sys_adm_users":false,"sys_adm_groups":false,"sys_adm_permissions":false,"sys_adm_statics":false,"sys_adm_info":false,"sys_adm_settings":false,"sys_adm_monitoring":false,"sys_adm_modules":false,"sys_search_news":true,"sys_search_comments":true,"sys_news_like":true,"sys_adm_m_g_main":false,"sys_adm_m_g_news":false,"sys_adm_m_g_users":false,"sys_adm_m_g_menu":false,"sys_adm_m_g_settings":false,"sys_adm_m_i_news":false,"sys_adm_m_i_news_cats":false,"sys_adm_m_i_comments":false,"sys_adm_m_i_news_views":false,"sys_adm_m_i_news_votes":false,"sys_adm_m_i_users":false,"sys_adm_m_i_groups":false,"sys_adm_m_i_permissions":false,"sys_adm_m_i_menu":false,"sys_adm_m_i_menu_adm":false,"sys_adm_m_i_menu_groups_adm":false,"sys_adm_m_i_icons":false,"sys_adm_m_i_statics":false,"sys_adm_m_i_settings":false,"sys_adm_m_i_monitor":false,"sys_adm_m_i_info":false,"sys_adm_m_i_modules":false,"sys_adm_m_i_logs":false,"sys_adm_manager":false,"sys_adm_logs":false,"sys_adm_permissions_add":false,"sys_adm_permissions_edit":false,"sys_adm_permissions_delete":false,"sys_adm_comments_add":false,"sys_adm_comments_edit":false,"sys_adm_comments_delete":false,"sys_adm_groups_add":false,"sys_adm_groups_edit":false,"sys_adm_groups_delete":false,"sys_adm_menu_add":false,"sys_adm_menu_edit":false,"sys_adm_menu_delete":false,"sys_adm_menu_adm_add":false,"sys_adm_menu_adm_edit":false,"sys_adm_menu_adm_delete":false,"sys_adm_menu_groups_add":false,"sys_adm_menu_groups_edit":false,"sys_adm_menu_groups_delete":false,"sys_adm_menu_icons_add":false,"sys_adm_menu_icons_edit":false,"sys_adm_menu_icons_delete":false,"sys_adm_monitoring_add":false,"sys_adm_monitoring_edit":false,"sys_adm_monitoring_delete":false,"sys_adm_news_add":false,"sys_adm_news_edit":false,"sys_adm_news_delete":false,"sys_adm_news_cats_add":false,"sys_adm_news_cats_edit":false,"sys_adm_news_cats_delete":false,"sys_adm_statics_add":false,"sys_adm_statics_edit":false,"sys_adm_statics_delete":false,"sys_adm_users_add":false,"sys_adm_users_edit":false,"sys_adm_users_delete":false,"sys_adm_users_ban":false,"sys_adm_modules_edit":false,"sys_adm_news_views_delete":false,"sys_adm_news_votes_delete":false,"sys_adm_blocks":false,"sys_adm_m_i_blocks":false,"sys_adm_blocks_edit":false,"block_online":true,"block_banner":false}'),
(3, 'Администратор', 'Группа администрации', '', '{"color":0,"sys_debug":true,"sys_max_file_size":4096,"sys_max_ratio":32,"sys_monitoring":true,"sys_share":true,"sys_search":true,"sys_restore":true,"sys_register":true,"sys_profile":true,"sys_profile_del_skin":true,"sys_profile_del_cloak":true,"sys_profile_skin":true,"sys_profile_cloak":true,"sys_profile_settings":true,"sys_news_list":true,"sys_news_full":true,"sys_comment_list":true,"sys_comment_add":true,"sys_comment_edt":true,"sys_comment_edt_all":true,"sys_comment_del":true,"sys_comment_del_all":true,"sys_auth":true,"sys_adm_main":true,"sys_adm_news":true,"sys_adm_news_cats":true,"sys_adm_news_views":true,"sys_adm_news_votes":true,"sys_adm_comments":true,"sys_adm_menu":true,"sys_adm_menu_adm":true,"sys_adm_menu_groups":true,"sys_adm_menu_icons":true,"sys_adm_users":true,"sys_adm_groups":true,"sys_adm_permissions":true,"sys_adm_statics":true,"sys_adm_info":true,"sys_adm_settings":true,"sys_adm_monitoring":true,"sys_adm_modules":true,"sys_search_news":true,"sys_search_comments":true,"sys_news_like":true,"sys_adm_m_g_main":true,"sys_adm_m_g_news":true,"sys_adm_m_g_users":true,"sys_adm_m_g_menu":true,"sys_adm_m_g_settings":true,"sys_adm_m_i_news":true,"sys_adm_m_i_news_cats":true,"sys_adm_m_i_comments":true,"sys_adm_m_i_news_views":true,"sys_adm_m_i_news_votes":true,"sys_adm_m_i_users":true,"sys_adm_m_i_groups":true,"sys_adm_m_i_permissions":true,"sys_adm_m_i_menu":true,"sys_adm_m_i_menu_adm":true,"sys_adm_m_i_menu_groups_adm":true,"sys_adm_m_i_icons":true,"sys_adm_m_i_statics":true,"sys_adm_m_i_settings":true,"sys_adm_m_i_monitor":true,"sys_adm_m_i_info":true,"sys_adm_m_i_modules":true,"sys_adm_m_i_logs":true,"sys_adm_manager":true,"sys_adm_logs":true,"sys_adm_permissions_add":true,"sys_adm_permissions_edit":true,"sys_adm_permissions_delete":true,"sys_adm_comments_add":true,"sys_adm_comments_edit":true,"sys_adm_comments_delete":true,"sys_adm_groups_add":true,"sys_adm_groups_edit":true,"sys_adm_groups_delete":true,"sys_adm_menu_add":true,"sys_adm_menu_edit":true,"sys_adm_menu_delete":true,"sys_adm_menu_adm_add":true,"sys_adm_menu_adm_edit":true,"sys_adm_menu_adm_delete":true,"sys_adm_menu_groups_add":true,"sys_adm_menu_groups_edit":true,"sys_adm_menu_groups_delete":true,"sys_adm_menu_icons_add":true,"sys_adm_menu_icons_edit":true,"sys_adm_menu_icons_delete":true,"sys_adm_monitoring_add":true,"sys_adm_monitoring_edit":true,"sys_adm_monitoring_delete":true,"sys_adm_news_add":true,"sys_adm_news_edit":true,"sys_adm_news_delete":true,"sys_adm_news_cats_add":true,"sys_adm_news_cats_edit":true,"sys_adm_news_cats_delete":true,"sys_adm_statics_add":true,"sys_adm_statics_edit":true,"sys_adm_statics_delete":true,"sys_adm_users_add":true,"sys_adm_users_edit":true,"sys_adm_users_delete":true,"sys_adm_users_ban":true,"sys_adm_modules_edit":true,"sys_adm_news_views_delete":true,"sys_adm_news_votes_delete":true,"sys_adm_blocks":true,"sys_adm_m_i_blocks":true,"sys_adm_blocks_edit":true,"block_online":true,"block_banner":true}');
#line
CREATE TABLE IF NOT EXISTS `~ic~` (
  `~ic_id~` int(10) NOT NULL AUTO_INCREMENT,
  `~ic_login~` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `~ic_money~` decimal(10,2) NOT NULL DEFAULT '0.00',
  `~ic_rc~` decimal(10,2) NOT NULL DEFAULT '0.00',
  `~ic_bank~` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`~ic_id~`),
  KEY `~ic_login~` (`~ic_login~`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `~logs~` (
  `~logs_id~` int(10) NOT NULL AUTO_INCREMENT,
  `~logs_uid~` int(10) NOT NULL DEFAULT '0',
  `~logs_msg~` varchar(255) NOT NULL DEFAULT '',
  `~logs_date~` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`~logs_id~`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `parent` int(10) NOT NULL DEFAULT '1',
  `url` varchar(255) NOT NULL DEFAULT '',
  `target` varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '_self',
  `permissions` varchar(255) NOT NULL DEFAULT '',
  `style` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_online` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `date_create` int(10) NOT NULL DEFAULT '0',
  `date_update` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
INSERT INTO `mcr_menu` (`id`, `title`, `parent`, `url`, `target`, `permissions`) VALUES
(1, 'Главная', 0, '~base_url~', '_self', 'sys_share'),
(2, 'ПУ', 0, '~base_url~?mode=admin', '_self', 'sys_adm_main');
#line
CREATE TABLE IF NOT EXISTS `mcr_menu_adm` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gid` int(10) NOT NULL DEFAULT '1',
  `title` varchar(24) NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `target` varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '_self',
  `access` text CHARACTER SET latin1 NOT NULL,
  `priority` int(6) NOT NULL DEFAULT '1',
  `icon` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;
#line
INSERT INTO `mcr_menu_adm` (`id`, `gid`, `title`, `text`, `url`, `target`, `access`, `priority`, `icon`) VALUES
(1, 1, 'Информация', 'Информация и статистика движка', '~base_url~?mode=admin&do=info', '_self', 'sys_adm_m_i_info', 1, 8),
(2, 2, 'Новости', 'Управление списком новостей', '~base_url~?mode=admin&do=news', '_self', 'sys_adm_m_i_news', 1, 2),
(3, 2, 'Категории', 'Управление категориями новостей', '~base_url~?mode=admin&do=news_cats', '_self', 'sys_adm_m_i_news_cats', 2, 10),
(4, 2, 'Комментарии', 'Управление комментариями новостей', '~base_url~?mode=admin&do=comments', '_self', 'sys_adm_m_i_comments', 3, 13),
(5, 2, 'Просмотры', 'Управление просмотрами новостей', '~base_url~?mode=admin&do=news_views', '_self', 'sys_adm_m_i_news_views', 4, 14),
(6, 2, 'Голоса', 'Управление голосами новостей', '~base_url~?mode=admin&do=news_votes', '_self', 'sys_adm_m_i_news_votes', 5, 9),
(7, 3, 'Пользователи', 'Изменение пользователей', '~base_url~?mode=admin&do=users', '_self', 'sys_adm_m_i_users', 1, 5),
(8, 3, 'Группы', 'Управление группами пользователей и их привилегиями', '~base_url~?mode=admin&do=groups', '_self', 'sys_adm_m_i_groups', 2, 15),
(9, 3, 'Привилегии', 'Управление доступными привилегиями', '~base_url~?mode=admin&do=permissions', '_self', 'sys_adm_m_i_permissions', 3, 17),
(10, 4, 'Меню сайта', 'Управление пунктами основного меню', '~base_url~?mode=admin&do=menu', '_self', 'sys_adm_m_i_menu', 1, 7),
(11, 4, 'Меню ПУ', 'Управление пунктами меню панели управления', '~base_url~?mode=admin&do=menu_adm', '_self', 'sys_adm_m_i_menu_adm', 2, 24),
(12, 4, 'Группы меню ПУ', 'Управление группами меню панели управления', '~base_url~?mode=admin&do=menu_groups', '_self', 'sys_adm_m_i_menu_groups_adm', 3, 11),
(13, 4, 'Иконки', 'Управление иконками пунктов меню панели управления', '~base_url~?mode=admin&do=menu_icons', '_self', 'sys_adm_m_i_icons', 4, 19),
(14, 1, 'Статические страницы', 'Управление статическими страницами ', '~base_url~?mode=admin&do=statics', '_self', 'sys_adm_m_i_statics', 2, 20),
(15, 5, 'Настройки сайта', 'Основные настройки сайта', '~base_url~?mode=admin&do=settings', '_self', 'sys_adm_m_i_settings', 1, 6),
(16, 1, 'Мониторинг серверов', 'Управление серверами мониторинга', '~base_url~?mode=admin&do=monitoring', '_self', 'sys_adm_m_i_monitor', 3, 21),
(17, 1, 'Модули', 'Управление модулями', '~base_url~?mode=admin&do=modules', '_self', 'sys_adm_m_i_modules', 4, 22),
(18, 1, 'Лог действий', 'Журнал действий пользователей', '~base_url~?mode=admin&do=logs', '_self', 'sys_adm_m_i_logs', 5, 23),
(19, 1, 'Блоки', 'Управление Блоками', '~base_url~?mode=admin&do=blocks', '_self', 'sys_adm_m_i_blocks', 6, 18);
#line
CREATE TABLE IF NOT EXISTS `mcr_menu_adm_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `text` varchar(255) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL DEFAULT '',
  `access` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `priority` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
#line
INSERT INTO `mcr_menu_adm_groups` (`id`, `title`, `text`, `access`, `priority`) VALUES
(1, 'Разное', 'Описание раздела разное', 'sys_adm_m_g_main', 1),
(2, 'Управление новостями', 'Всё, что связано с модулем новостей', 'sys_adm_m_g_news', 2),
(3, 'Управление пользователями', 'Управление пользователями', 'sys_adm_m_g_users', 3),
(4, 'Управление меню', 'Управление группами и пунктами меню сайта и панели управления', 'sys_adm_m_g_menu', 4),
(5, 'Настройки', 'Настройки сайта и движка', 'sys_adm_m_g_settings', 6);
#line
CREATE TABLE IF NOT EXISTS `mcr_menu_adm_icons` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `img` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;
#line
INSERT INTO `mcr_menu_adm_icons` (`id`, `title`, `img`) VALUES
(1, 'По умолчанию', 'default.png'),
(2, 'Новости', 'news.png'),
(3, 'Пазл', 'puzzle.png'),
(4, 'Пользователь', 'user.png'),
(5, 'Пользователи', 'users.png'),
(6, 'Молоток и гаечный ключ', 'settings.png'),
(7, 'Древо', 'tree.png'),
(8, 'Диаграмма', 'diagram.png'),
(9, 'Лайк', 'like.png'),
(10, 'Документы', 'documents.png'),
(11, 'Иерархия', 'hierarchy.png'),
(12, 'Шестеренка', 'wheel.png'),
(13, 'Комментарии', 'comments.png'),
(14, 'Глаз', 'eye.png'),
(15, 'Группа пользователей', 'groups.png'),
(16, 'График', 'chart.png'),
(17, 'Замок', 'lock.png'),
(18, 'Блоки', 'blocks.png'),
(19, 'Иконка', 'icon.png'),
(20, 'Два листа', 'pages.png'),
(21, 'Монитор', 'monitor.png'),
(22, 'Сеть', 'network.png'),
(23, 'Открытая книга', 'logs.png'),
(24, 'Пустое древо', 'gealogy.png');
#line
CREATE TABLE IF NOT EXISTS `mcr_monitoring` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `text` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `port` int(6) NOT NULL DEFAULT '25565',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `version` varchar(64) NOT NULL DEFAULT '',
  `online` int(10) NOT NULL DEFAULT '0',
  `slots` int(10) NOT NULL DEFAULT '0',
  `players` text NOT NULL,
  `motd` text NOT NULL,
  `plugins` text NOT NULL,
  `map` varchar(64) NOT NULL DEFAULT '',
  `last_error` text NOT NULL,
  `last_update` int(10) NOT NULL DEFAULT '0',
  `updater` int(10) NOT NULL DEFAULT '60',
  `type` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT 'MineToolsAPIPing',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL DEFAULT '1' COMMENT 'ID категории',
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT 'Название новости',
  `text_bb` longtext NOT NULL COMMENT 'Текст полного описание(необработанный)',
  `text_html` longtext NOT NULL COMMENT 'Текст полного описание(обработанный)',
  `text_bb_short` text NOT NULL COMMENT 'Текст краткого описание(необработанный)',
  `text_html_short` text NOT NULL COMMENT 'Текст краткого описание(обработанный)',
  `vote` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активатор лайков',
  `discus` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активатор комметариев',
  `attach` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активатор закрепления',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT 'ID добавившего пользователя',
  `data` text NOT NULL COMMENT 'Сведения о новости',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `uid` (`uid`),
  KEY `cid_2` (`cid`),
  KEY `uid_2` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news_cats` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
#line
INSERT INTO `mcr_news_cats` (`id`, `title`, `description`, `data`) VALUES
(1, 'Без категории', 'Без категории', '{"time_create":1005553535,"time_last":1005553535,"user":"admin"}');
#line
CREATE TABLE IF NOT EXISTS `mcr_news_views` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '-1',
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`),
  KEY `nid_2` (`nid`),
  KEY `uid_2` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news_votes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '-1',
  `value` tinyint(1) NOT NULL DEFAULT '1',
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`),
  KEY `nid_2` (`nid`),
  KEY `uid_2` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_permissions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT 'boolean',
  `default` varchar(32) NOT NULL DEFAULT 'false',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=110 ;
#line
INSERT INTO `mcr_permissions` (`id`, `title`, `description`, `value`, `system`, `type`, `default`, `data`) VALUES
(1, 'Доступ к отладке', 'Дает доступ к системной информации для устранения и выявления неисправностей', 'sys_debug', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(2, 'Максимальный размер файла', 'Максимально допустимый размер загружаемого файла(КБ)', 'sys_max_file_size', 1, 'float', '1024', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(3, 'Максимальное соотношение', 'Максимальное соотношение скинов и плащей. Подробнее в документации.', 'sys_max_ratio', 1, 'integer', '0', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(4, 'Доступ к мониторингу', 'Доступ к просмотру блока мониторинга серверов', 'sys_monitoring', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(5, 'Общий доступ', 'Доступ к общедоступным элементам', 'sys_share', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(6, 'Основной доступ к поиску', 'Если запрещено, то доступ к подмодулям будет так же недоступен, независимо от их настроек.', 'sys_search', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(7, 'Восстановление пароля', 'Доступ к восстановлению пароля', 'sys_restore', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(8, 'Доступ к регистрации', 'Позволяет выбранным группам пользователей регистрироваться на сайте', 'sys_register', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(9, 'Доступ к своему профилю', 'Просмотр собственного профиля и информации о себе', 'sys_profile', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(10, 'Удаление скина персонажа', 'Доступ к удалению скина персонажа', 'sys_profile_del_skin', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(11, 'Удаление плаща персонажа', 'Доступ к удалению плаща персонажа', 'sys_profile_del_cloak', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(12, 'Изменение скина персонажа', 'Доступ к изменению скина персонажа', 'sys_profile_skin', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(13, 'Изменение плаща персонажа', 'Доступ к изменению плаща персонажа', 'sys_profile_cloak', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(14, 'Настройки пользователя', 'Доступ к настройкам пользователя', 'sys_profile_settings', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(15, 'Просмотр списка новостей', 'Доступ к просмотрю списка всех новостей', 'sys_news_list', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(16, 'Просмотр полных новостей', 'Доступ к просмотру полных новостей', 'sys_news_full', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(17, 'Просмотр комментариев', 'Доступ к просмотру комментариев', 'sys_comment_list', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(18, 'Добавление комментариев', 'Доступ к добавлению комментариев', 'sys_comment_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(19, 'Редактирование своих комментариев', 'Доступ к редактированию собственных комментариев', 'sys_comment_edt', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(20, 'Редактирование всех комментариев', 'Доступ к редактированию комментариев всех пользователей, включая свои независимо от доступа к редактированию своих комментариев', 'sys_comment_edt_all', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(21, 'Удаление своих комментариев', 'Доступ к удалению собственных комментариев', 'sys_comment_del', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(22, 'Удаление всех комментариев', 'Доступ к удалению комментариев всех пользователей, включая свои независимо от доступа к удалению своих комментариев', 'sys_comment_del_all', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(23, 'Авторизация', 'Доступ к авторизации пользователей', 'sys_auth', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(24, 'Доступ к ПУ', 'Основной доступ к панели управления. Если запрещено, то доступ ко всем элементам ПУ будет закрыт.', 'sys_adm_main', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(25, 'Управление новостями', 'Управление новостями: добавление, удаление, редактирование', 'sys_adm_news', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(26, 'Управление категориями', 'Управление категориями новостей: добавление, удаление, редактирование', 'sys_adm_news_cats', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(27, 'Управление просмотрами новостей', 'Управление просмотрами новостей: удаление', 'sys_adm_news_views', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(28, 'Управление просмотрами новостей', 'Управление просмотрами новостей: удаление', 'sys_adm_news_votes', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(29, 'Управление комментариями', 'Управление комментариями: добавление, удаление, редактирование', 'sys_adm_comments', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(30, 'Управление меню сайта', 'Управление меню сайта: добавление, редактирование, удаление', 'sys_adm_menu', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(31, 'Управление меню ПУ', 'Управление меню ПУ: добавление, редактирование, удаление', 'sys_adm_menu_adm', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(32, 'Управление группами меню ПУ', 'Управление группами меню ПУ: добавление, редактирование, удаление', 'sys_adm_menu_groups', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(33, 'Управление иконками меню ПУ', 'Управление иконками меню ПУ: добавление, редактирование, удаление', 'sys_adm_menu_icons', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(34, 'Управление пользователями', 'Управление пользователями: добавление, редактирование, удаление, бан, разбан', 'sys_adm_users', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(35, 'Управление группами пользователей', 'Управление группами пользователей: добавление, редактирование, удаление', 'sys_adm_groups', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(36, 'Управление привилегиями', 'Управление привилегиями: добавление, редактирование, удаление', 'sys_adm_permissions', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(37, 'Управление статическими страницами', 'Управление статическими страницами: добавление, редактирование, удаление', 'sys_adm_statics', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(38, 'Информация о движке', 'Доступ к информации и статистике движка', 'sys_adm_info', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(39, 'Настройки сайта', 'Доступ к настройкам сайта', 'sys_adm_settings', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(40, 'Управление мониторингом', 'Управление мониторингом: добавление, редактирование, удаление', 'sys_adm_monitoring', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(41, 'Управление модулями', 'Доступ к управлению модулями', 'sys_adm_modules', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(42, 'Поиск по новостям', 'Доступ к поиску по новостям', 'sys_search_news', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(43, 'Поиск по комментариям', 'Доступ к поиску по комментариям', 'sys_search_comments', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(44, 'Голосование за новость', 'Доступ к голосованию за новость (Лайки/Дизлайки)', 'sys_news_like', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(45, 'Группа меню "Разное"', 'Доступ к группе меню "Разное" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_main', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(46, 'Группа меню "Управление новостями"', 'Доступ к группе меню "Управление новостями" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_news', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(47, 'Группа меню "Управление пользователями"', 'Доступ к группе меню "Управление пользователями" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_users', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(48, 'Группа меню "Управление меню"', 'Доступ к группе меню "Управление меню" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_menu', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(49, 'Группа меню "Настройки"', 'Доступ к группе меню "Настройки" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_settings', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(50, 'Пункт меню "Новости"', 'Доступ к пункту меню "Новости" в панели управления.', 'sys_adm_m_i_news', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(51, 'Пункт меню "Категории"', 'Доступ к пункту меню "Категории" в панели управления.', 'sys_adm_m_i_news_cats', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(52, 'Пункт меню "Комментарии"', 'Доступ к пункту меню "Комментарии" в панели управления.', 'sys_adm_m_i_comments', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(53, 'Пункт меню "Просмотры"', 'Доступ к пункту меню "Просмотры" в панели управления.', 'sys_adm_m_i_news_views', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(54, 'Пункт меню "Голоса"', 'Доступ к пункту меню "Голоса" в панели управления.', 'sys_adm_m_i_news_votes', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(55, 'Пункт меню "Пользователи"', 'Доступ к пункту меню "Пользователи" в панели управления.', 'sys_adm_m_i_users', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(56, 'Пункт меню "Группы"', 'Доступ к пункту меню "Группы" в панели управления.', 'sys_adm_m_i_groups', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(57, 'Пункт меню "Привилегии"', 'Доступ к пункту меню "Привилегии" в панели управления.', 'sys_adm_m_i_permissions', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(58, 'Пункт меню "Меню сайта"', 'Доступ к пункту меню "Меню сайта" в панели управления.', 'sys_adm_m_i_menu', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(59, 'Пункт меню "Меню ПУ"', 'Доступ к пункту меню "Меню ПУ" в панели управления.', 'sys_adm_m_i_menu_adm', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(60, 'Пункт меню "Группы меню ПУ"', 'Доступ к пункту меню "Группы меню ПУ" в панели управления.', 'sys_adm_m_i_menu_groups_adm', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(61, 'Пункт меню "Иконки"', 'Доступ к пункту меню "Иконки" в панели управления.', 'sys_adm_m_i_icons', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(62, 'Пункт меню "Статические страницы"', 'Доступ к пункту меню "Статические страницы" в панели управления.', 'sys_adm_m_i_statics', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(63, 'Пункт меню "Настройки сайта"', 'Доступ к пункту меню "Настройки сайта" в панели управления.', 'sys_adm_m_i_settings', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(64, 'Пункт меню "Мониторинг"', 'Доступ к пункту меню "Мониторинг" в панели управления.', 'sys_adm_m_i_monitor', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(65, 'Пункт меню "Информация"', 'Доступ к пункту меню "Информация" в панели управления.', 'sys_adm_m_i_info', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(66, 'Пункт меню "Модули"', 'Доступ к пункту меню "Модули" в панели управления.', 'sys_adm_m_i_modules', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(67, 'Пункт меню "Логи"', 'Доступ к пункту меню "Логи" в панели управления.', 'sys_adm_m_i_logs', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(68, 'Файловый загрузчик', 'Доступ к файловому загрузчику', 'sys_adm_manager', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(69, 'Управление логами', 'Доступ к логам пользователей', 'sys_adm_logs', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(70, 'Добавление привилегий', 'Доступ к добавлению привилегий', 'sys_adm_permissions_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(71, 'Редактирование привилегий', 'Доступ к редактированию привилегий', 'sys_adm_permissions_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(72, 'Удаление привилегий', 'Доступ к удалению привилегий', 'sys_adm_permissions_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(73, 'Добавление комментариев в ПУ', 'Доступ к добавлению комментариев в ПУ', 'sys_adm_comments_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(74, 'Редактирование комментариев в ПУ', 'Доступ к редактирование комментариев в ПУ', 'sys_adm_comments_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(75, 'Удаление комментариев в ПУ', 'Доступ к удалению комментариев в ПУ', 'sys_adm_comments_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(76, 'Добавление групп пользователей', 'Доступ к добавлению групп пользователей', 'sys_adm_groups_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(77, 'Редактирование групп пользователей', 'Доступ к редактированию групп пользователей', 'sys_adm_groups_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(78, 'Удаление групп пользователей', 'Доступ к удалению групп пользователей', 'sys_adm_groups_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(79, 'Добавление пунктов меню', 'Доступ к добавлению пунктов меню', 'sys_adm_menu_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(80, 'Редактирование пунктов меню', 'Доступ к редактированию пунктов меню', 'sys_adm_menu_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(81, 'Удаление пунктов меню', 'Доступ к удалению пунктов меню', 'sys_adm_menu_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(82, 'Добавление пунктов меню панели управления', 'Доступ к добавлению пунктов меню панели управления', 'sys_adm_menu_adm_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(83, 'Редактирование пунктов меню панели управления', 'Доступ к редактированию пунктов меню панели управления', 'sys_adm_menu_adm_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(84, 'Удаление пунктов меню панели управления', 'Доступ к удалению пунктов меню панели управления', 'sys_adm_menu_adm_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(85, 'Добавление групп меню панели управления', 'Доступ к добавлению групп меню панели управления', 'sys_adm_menu_groups_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(86, 'Редактирование групп меню панели управления', 'Доступ к редактированию групп меню панели управления', 'sys_adm_menu_groups_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(87, 'Удаление групп меню панели управления', 'Доступ к удаление групп меню панели управления', 'sys_adm_menu_groups_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(88, 'Добавление иконок', 'Доступ к добавлению иконок', 'sys_adm_menu_icons_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(89, 'Редактирование иконок', 'Доступ к редактированию иконок', 'sys_adm_menu_icons_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(90, 'Удаление иконок', 'Доступ к удалению иконок', 'sys_adm_menu_icons_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(91, 'Добавление серверов в мониторинг', 'Доступ к добавлению серверов в мониторинге', 'sys_adm_monitoring_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(92, 'Редактирование серверов в мониторинге', 'Доступ к редактированию серверов в мониторинге', 'sys_adm_monitoring_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(93, 'Удаление серверов из мониторинга', 'Доступ к удалению серверов из мониторинга', 'sys_adm_monitoring_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(94, 'Добавление новостей', 'Доступ к добавлению новостей', 'sys_adm_news_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(95, 'Редактирование новостей', 'Доступ к редактированию новостей', 'sys_adm_news_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(96, 'Удаление новостей', 'Доступ к удалению новостей', 'sys_adm_news_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(97, 'Добавление категорий новостей', 'Доступ к добавлению категорий новостей', 'sys_adm_news_cats_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(98, 'Редактирование категорий новостей', 'Доступ к редактированию категорий новостей', 'sys_adm_news_cats_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(99, 'Удаление категорий новостей', 'Доступ к удалению категорий новостей', 'sys_adm_news_cats_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(100, 'Добавление статических страниц', 'Доступ к добавлению статических страниц', 'sys_adm_statics_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(101, 'Редактирование статических страниц', 'Доступ к редактированию статических страниц', 'sys_adm_statics_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(102, 'Удаление статических страниц', 'Доступ к удалению статических страниц', 'sys_adm_statics_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(103, 'Добавление пользователей', 'Доступ к добавлению пользователей', 'sys_adm_users_add', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(104, 'Редактирование пользователей', 'Доступ к редактированию пользователей', 'sys_adm_users_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(105, 'Удаление пользователей', 'Доступ к удалению пользователей', 'sys_adm_users_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(106, 'Бан/разбан пользователей', 'Доступ к банам и разбанам пользователей', 'sys_adm_users_ban', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(107, 'Редактирование модулей', 'Доступ к редактированию модулей', 'sys_adm_modules_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(108, 'Удаление просмотров', 'Доступ к удалению просмотров новостей', 'sys_adm_news_views_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(109, 'Удаление голосов', 'Доступ к удалению голосов новостей', 'sys_adm_news_votes_delete', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(110, 'Управление блоками', 'Доступ к управлению блоками', 'sys_adm_blocks', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(111, 'Пункт меню "Блоки"', 'Доступ к пункту меню "Блоки" в панели управления.', 'sys_adm_m_i_blocks', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(112, 'Редактирование блоков', 'Доступ к редактированию блоков', 'sys_adm_blocks_edit', 1, 'boolean', 'false', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(113, 'Доступ к блоку онлайна', 'Дает доступ к просмотру блока онлайн статистики', 'block_online', 0, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}'),
(114, 'Доступ к блоку баннера', 'Дает доступ к просмотру блока баннера', 'block_banner', 1, 'boolean', 'true', '{"time_create":1005553535,"time_last":1005553535,"login_create":"admin","login_last":"admin"}');
#line
CREATE TABLE IF NOT EXISTS `mcr_statics` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniq` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `title` varchar(64) NOT NULL DEFAULT '',
  `text_bb` longtext NOT NULL,
  `text_html` longtext NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  `permissions` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`uniq`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `~us~` (
  `~us_id~` int(11) NOT NULL AUTO_INCREMENT,
  `~us_gid~` int(11) NOT NULL DEFAULT '1',
  `~us_login~` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `~us_email~` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `~us_pass~` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `~us_uuid~` varchar(64) NOT NULL DEFAULT '',
  `~us_salt~` varchar(128) NOT NULL DEFAULT '',
  `~us_tmp~` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `~us_is_skin~` tinyint(1) NOT NULL DEFAULT '0',
  `~us_is_cloak~` tinyint(1) NOT NULL DEFAULT '0',
  `~us_ip_create~` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `~us_ip_last~` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `~us_color~` varchar(24) NOT NULL DEFAULT '',
  `~us_date_reg~` varchar(32) NOT NULL DEFAULT '0',
  `~us_date_last~` varchar(32) NOT NULL DEFAULT '0',
  `~us_fname~` varchar(32) NOT NULL DEFAULT '0',
  `~us_lname~` varchar(32) NOT NULL DEFAULT '0',
  `~us_gender~` varchar(8) NOT NULL DEFAULT '0',
  `~us_bday~` varchar(32) NOT NULL DEFAULT '0',
  `~us_ban_server~` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`~us_id~`),
  UNIQUE KEY `~us_login~` (`~us_login~`,`~us_email~`),
  KEY `~us_gid~` (`~us_gid~`),
  KEY `~us_login~_2` (`~us_login~`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
ALTER TABLE `mcr_comments`
  ADD CONSTRAINT `mcr_comments_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `~us~` (`~us_id~`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mcr_comments_ibfk_1` FOREIGN KEY (`nid`) REFERENCES `mcr_news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_files`
  ADD CONSTRAINT `mcr_files_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `~us~` (`~us_id~`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_iconomy`
  ADD CONSTRAINT `mcr_iconomy_ibfk_1` FOREIGN KEY (`login`) REFERENCES `~us~` (`~us_login~`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_menu_adm`
  ADD CONSTRAINT `mcr_menu_adm_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `mcr_menu_adm_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_news`
  ADD CONSTRAINT `mcr_news_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `mcr_news_cats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mcr_news_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `~us~` (`~us_id~`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_news_views`
  ADD CONSTRAINT `mcr_news_views_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `~us~` (`~us_id~`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mcr_news_views_ibfk_1` FOREIGN KEY (`nid`) REFERENCES `mcr_news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_news_votes`
  ADD CONSTRAINT `mcr_news_votes_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `~us~` (`~us_id~`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mcr_news_votes_ibfk_1` FOREIGN KEY (`nid`) REFERENCES `mcr_news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `mcr_statics`
  ADD CONSTRAINT `mcr_statics_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `~us~` (`~us_id~`) ON DELETE CASCADE ON UPDATE CASCADE;
#line
ALTER TABLE `~us~`
  ADD CONSTRAINT `~us~_ibfk_1` FOREIGN KEY (`~us_gid~`) REFERENCES `~ug~` (`~ug_id~`) ON DELETE CASCADE ON UPDATE CASCADE;
#line