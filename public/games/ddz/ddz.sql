-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2022-10-29 16:56:05
-- 服务器版本： 5.6.50-log
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ddz`
--

-- --------------------------------------------------------

--
-- 表的结构 `player`
--

CREATE TABLE IF NOT EXISTS `player` (
  `id` int(11) NOT NULL,
  `name` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `duanwei` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `chengwei` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `lihui` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `bglihui` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `touxiang` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `jinbi` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `hunyu` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `card` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `over_card` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `weight` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `identity` varchar(999) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `player`
--

INSERT INTO `player` (`id`, `name`, `duanwei`, `chengwei`, `lihui`, `bglihui`, `touxiang`, `jinbi`, `hunyu`, `mail`, `password`, `card`, `over_card`, `weight`, `identity`) VALUES
(1, '陈盔盔', 'img/ui/sima_quesheng.png', 'img/ui/zuichuquesheng.png', 'img/banshen/waitingroom15424.png', 'img/lihui/full15418.png', 'img/touxiang/bighead15419.png', '999999', '1002499', '2106786285@qq.com', '123', '["1","12","13","18","28","33","36","37","41","43","44"]', '[10,0,35]', '0', '农民'),
(2, 'SLY', 'img/ui/sima_fish.png', 'img/ui/wanxianggengxin19554.png', 'img/banshen/waitingroom15715.png', 'img/lihui/full15703.png', 'img/touxiang/bighead15718.png', '999999', '65000', '123@qq.com', '123', '[]', '[10,0,35]', '0', '地主'),
(3, '维维', 'img/ui/sima_fish.png', 'img/ui/wanxianggengxin19554.png', 'img/banshen/waitingroom16452.png', 'img/lihui/full16020.png', 'img/touxiang/bighead16019.png', '999999', '-2500', '456@qq.com', '123', '["15","17","31","47","48"]', '[10,0,35]', '0', '农民');

-- --------------------------------------------------------

--
-- 表的结构 `room`
--

CREATE TABLE IF NOT EXISTS `room` (
  `id` int(99) NOT NULL,
  `player` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `prepare` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[1,0,0]',
  `type` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT '斗地主',
  `reward` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT '用辉玉结算',
  `begin` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'off',
  `round` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `discard` varchar(999) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discard_player` varchar(999) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discard_type` varchar(999) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discard_weight` varchar(999) COLLATE utf8_unicode_ci DEFAULT NULL,
  `noplaynum` varchar(999) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=1667029721 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `room`
--

INSERT INTO `room` (`id`, `player`, `prepare`, `type`, `reward`, `begin`, `round`, `discard`, `discard_player`, `discard_type`, `discard_weight`, `noplaynum`) VALUES
(1667029720, '[2,"3","1"]', '[1,0,0]', '斗地主', '用辉玉结算', 'off', '0', '["4"]', '2', '1', '15', '2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `player`
--
ALTER TABLE `player`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(99) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1667029721;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
