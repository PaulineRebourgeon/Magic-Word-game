-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Dim 29 Novembre 2015 à 15:45
-- Version du serveur: 5.5.43
-- Version de PHP: 5.5.29-1~dotdeb+7.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `magicword`
--

-- --------------------------------------------------------

-- Les structures de dictionnaires sont dans les fichiers langue…

--
-- Structure de la table `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `gameid` int(11) NOT NULL AUTO_INCREMENT,
  `gamelang` varchar(255) NOT NULL DEFAULT '',
  `gametype` tinyint(3) NOT NULL DEFAULT '0',
  `gamefinished` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gameid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `gamesstatus`
--

CREATE TABLE IF NOT EXISTS `gamesstatus` (
  `gameid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `gridid` int(11) NOT NULL DEFAULT '0',
  `gridstatus` tinyint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gameid`,`userid`,`gridid`),
  UNIQUE KEY `gameid` (`gameid`,`gridid`,`gridstatus`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `gamesusers`
--

CREATE TABLE IF NOT EXISTS `gamesusers` (
  `gameid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `request` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gameid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `gamesuserswords`
--

CREATE TABLE IF NOT EXISTS `gamesuserswords` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `gameid` int(11) NOT NULL DEFAULT '0',
  `gridid` int(11) NOT NULL DEFAULT '0',
  `word` varchar(255) NOT NULL DEFAULT '',
  `wordpoints` int(11) NOT NULL DEFAULT '0',
  `wordexists` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `grids`
--

CREATE TABLE IF NOT EXISTS `grids` (
  `gridid` int(11) NOT NULL AUTO_INCREMENT,
  `gridletters` varchar(16) NOT NULL DEFAULT '',
  `gridwordscount` int(11) NOT NULL DEFAULT '0',
  `gridtype` tinyint(3) NOT NULL DEFAULT '0',
  `gridconstraint` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gridid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `gridswords`
--

CREATE TABLE IF NOT EXISTS `gridswords` (
  `gridid` int(11) NOT NULL DEFAULT '0',
  `gridword` varchar(255) NOT NULL DEFAULT '',
  `gridpoints` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gridid`,`gridword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `invitations`
--

CREATE TABLE IF NOT EXISTS `invitations` (
  `invitid` int(11) NOT NULL AUTO_INCREMENT,
  `fromuserid` int(11) NOT NULL DEFAULT '0',
  `touserid` int(11) NOT NULL DEFAULT '0',
  `invittime` int(11) NOT NULL DEFAULT '0',
  `gameid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`invitid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `useremail` varchar(255) NOT NULL DEFAULT '',
  `userpasswd` varchar(255) NOT NULL DEFAULT '',
  `useronline` int(11) NOT NULL DEFAULT '0',
  `userlang` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `wordbox`
--

CREATE TABLE IF NOT EXISTS `wordbox` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `wordboxword` varchar(255) NOT NULL DEFAULT '0',
  `wordboxstatus` tinyint(3) NOT NULL DEFAULT '0',
  `wordboxlang` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `wordofday`
--

CREATE TABLE IF NOT EXISTS `wordofday` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `wordofdaydate` int(11) NOT NULL DEFAULT '0',
  `wordofdayword` varchar(255) NOT NULL DEFAULT '',
  `wordofdaylang` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
