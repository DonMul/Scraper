CREATE TABLE IF NOT EXISTS `backlog` (
  `link` varchar(1024) NOT NULL,
  `isLocked` tinyint(1) NOT NULL,
  `uniqueHash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `link` (
  `fromPageId` bigint(20) NOT NULL,
  `toPageId` bigint(20) NOT NULL,
  `url` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL,
  `raw` text NOT NULL,
  `isInternal` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `page` (
  `siteId` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `site` (
  `id` int(255) NOT NULL,
  `url` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `backlog`
  ADD UNIQUE KEY `uniqueHash` (`uniqueHash`);

ALTER TABLE `page`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siteId_2` (`siteId`,`url`),
  ADD KEY `siteId` (`siteId`);

ALTER TABLE `site`
  ADD PRIMARY KEY (`id`),
  ADD KEY `url` (`url`(767));

ALTER TABLE `page`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115160;

ALTER TABLE `site`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1050;