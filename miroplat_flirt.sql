-- phpMyAdmin SQL Dump
-- version 
-- http://www.phpmyadmin.net
--
-- Хост: miroplat.mysql
-- Время создания: Фев 03 2018 г., 15:35
-- Версия сервера: 5.6.25-73.1
-- Версия PHP: 5.6.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `miroplat_flirt`
--

-- --------------------------------------------------------

--
-- Структура таблицы `checked_users`
--
-- Создание: Янв 24 2018 г., 15:03
--

DROP TABLE IF EXISTS `checked_users`;
CREATE TABLE IF NOT EXISTS `checked_users` (
  `place_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `dialogs`
--
-- Создание: Янв 24 2018 г., 15:10
--

DROP TABLE IF EXISTS `dialogs`;
CREATE TABLE IF NOT EXISTS `dialogs` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `receiver_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `likes`
--
-- Создание: Янв 24 2018 г., 15:07
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `receiver_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `like` varchar(8) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--
-- Создание: Янв 24 2018 г., 15:13
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `receiver_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `message` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `places`
--
-- Создание: Янв 24 2018 г., 17:09
--

DROP TABLE IF EXISTS `places`;
CREATE TABLE IF NOT EXISTS `places` (
  `id` int(11) NOT NULL,
  `lat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `lon` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(264) COLLATE utf8_unicode_ci NOT NULL,
  `count_check_in` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `places`
--

INSERT INTO `places` (`id`, `lat`, `lon`, `name`, `address`, `picture`, `count_check_in`) VALUES
(1, '54.7140948', '20.4346876', 'FishBone Grill&Bar', 'Ленинский пр., 16', 'https://i6.photo.2gis.com/images/branch/40/5629499546983496_ab4c.jpg', ''),
(2, '54.7207672', '20.3856571', 'Геркулес', 'просп. Мира, 105', 'https://kgd.kassir.ru/media/venue/7014b20a692f856d6b814379fb4dc070.jpg', '');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--
-- Создание: Янв 24 2018 г., 16:49
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `api_token` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `firebase_instance_id` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `vk_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `facebook_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `birthday` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `about` text COLLATE utf8_unicode_ci NOT NULL,
  `search_man` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `search_woman` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `search_max_age` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `notifications_likes` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `notifications_messages` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `api_token`, `firebase_instance_id`, `vk_id`, `facebook_id`, `name`, `birthday`, `sex`, `picture`, `about`, `search_man`, `search_woman`, `search_max_age`, `notifications_likes`, `notifications_messages`, `timestamp`) VALUES
(1, '0ee39aa65e7f107d15e6291041119933', 'dY_flSX1jUU:APA91bGRuSnJUjqhoHzzQHmWdT7hOOfYK4p1NFd_3YOQKvJanoJ85sViR2j7QnAltGN8YzV3sdqHqPStoZR_36nU2jIA8o47Gn2JT_hwX1Zh_3upYC8XOhdOyq-EXbA_Gj51W_7M2QwB', '19334532', '', 'Ivan  Utochkin', '', '2', 'https://pp.userapi.com/c824500/v824500225/18f0f/FkD8k_VhKow.jpg', '', '0', '1', '99', '', '', '2018-01-24 16:49:45'),
(2, '2e85aad28307efdb77932b43903613c2', 'dY_flSX1jUU:APA91bGRuSnJUjqhoHzzQHmWdT7hOOfYK4p1NFd_3YOQKvJanoJ85sViR2j7QnAltGN8YzV3sdqHqPStoZR_36nU2jIA8o47Gn2JT_hwX1Zh_3upYC8XOhdOyq-EXbA_Gj51W_7M2QwB', '', '1736919036324254', 'Р?РІР°РЅ РЈС‚РѕС‡РєРёРЅ', '', '2', 'https://scontent.xx.fbcdn.net/v/t31.0-1/c180.120.720.480/p720x720/1523318_814239101925590_1167900206_o.jpg?oh=00b9e3ba2c474f94bbb31fe20403abc4&oe=5AE9FF42', '', '0', '1', '99', '', '', '2018-01-24 16:49:45'),
(3, 'e2ff183662a776f10c984a37912b1777', 'dpFE80VPOXM:APA91bFurgEsJ0WWmpaQ4tDNra5PYcdiLD8G_1cpSZoiob7no-JO2XbM4eMiQRumoF1f-1HiaUvFmYvoE330v4huh2v-rlVKFrKMhypHJlvd_OeS4bl4PTC6vLhK4RgI9v_S__jMJwWP', '291909461', '', 'Р’Р°С€Р°  РҐР»РѕРїСѓС€РєР°', '', '1', 'https://pp.userapi.com/c627723/v627723461/2cb4/coatpBr95ns.jpg', 'СЏ СЃР°РјС‹Р№ Р»СѓС‡С€РёР№', '1', '0', '99', '', '', '2018-01-24 18:12:41');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `checked_users`
--
ALTER TABLE `checked_users`
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Индексы таблицы `dialogs`
--
ALTER TABLE `dialogs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `dialogs`
--
ALTER TABLE `dialogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
