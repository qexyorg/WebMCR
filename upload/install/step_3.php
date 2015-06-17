<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $lng, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;

		$this->core->title = 'Установка — Шаг #3';

		$bc = array(
			'Установка' => BASE_URL."install/",
			'Шаг #3' => BASE_URL."install/?mode=step_3"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!isset($_SESSION['step_2'])){ $this->core->notify('', '', 4, 'install/?mode=step_2'); }
		if(isset($_SESSION['step_3'])){ $this->core->notify('', '', 4, 'install/?mode=settings'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$method = (intval(@$_POST['method'])<0 || intval(@$_POST['method'])>15) ? 0 : intval(@$_POST['method']);

			if(!preg_match("/^[\w\-]{3,}$/i", @$_POST['login'])){
				$this->core->notify('Ошибка!', 'Логин может состоять только из символов a-zA-Z0-9_- и быть не менее 3-х символов', 2, 'install/?mode=step_3');
			}

			if(mb_strlen(@$_POST['password'], "UTF-8")<6){
				$this->core->notify('Ошибка!', 'Пароль должен быть не менее 6-ти символов', 2, 'install/?mode=step_3');
			}

			if(@$_POST['password'] !== @$_POST['repassword']){
				$this->core->notify('Ошибка!', 'Пароли не совпадают', 2, 'install/?mode=step_3');
			}

			if(!filter_var(@$_POST['email'], FILTER_VALIDATE_EMAIL)){
				$this->core->notify('Ошибка!', 'Неверный формат E-Mail адреса', 2, 'install/?mode=step_3');
			}

			$login = $this->db->safesql(@$_POST['login']);
			$email = $this->db->safesql(@$_POST['email']);

			$salt = $this->db->safesql($this->core->random());
			$password = $this->core->gen_password(@$_POST['password'], $salt, $method);
			$password = $this->db->safesql($password);
			$ip = $this->user->ip;

			$data = array(
				"time_create" => time(),
				"time_last" => time(),
				"firstname" => "",
				"lastname" => "",
				"gender" => 0,
				"birthday" => 0
			);

			$data = $this->db->safesql(json_encode($data));

			$tables = file(MCR_ROOT.'install/tables.sql');

			$string = "";

			foreach($tables as $key => $value){

				$value = trim($value);

				if($value=='#line'){
					$string = trim($string);

					@$this->db->obj->query($string);

					$string = "";
					continue;
				}

				$string .= $value;

			}

			$sql1 = $this->db->query("INSERT INTO `mcr_menu_adm`
										(`gid`, `title`, `text`, `url`, `target`, `access`, `priority`, `icon`)
									VALUES
										(1, 'Информация', 'Информация и статистика движка', '/?mode=admin&do=info', '_self', 'sys_adm_m_i_info', 1, 8),
										(2, 'Новости', 'Управление списком новостей', '/?mode=admin&do=news', '_self', 'sys_adm_m_i_news', 1, 2),
										(2, 'Категории', 'Управление категориями новостей', '/?mode=admin&do=news_cats', '_self', 'sys_adm_m_i_news_cats', 2, 10),
										(2, 'Комментарии', 'Управление комментариями новостей', '/?mode=admin&do=comments', '_self', 'sys_adm_m_i_comments', 3, 13),
										(2, 'Просмотры', 'Управление просмотрами новостей', '/?mode=admin&do=news_views', '_self', 'sys_adm_m_i_news_views', 4, 14),
										(2, 'Голоса', 'Управление голосами новостей', '/?mode=admin&do=news_votes', '_self', 'sys_adm_m_i_news_votes', 5, 9),
										(3, 'Пользователи', 'Изменение пользователей', '/?mode=admin&do=users', '_self', 'sys_adm_m_i_users', 1, 5),
										(3, 'Группы', 'Управление группами пользователей и их привилегиями', '/?mode=admin&do=groups', '_self', 'sys_adm_m_i_groups', 2, 15),
										(3, 'Привилегии', 'Управление доступными привилегиями', '/?mode=admin&do=permissions', '_self', 'sys_adm_m_i_permissions', 3, 17),
										(4, 'Меню сайта', 'Управление пунктами основного меню', '/?mode=admin&do=menu', '_self', 'sys_adm_m_i_menu', 1, 7),
										(4, 'Меню ПУ', 'Управление пунктами меню панели управления', '/?mode=admin&do=menu_adm', '_self', 'sys_adm_m_i_menu_adm', 2, 18),
										(4, 'Группы меню ПУ', 'Управление группами меню панели управления', '/?mode=admin&do=menu_groups', '_self', 'sys_adm_m_i_menu_groups_adm', 3, 11),
										(4, 'Иконки', 'Управление иконками пунктов меню панели управления', '/?mode=admin&do=menu_icons', '_self', 'sys_adm_m_i_icons', 4, 19),
										(1, 'Статические страницы', 'Управление статическими страницами ', '/?mode=admin&do=statics', '_self', 'sys_adm_m_i_statics', 2, 20),
										(5, 'Настройки сайта', 'Основные настройки сайта', '/?mode=admin&do=settings', '_self', 'sys_adm_m_i_settings', 1, 6),
										(1, 'Мониторинг серверов', 'Управление серверами мониторинга', '/?mode=admin&do=monitoring', '_self', 'sys_adm_m_i_monitor', 3, 21)");

			if(!$sql1){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления пунктов меню', 2, 'install/?mode=step_3'); }

			$sql2 = $this->db->query("INSERT INTO `mcr_users`
											(`gid`, `login`, `email`, `password`, `salt`, `ip_create`, `ip_last`, `data`)
										VALUES
											('3', '$login', '$email', '$password', '$salt', '$ip', '$ip', '$data')");

			if(!$sql2){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления администратора', 2, 'install/?mode=step_3'); }

			$sql3 = $this->db->query("INSERT INTO `mcr_menu_adm_icons`
											(`title`, `img`)
										VALUES
											('По умолчанию', 'default.png'),
											('Новости', 'news.png'),
											('Пазл', 'puzzle.png'),
											('Пользователь', 'user.png'),
											('Пользователи', 'users.png'),
											('Молоток и гаечный ключ', 'settings.png'),
											('Древо', 'tree.png'),
											('Диаграмма', 'diagram.png'),
											('Лайк', 'like.png'),
											('Документы', 'documents.png'),
											('Иерархия', 'hierarchy.png'),
											('Шестеренка', 'wheel.png'),
											('Комментарии', 'comments.png'),
											('Глаз', 'eye.png'),
											('Группа пользователей', 'groups.png'),
											('График', 'chart.png'),
											('Замок', 'lock.png'),
											('Блоки', 'blocks.png'),
											('Иконка', 'icon.png'),
											('Два листа', 'pages.png'),
											('Монитор', 'monitor.png')");

			if(!$sql3){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления иконок', 2, 'install/?mode=step_3'); }

			$sql4 = $this->db->query("INSERT INTO `mcr_menu_adm_groups`
											(`title`, `text`, `access`, `priority`)
										VALUES
											('Разное', 'Описание раздела разное', 'sys_adm_m_g_main', 1),
											('Управление новостями', 'Всё, что связано с модулем новостей', 'sys_adm_m_g_news', 2),
											('Управление пользователями', 'Управление пользователями', 'sys_adm_m_g_users', 3),
											('Управление меню', 'Управление группами и пунктами меню сайта и панели управления', 'sys_adm_m_g_menu', 4),
											('Настройки', 'Настройки сайта и движка', 'sys_adm_m_g_settings', 6)");

			if(!$sql4){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления групп меню', 2, 'install/?mode=step_3'); }

			$sql5_data = array(
				"time_create" => time(),
				"time_last" => time(),
				"login_create" => $login,
				"login_last" => $login
			);

			$sql5_data = $this->db->safesql(json_encode($sql5_data));

			$sql5 = $this->db->query("INSERT INTO `mcr_permissions`
											(`title`, `description`, `value`, `system`, `type`, `default`, `data`)
										VALUES
('Доступ к отладке', 'Дает доступ к системной информации для устранения и выявления неисправностей', 'sys_debug', 1, 'boolean', 'true', '$sql5_data'),
('Максимальный размер файла', 'Максимально допустимый размер загружаемого файла(КБ)', 'sys_max_file_size', 1, 'float', '1024', '$sql5_data'),
('Максимальное соотношение', 'Максимальное соотношение скинов и плащей. Подробнее в документации.', 'sys_max_ratio', 1, 'integer', '0', '$sql5_data'),
('Доступ к мониторингу', 'Доступ к просмотру блока мониторинга серверов', 'sys_monitoring', 1, 'boolean', 'true', '$sql5_data'),
('Общий доступ', 'Доступ к общедоступным элементам', 'sys_share', 1, 'boolean', 'true', '$sql5_data'),
('Основной доступ к поиску', 'Если запрещено, то доступ к подмодулям будет так же недоступен, независимо от их настроек.', 'sys_search', 1, 'boolean', 'true', '$sql5_data'),
('Восстановление пароля', 'Доступ к восстановлению пароля', 'sys_restore', 1, 'boolean', 'true', '$sql5_data'),
('Доступ к регистрации', 'Позволяет выбранным группам пользователей регистрироваться на сайте', 'sys_register', 1, 'boolean', 'true', '$sql5_data'),
('Доступ к своему профилю', 'Просмотр собственного профиля и информации о себе', 'sys_profile', 1, 'boolean', 'true', '$sql5_data'),
('Удаление скина персонажа', 'Доступ к удалению скина персонажа', 'sys_profile_del_skin', 1, 'boolean', 'false', '$sql5_data'),
('Удаление плаща персонажа', 'Доступ к удалению плаща персонажа', 'sys_profile_del_cloak', 1, 'boolean', 'false', '$sql5_data'),
('Изменение скина персонажа', 'Доступ к изменению скина персонажа', 'sys_profile_skin', 1, 'boolean', 'false', '$sql5_data'),
('Изменение плаща персонажа', 'Доступ к изменению плаща персонажа', 'sys_profile_cloak', 1, 'boolean', 'false', '$sql5_data'),
('Настройки пользователя', 'Доступ к настройкам пользователя', 'sys_profile_settings', 1, 'boolean', 'false', '$sql5_data'),
('Просмотр списка новостей', 'Доступ к просмотрю списка всех новостей', 'sys_news_list', 1, 'boolean', 'true', '$sql5_data'),
('Просмотр полных новостей', 'Доступ к просмотру полных новостей', 'sys_news_full', 1, 'boolean', 'true', '$sql5_data'),
('Просмотр комментариев', 'Доступ к просмотру комментариев', 'sys_comment_list', 1, 'boolean', 'false', '$sql5_data'),
('Добавление комментариев', 'Доступ к добавлению комментариев', 'sys_comment_add', 1, 'boolean', 'false', '$sql5_data'),
('Редактирование своих комментариев', 'Доступ к редактированию собственных комментариев', 'sys_comment_edt', 1, 'boolean', 'false', '$sql5_data'),
('Редактирование всех комментариев', 'Доступ к редактированию комментариев всех пользователей, включая свои независимо от доступа к редактированию своих комментариев', 'sys_comment_edt_all', 1, 'boolean', 'false', '$sql5_data'),
('Удаление своих комментариев', 'Доступ к удалению собственных комментариев', 'sys_comment_del', 1, 'boolean', 'false', '$sql5_data'),
('Удаление всех комментариев', 'Доступ к удалению комментариев всех пользователей, включая свои независимо от доступа к удалению своих комментариев', 'sys_comment_del_all', 1, 'boolean', 'false', '$sql5_data'),
('Авторизация', 'Доступ к авторизации пользователей', 'sys_auth', 1, 'boolean', 'false', '$sql5_data'),
('Доступ к ПУ', 'Основной доступ к панели управления. Если запрещено, то доступ ко всем элементам ПУ будет закрыт.', 'sys_adm_main', 1, 'boolean', 'false', '$sql5_data'),
('Управление новостями', 'Управление новостями: добавление, удаление, редактирование', 'sys_adm_news', 1, 'boolean', 'false', '$sql5_data'),
('Управление категориями', 'Управление категориями новостей: добавление, удаление, редактирование', 'sys_adm_news_cats', 1, 'boolean', 'false', '$sql5_data'),
('Управление просмотрами новостей', 'Управление просмотрами новостей: удаление', 'sys_adm_news_views', 1, 'boolean', 'false', '$sql5_data'),
('Управление просмотрами новостей', 'Управление просмотрами новостей: удаление', 'sys_adm_news_votes', 1, 'boolean', 'false', '$sql5_data'),
('Управление комментариями', 'Управление комментариями: добавление, удаление, редактирование', 'sys_adm_comments', 1, 'boolean', 'false', '$sql5_data'),
('Управление меню сайта', 'Управление меню сайта: добавление, редактирование, удаление', 'sys_adm_menu', 1, 'boolean', 'false', '$sql5_data'),
('Управление меню ПУ', 'Управление меню ПУ: добавление, редактирование, удаление', 'sys_adm_menu_adm', 1, 'boolean', 'false', '$sql5_data'),
('Управление группами меню ПУ', 'Управление группами меню ПУ: добавление, редактирование, удаление', 'sys_adm_menu_groups', 1, 'boolean', 'false', '$sql5_data'),
('Управление иконками меню ПУ', 'Управление иконками меню ПУ: добавление, редактирование, удаление', 'sys_adm_menu_icons', 1, 'boolean', 'false', '$sql5_data'),
('Управление пользователями', 'Управление пользователями: добавление, редактирование, удаление, бан, разбан', 'sys_adm_users', 1, 'boolean', 'false', '$sql5_data'),
('Управление группами пользователей', 'Управление группами пользователей: добавление, редактирование, удаление', 'sys_adm_groups', 1, 'boolean', 'false', '$sql5_data'),
('Управление привилегиями', 'Управление привилегиями: добавление, редактирование, удаление', 'sys_adm_permissions', 1, 'boolean', 'false', '$sql5_data'),
('Управление статическими страницами', 'Управление статическими страницами: добавление, редактирование, удаление', 'sys_adm_statics', 1, 'boolean', 'false', '$sql5_data'),
('Информация о движке', 'Доступ к информации и статистике движка', 'sys_adm_info', 1, 'boolean', 'false', '$sql5_data'),
('Настройки сайта', 'Доступ к настройкам сайта', 'sys_adm_settings', 1, 'boolean', 'false', '$sql5_data'),
('Управление мониторингом', 'Управление мониторингом: добавление, редактирование, удаление', 'sys_adm_monitoring', 1, 'boolean', 'false', '$sql5_data'),
('Управление модулями', 'Доступ к управлению модулями', 'sys_adm_modules', 1, 'boolean', 'false', '$sql5_data'),
('Поиск по новостям', 'Доступ к поиску по новостям', 'sys_search_news', 1, 'boolean', 'false', '$sql5_data'),
('Поиск по комментариям', 'Доступ к поиску по комментариям', 'sys_search_comments', 1, 'boolean', 'false', '$sql5_data'),
('Голосование за новость', 'Доступ к голосованию за новость (Лайки/Дизлайки)', 'sys_news_like', 1, 'boolean', 'false', '$sql5_data'),
('Группа меню \"Разное\"', 'Доступ к группе меню \"Разное\" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_main', 1, 'boolean', 'false', '$sql5_data'),
('Группа меню \"Управление новостями\"', 'Доступ к группе меню \"Управление новостями\" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_news', 1, 'boolean', 'false', '$sql5_data'),
('Группа меню \"Управление пользователями\"', 'Доступ к группе меню \"Управление пользователями\" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_users', 1, 'boolean', 'false', '$sql5_data'),
('Группа меню \"Управление меню\"', 'Доступ к группе меню \"Управление меню\" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_menu', 1, 'boolean', 'false', '$sql5_data'),
('Группа меню \"Настройки\"', 'Доступ к группе меню \"Настройки\" в панели управления. Входящие в нее пункты будут так же недоступны.', 'sys_adm_m_g_settings', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Новости\"', 'Доступ к пункту меню \"Новости\" в панели управления.', 'sys_adm_m_i_news', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Категории\"', 'Доступ к пункту меню \"Категории\" в панели управления.', 'sys_adm_m_i_news_cats', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Комментарии\"', 'Доступ к пункту меню \"Комментарии\" в панели управления.', 'sys_adm_m_i_comments', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Просмотры\"', 'Доступ к пункту меню \"Просмотры\" в панели управления.', 'sys_adm_m_i_news_views', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Голоса\"', 'Доступ к пункту меню \"Голоса\" в панели управления.', 'sys_adm_m_i_news_votes', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Пользователи\"', 'Доступ к пункту меню \"Пользователи\" в панели управления.', 'sys_adm_m_i_users', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Группы\"', 'Доступ к пункту меню \"Группы\" в панели управления.', 'sys_adm_m_i_groups', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Привилегии\"', 'Доступ к пункту меню \"Привилегии\" в панели управления.', 'sys_adm_m_i_permissions', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Меню сайта\"', 'Доступ к пункту меню \"Меню сайта\" в панели управления.', 'sys_adm_m_i_menu', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Меню ПУ\"', 'Доступ к пункту меню \"Меню ПУ\" в панели управления.', 'sys_adm_m_i_menu_adm', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Группы меню ПУ\"', 'Доступ к пункту меню \"Группы меню ПУ\" в панели управления.', 'sys_adm_m_i_menu_groups_adm', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Иконки\"', 'Доступ к пункту меню \"Иконки\" в панели управления.', 'sys_adm_m_i_icons', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Статические страницы\"', 'Доступ к пункту меню \"Статические страницы\" в панели управления.', 'sys_adm_m_i_statics', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Настройки сайта\"', 'Доступ к пункту меню \"Настройки сайта\" в панели управления.', 'sys_adm_m_i_settings', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Мониторинг\"', 'Доступ к пункту меню \"Мониторинг\" в панели управления.', 'sys_adm_m_i_monitor', 1, 'boolean', 'false', '$sql5_data'),
('Пункт меню \"Информация\"', 'Доступ к пункту меню \"Информация\" в панели управления.', 'sys_adm_m_i_info', 1, 'boolean', 'false', '$sql5_data')");

			if(!$sql5){ $this->core->notify('Ошибка!', 'Произошла ошибка выставления стандартных привилегий', 2, 'install/?mode=step_3'); }

			$url = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install'));

			$sql6 = $this->db->query("INSERT INTO `mcr_menu`
										(`title`, `parent`, `url`, `target`, `permissions`)
									VALUES
										('Главная', 0, '$url', '_self', 'sys_share')");

			if(!$sql6){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления пунктов меню', 2, 'install/?mode=step_3'); }

			$sql7 = $this->db->query("INSERT INTO `mcr_iconomy`
										(`login`, `money`, `realmoney`, `bank`)
									VALUES
										('$login', 0, 0, 0)");

			if(!$sql7){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления поля экономики', 2, 'install/?mode=step_3'); }

			$sql8 = $this->db->query("INSERT INTO `mcr_groups` (`id`, `title`, `description`, `permissions`) VALUES
(4, 'Заблокированный', 'Группа заблокированных пользователей', '{\"sys_debug\":true,\"sys_max_file_size\":0,\"sys_max_ratio\":0,\"sys_monitoring\":true,\"sys_share\":true,\"sys_search\":true,\"sys_restore\":false,\"sys_register\":false,\"sys_profile\":true,\"sys_profile_del_skin\":false,\"sys_profile_del_cloak\":false,\"sys_profile_skin\":false,\"sys_profile_cloak\":false,\"sys_profile_settings\":false,\"sys_news_list\":true,\"sys_news_full\":true,\"sys_comment_list\":false,\"sys_comment_add\":false,\"sys_comment_edt\":false,\"sys_comment_edt_all\":false,\"sys_comment_del\":false,\"sys_comment_del_all\":false,\"sys_auth\":false,\"sys_adm_main\":false,\"sys_adm_news\":false,\"sys_adm_news_cats\":false,\"sys_adm_news_views\":false,\"sys_adm_news_votes\":false,\"sys_adm_comments\":false,\"sys_adm_menu\":false,\"sys_adm_menu_adm\":false,\"sys_adm_menu_groups\":false,\"sys_adm_menu_icons\":false,\"sys_adm_users\":false,\"sys_adm_groups\":false,\"sys_adm_permissions\":false,\"sys_adm_statics\":false,\"sys_adm_info\":false,\"sys_adm_settings\":false,\"sys_adm_monitoring\":false,\"sys_adm_modules\":false,\"sys_search_news\":false,\"sys_search_comments\":false,\"sys_news_like\":false,\"sys_adm_m_g_main\":false,\"sys_adm_m_g_news\":false,\"sys_adm_m_g_users\":false,\"sys_adm_m_g_menu\":false,\"sys_adm_m_g_settings\":false,\"sys_adm_m_i_news\":false,\"sys_adm_m_i_news_cats\":false,\"sys_adm_m_i_comments\":false,\"sys_adm_m_i_news_views\":false,\"sys_adm_m_i_news_votes\":false,\"sys_adm_m_i_users\":false,\"sys_adm_m_i_groups\":false,\"sys_adm_m_i_permissions\":false,\"sys_adm_m_i_menu\":false,\"sys_adm_m_i_menu_adm\":false,\"sys_adm_m_i_menu_groups_adm\":false,\"sys_adm_m_i_icons\":false,\"sys_adm_m_i_statics\":false,\"sys_adm_m_i_settings\":false,\"sys_adm_m_i_monitor\":false,\"sys_adm_m_i_info\":false}'),
(1, 'Непроверенный', 'Группа непроверенных пользователей', '{\"sys_debug\":true,\"sys_max_file_size\":1024,\"sys_max_ratio\":0,\"sys_monitoring\":true,\"sys_share\":true,\"sys_search\":true,\"sys_restore\":false,\"sys_register\":false,\"sys_profile\":true,\"sys_profile_del_skin\":false,\"sys_profile_del_cloak\":false,\"sys_profile_skin\":false,\"sys_profile_cloak\":false,\"sys_profile_settings\":false,\"sys_news_list\":true,\"sys_news_full\":true,\"sys_comment_list\":true,\"sys_comment_add\":false,\"sys_comment_edt\":false,\"sys_comment_edt_all\":false,\"sys_comment_del\":false,\"sys_comment_del_all\":false,\"sys_auth\":true,\"sys_adm_main\":false,\"sys_adm_news\":false,\"sys_adm_news_cats\":false,\"sys_adm_news_views\":false,\"sys_adm_news_votes\":false,\"sys_adm_comments\":false,\"sys_adm_menu\":false,\"sys_adm_menu_adm\":false,\"sys_adm_menu_groups\":false,\"sys_adm_menu_icons\":false,\"sys_adm_users\":false,\"sys_adm_groups\":false,\"sys_adm_permissions\":false,\"sys_adm_statics\":false,\"sys_adm_info\":false,\"sys_adm_settings\":false,\"sys_adm_monitoring\":false,\"sys_adm_modules\":false,\"sys_search_news\":true,\"sys_search_comments\":false,\"sys_news_like\":false,\"sys_adm_m_g_main\":false,\"sys_adm_m_g_news\":false,\"sys_adm_m_g_users\":false,\"sys_adm_m_g_menu\":false,\"sys_adm_m_g_settings\":false,\"sys_adm_m_i_news\":false,\"sys_adm_m_i_news_cats\":false,\"sys_adm_m_i_comments\":false,\"sys_adm_m_i_news_views\":false,\"sys_adm_m_i_news_votes\":false,\"sys_adm_m_i_users\":false,\"sys_adm_m_i_groups\":false,\"sys_adm_m_i_permissions\":false,\"sys_adm_m_i_menu\":false,\"sys_adm_m_i_menu_adm\":false,\"sys_adm_m_i_menu_groups_adm\":false,\"sys_adm_m_i_icons\":false,\"sys_adm_m_i_statics\":false,\"sys_adm_m_i_settings\":false,\"sys_adm_m_i_monitor\":false,\"sys_adm_m_i_info\":false}'),
(2, 'Пользователь', 'Зарегистрированные и проверенные пользователи', '{\"sys_debug\":true,\"sys_max_file_size\":1024,\"sys_max_ratio\":0,\"sys_monitoring\":true,\"sys_share\":true,\"sys_search\":true,\"sys_restore\":false,\"sys_register\":false,\"sys_profile\":true,\"sys_profile_del_skin\":true,\"sys_profile_del_cloak\":false,\"sys_profile_skin\":true,\"sys_profile_cloak\":false,\"sys_profile_settings\":true,\"sys_news_list\":true,\"sys_news_full\":true,\"sys_comment_list\":true,\"sys_comment_add\":true,\"sys_comment_edt\":true,\"sys_comment_edt_all\":false,\"sys_comment_del\":false,\"sys_comment_del_all\":false,\"sys_auth\":true,\"sys_adm_main\":false,\"sys_adm_news\":false,\"sys_adm_news_cats\":false,\"sys_adm_news_views\":false,\"sys_adm_news_votes\":false,\"sys_adm_comments\":false,\"sys_adm_menu\":false,\"sys_adm_menu_adm\":false,\"sys_adm_menu_groups\":false,\"sys_adm_menu_icons\":false,\"sys_adm_users\":false,\"sys_adm_groups\":false,\"sys_adm_permissions\":false,\"sys_adm_statics\":false,\"sys_adm_info\":false,\"sys_adm_settings\":false,\"sys_adm_monitoring\":false,\"sys_adm_modules\":false,\"sys_search_news\":true,\"sys_search_comments\":true,\"sys_news_like\":true,\"sys_adm_m_g_main\":false,\"sys_adm_m_g_news\":false,\"sys_adm_m_g_users\":false,\"sys_adm_m_g_menu\":false,\"sys_adm_m_g_settings\":false,\"sys_adm_m_i_news\":false,\"sys_adm_m_i_news_cats\":false,\"sys_adm_m_i_comments\":false,\"sys_adm_m_i_news_views\":false,\"sys_adm_m_i_news_votes\":false,\"sys_adm_m_i_users\":false,\"sys_adm_m_i_groups\":false,\"sys_adm_m_i_permissions\":false,\"sys_adm_m_i_menu\":false,\"sys_adm_m_i_menu_adm\":false,\"sys_adm_m_i_menu_groups_adm\":false,\"sys_adm_m_i_icons\":false,\"sys_adm_m_i_statics\":false,\"sys_adm_m_i_settings\":false,\"sys_adm_m_i_monitor\":false,\"sys_adm_m_i_info\":false}'),
(3, 'Администратор', 'Группа администрации', '{\"sys_debug\":true,\"sys_max_file_size\":4096,\"sys_max_ratio\":32,\"sys_monitoring\":true,\"sys_share\":true,\"sys_search\":true,\"sys_restore\":true,\"sys_register\":true,\"sys_profile\":true,\"sys_profile_del_skin\":true,\"sys_profile_del_cloak\":true,\"sys_profile_skin\":true,\"sys_profile_cloak\":true,\"sys_profile_settings\":true,\"sys_news_list\":true,\"sys_news_full\":true,\"sys_comment_list\":true,\"sys_comment_add\":true,\"sys_comment_edt\":true,\"sys_comment_edt_all\":true,\"sys_comment_del\":true,\"sys_comment_del_all\":true,\"sys_auth\":true,\"sys_adm_main\":true,\"sys_adm_news\":true,\"sys_adm_news_cats\":true,\"sys_adm_news_views\":true,\"sys_adm_news_votes\":true,\"sys_adm_comments\":true,\"sys_adm_menu\":true,\"sys_adm_menu_adm\":true,\"sys_adm_menu_groups\":true,\"sys_adm_menu_icons\":true,\"sys_adm_users\":true,\"sys_adm_groups\":true,\"sys_adm_permissions\":true,\"sys_adm_statics\":true,\"sys_adm_info\":true,\"sys_adm_settings\":true,\"sys_adm_monitoring\":true,\"sys_adm_modules\":true,\"sys_search_news\":true,\"sys_search_comments\":true,\"sys_news_like\":true,\"sys_adm_m_g_main\":true,\"sys_adm_m_g_news\":true,\"sys_adm_m_g_users\":true,\"sys_adm_m_g_menu\":true,\"sys_adm_m_g_settings\":true,\"sys_adm_m_i_news\":true,\"sys_adm_m_i_news_cats\":true,\"sys_adm_m_i_comments\":true,\"sys_adm_m_i_news_views\":true,\"sys_adm_m_i_news_votes\":true,\"sys_adm_m_i_users\":true,\"sys_adm_m_i_groups\":true,\"sys_adm_m_i_permissions\":true,\"sys_adm_m_i_menu\":true,\"sys_adm_m_i_menu_adm\":true,\"sys_adm_m_i_menu_groups_adm\":true,\"sys_adm_m_i_icons\":true,\"sys_adm_m_i_statics\":true,\"sys_adm_m_i_settings\":true,\"sys_adm_m_i_monitor\":true,\"sys_adm_m_i_info\":true}')");

			if(!$sql8){ $this->core->notify('Ошибка!', 'Произошла ошибка добавления групп пользователей', 2, 'install/?mode=step_3'); }

			$sql9 = $this->db->query("UPDATE `mcr_groups` SET id='0' WHERE id='4'");

			if(!$sql9){ $this->core->notify('Ошибка!', 'Произошла ошибка обновления групп пользователей', 2, 'install/?mode=step_3'); }

			$sql10 = $this->db->query("ALTER TABLE `mcr_groups` AUTO_INCREMENT=0");

			if(!$sql10){ $this->core->notify('Ошибка!', 'Произошла ошибка обновления групп пользователей', 2, 'install/?mode=step_3'); }

			$this->config->main['crypt'] = $method;

			if(!$this->core->savecfg($this->config->main, 'main.php', 'main')){
				$this->core->notify('Ошибка!', 'Настройки не могут быть сохранены', 2, 'install/?mode=step_3');
			}


			$_SESSION['step_3'] = true;

			file_get_contents("http://api.webmcr.com/?do=install&domain=".$_SERVER['SERVER_NAME']);

			$this->core->notify('Завершение установки', 'Настройки', 4, 'install/?mode=settings');

		}

		return $this->core->sp(MCR_ROOT."install/theme/step_3.html");
	}

}

?>