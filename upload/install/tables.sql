CREATE TABLE IF NOT EXISTS `mcr_comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL,
  `text_html` text NOT NULL,
  `text_bb` text NOT NULL,
  `uid` int(10) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_iconomy` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `login` varchar(32) CHARACTER SET latin1 NOT NULL,
  `money` float NOT NULL DEFAULT '0',
  `realmoney` float NOT NULL DEFAULT '0',
  `bank` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `parent` int(10) NOT NULL DEFAULT '1',
  `url` varchar(255) NOT NULL,
  `target` varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '_self',
  `permissions` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_menu_adm` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gid` int(10) NOT NULL DEFAULT '1',
  `title` varchar(24) NOT NULL,
  `text` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `target` varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '_self',
  `access` text CHARACTER SET latin1 NOT NULL,
  `priority` int(6) NOT NULL DEFAULT '1',
  `icon` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_menu_adm_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `text` varchar(255) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
  `access` varchar(64) CHARACTER SET latin1 NOT NULL,
  `priority` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_menu_adm_icons` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `img` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_monitoring` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `text` varchar(255) NOT NULL,
  `ip` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `port` int(6) NOT NULL DEFAULT '25565',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL DEFAULT '1' COMMENT 'ID категории',
  `title` varchar(32) NOT NULL COMMENT 'Название новости',
  `text_bb` longtext NOT NULL COMMENT 'Текст полного описание(необработанный)',
  `text_html` longtext NOT NULL COMMENT 'Текст полного описание(обработанный)',
  `text_bb_short` text NOT NULL COMMENT 'Текст краткого описание(необработанный)',
  `text_html_short` text NOT NULL COMMENT 'Текст краткого описание(обработанный)',
  `vote` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активатор лайков',
  `discus` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активатор комметариев',
  `attach` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активатор закрепления',
  `uid` int(10) NOT NULL COMMENT 'ID добавившего пользователя',
  `data` text NOT NULL COMMENT 'Сведения о новости',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news_cats` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news_views` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '-1',
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_news_votes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '-1',
  `value` tinyint(1) NOT NULL DEFAULT '1',
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_permissions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `value` varchar(32) CHARACTER SET latin1 NOT NULL,
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT 'boolean',
  `default` varchar(32) NOT NULL DEFAULT 'false',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_statics` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniq` varchar(64) CHARACTER SET latin1 NOT NULL,
  `title` varchar(64) NOT NULL,
  `text_bb` longtext NOT NULL,
  `text_html` longtext NOT NULL,
  `uid` int(10) NOT NULL,
  `permissions` varchar(64) CHARACTER SET latin1 NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`uniq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line
CREATE TABLE IF NOT EXISTS `mcr_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT '1',
  `login` varchar(32) CHARACTER SET latin1 NOT NULL,
  `email` varchar(64) CHARACTER SET latin1 NOT NULL,
  `password` varchar(64) CHARACTER SET latin1 NOT NULL,
  `salt` varchar(10) NOT NULL,
  `tmp` varchar(32) CHARACTER SET latin1 NOT NULL,
  `is_skin` tinyint(1) NOT NULL DEFAULT '0',
  `is_cloak` tinyint(1) NOT NULL DEFAULT '0',
  `ip_create` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `ip_last` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '127.0.0.1',
  `data` text NOT NULL,
  `ban_server` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`,`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#line