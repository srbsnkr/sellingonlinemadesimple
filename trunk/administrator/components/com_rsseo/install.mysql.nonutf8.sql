CREATE TABLE IF NOT EXISTS `#__rsseo_competitors` (
  `IdCompetitor` int(11) NOT NULL AUTO_INCREMENT,
  `Competitor` varchar(255) NOT NULL,
  `LastPageRank` int(11) NOT NULL DEFAULT '-1',
  `LastAlexaRank` int(11) NOT NULL DEFAULT '-1',
  `LastTehnoratiRank` int(11) NOT NULL DEFAULT '-1',
  `LastGooglePages` int(11) NOT NULL DEFAULT '-1',
  `LastYahooPages` int(11) NOT NULL DEFAULT '-1',
  `LastBingPages` int(11) NOT NULL DEFAULT '-1',
  `LastGoogleBacklinks` int(11) NOT NULL DEFAULT '-1',
  `LastYahooBacklinks` int(11) NOT NULL DEFAULT '-1',
  `LastBingBacklinks` int(11) NOT NULL DEFAULT '-1',
  `Dmoz` int(1) NOT NULL DEFAULT '-1',
  `LastDateRefreshed` int(11) NOT NULL DEFAULT '-1',
  `Tags` text NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`IdCompetitor`),
  UNIQUE KEY `Competitor` (`Competitor`)
);


CREATE TABLE IF NOT EXISTS `#__rsseo_competitors_history` (
  `IdCompetitorHistory` int(11) NOT NULL AUTO_INCREMENT,
  `IdCompetitor` int(11) NOT NULL,
  `PageRank` int(11) NOT NULL,
  `AlexaRank` int(11) NOT NULL,
  `TehnoratiRank` int(11) NOT NULL,
  `GooglePages` int(11) NOT NULL,
  `YahooPages` int(11) NOT NULL,
  `BingPages` int(11) NOT NULL,
  `GoogleBacklinks` int(11) NOT NULL,
  `YahooBacklinks` int(11) NOT NULL,
  `BingBacklinks` int(11) NOT NULL,
  `DateRefreshed` int(11) NOT NULL,
  PRIMARY KEY (`IdCompetitorHistory`)
);


CREATE TABLE IF NOT EXISTS `#__rsseo_config` (
  `IdConfig` int(11) NOT NULL AUTO_INCREMENT,
  `ConfigName` varchar(255) NOT NULL,
  `ConfigValue` text NOT NULL,
  PRIMARY KEY (`IdConfig`)
);

CREATE TABLE IF NOT EXISTS `#__rsseo_keywords` (
  `IdKeyword` int(11) NOT NULL AUTO_INCREMENT,
  `Keyword` varchar(255) NOT NULL,
  `KeywordImportance` enum('low','relevant','important','critical') NOT NULL,
  `ActualKeywordPosition` int(11) NOT NULL,
  `LastKeywordPosition` int(11) NOT NULL,
  `DateRefreshed` int(11) NOT NULL,
  `KeywordBold` int(2) NOT NULL,
  `KeywordUnderline` int(2) NOT NULL,
  `KeywordLimit` int(3) NOT NULL,
  `KeywordAttributes` text NOT NULL,
  `KeywordLink` varchar(255) NOT NULL,
  PRIMARY KEY (`IdKeyword`),
  UNIQUE KEY `Keyword` (`Keyword`)
);



CREATE TABLE IF NOT EXISTS `#__rsseo_pages` (
  `IdPage` int(11) NOT NULL AUTO_INCREMENT,
  `PageURL` varchar(255) NOT NULL,
  `PageTitle` text NOT NULL,
  `PageKeywords` text NOT NULL,
  `PageKeywordsDensity` text NOT NULL,
  `PageDescription` text NOT NULL,
  `PageSitemap` tinyint(1) NOT NULL,
  `PageInSitemap` int(2) NOT NULL,
  `PageCrawled` tinyint(1) NOT NULL,
  `DatePageCrawled` int(11) NOT NULL,
  `PageModified` int(3) NOT NULL,
  `PageLevel` tinyint(4) NOT NULL,
  `PageGrade` float(10,2) NOT NULL DEFAULT '-1.00',
  `params` text NOT NULL,
  `densityparams` text NOT NULL,
  `published` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`IdPage`),
  UNIQUE KEY `PageURL` (`PageURL`)
);

CREATE TABLE IF NOT EXISTS `#__rsseo_redirects` (
  `IdRedirect` int(11) NOT NULL AUTO_INCREMENT,
  `RedirectFrom` varchar(255) NOT NULL,
  `RedirectTo` varchar(255) NOT NULL,
  `RedirectType` enum('301','302') NOT NULL,
  `published` int(2) NOT NULL,
  PRIMARY KEY (`IdRedirect`)
);


INSERT IGNORE INTO `#__rsseo_pages` (`IdPage`, `PageURL`, `PageTitle`, `PageKeywords`, `PageDescription`, `PageSitemap`, `PageCrawled`, `DatePageCrawled`, `PageLevel`, `PageGrade`, `params`, `published`) VALUES(1, '', '', '', '', 0, 0, 0, 0, 0.00, '', 1);

INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(1, 'global.register.code', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(2, 'global.dateformat', 'd M y H:i');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(3, 'crawler.level', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(4, 'analytics.enable', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(5, 'analytics.username', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(6, 'analytics.password', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(7, 'crawler.title.duplicate', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(8, 'crawler.title.length', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(9, 'crawler.description.duplicate', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(10, 'crawler.description.length', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(11, 'crawler.keywords', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(12, 'crawler.headings', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(13, 'crawler.images', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(14, 'crawler.images.alt', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(15, 'crawler.images.hw', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(16, 'crawler.sef', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(17, 'enable.debug', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(18, 'enable.pr', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(19, 'enable.alexa', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(20, 'enable.googlep', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(21, 'enable.yahoop', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(22, 'enable.bingp', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(23, 'enable.googleb', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(24, 'enable.yahoob', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(25, 'enable.bingb', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(26, 'crawler.ignore', '{*}tmpl=component{*}\r\n{*}format=pdf{*}\r\n{*}format=feed{*}\r\n{*}output=pdf{*}');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(32, 'approved.chars', ' ,;:.?!$%*&()[]{}');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(27, 'crawler.enable.auto', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(31, 'enable.keyword.replace', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(28, 'google.domain', 'google.com');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(29, 'component.heading', 'h1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(30, 'content.heading', 'h1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(33, 'enable.tehnorati', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(34, 'subdomains', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(35, 'site.name.in.title', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(36, 'site.name.position', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(37, 'site.name.separator', '|');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(39, 'search.dmoz', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(40, 'php.folder', 'php');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(41, 'crawler.intext.links', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(42, 'proxy.server', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(43, 'proxy.port', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(44, 'proxy.username', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(45, 'proxy.password', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(46, 'proxy.enable', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(47, 'sitemap_menus', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(48, 'sitemap_excludes', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(49, 'keyword.density.enable', '1');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(50, 'ga.account', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(51, 'ga.start', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(52, 'ga.end', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(53, 'ga.token', '');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(54, 'ga.tracking', '0');
INSERT IGNORE INTO `#__rsseo_config` (`IdConfig`, `ConfigName`, `ConfigValue`) VALUES(55, 'ga.code', '');