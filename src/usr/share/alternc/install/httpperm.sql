
CREATE TABLE IF NOT EXISTS `http_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `srcip` varchar(64) NOT NULL,
  `dstip` varchar(64) NOT NULL,
  `dstport` int(10) unsigned NOT NULL,
  `dns` varchar(255) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `gid` int(10) unsigned NOT NULL,
  `permid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `permid` (`permid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `http_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `kind` tinyint(3) unsigned NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `udate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` int(10) unsigned NOT NULL,
  `ttl` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `udate` (`udate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Permission to do OUTGOING http or https connections';

CREATE TABLE IF NOT EXISTS `http_permission_bloc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permid` int(10) unsigned NOT NULL,
  `bloc` varchar(64) NOT NULL,
  `kind` tinyint(3) unsigned NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permid_2` (`permid`,`bloc`),
  KEY `cdate` (`cdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='List of IPv4/IPv6 blocs of permitted HTTP/HTTPS outgoing connections';


