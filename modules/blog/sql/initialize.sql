CREATE TABLE IF NOT EXISTS `archive` (
  `aID` int(11) NOT NULL AUTO_INCREMENT,
  `arch` text NOT NULL,
  PRIMARY KEY (`aID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `display` int(11) NOT NULL DEFAULT '1',
  `user` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `arch` text NOT NULL,
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
