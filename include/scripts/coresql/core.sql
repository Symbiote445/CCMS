CREATE TABLE IF NOT EXISTS `bans` (
  `bID` int(11) NOT NULL AUTO_INCREMENT,
  `user` text NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY (`bID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `comments` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `body` text NOT NULL,
  `user` int(11) NOT NULL,
  `module` text NOT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `convo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_message` int(11) NOT NULL,
  `sent_by` int(11) NOT NULL,
  `sent_to` text NOT NULL,
  `title` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `err` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `errno` int(11) NOT NULL,
  `errstr` text NOT NULL,
  `errfile` text NOT NULL,
  `errline` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `loggedin` (
  `loggedInID` int(11) NOT NULL AUTO_INCREMENT,
  `hash` text NOT NULL,
  `ip` text NOT NULL,
  `uid` int(11) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`loggedInID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `messages` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `convo` int(11) NOT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `notifications` (
  `nid` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `description` text NOT NULL,
  `link` text NOT NULL,
  `read` int(11) NOT NULL,
  PRIMARY KEY (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `settings` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `modifiers` text NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `email` text NOT NULL,
  `group` int(11) NOT NULL,
  `gPerm` text NOT NULL,
  `sig` text NOT NULL,
  `hash` text NOT NULL,
  `password` text NOT NULL,
  `activated` int(11) NOT NULL DEFAULT '0',
  `passwordReset` int(11) NOT NULL,
  `adminlevel` int(11) NOT NULL DEFAULT '0',
  `picture` text NOT NULL,
  `ip` varchar(16) NOT NULL,
  `active` int(11) NOT NULL,
  `rep` varchar(128) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `views` (
  `vcid` int(11) NOT NULL AUTO_INCREMENT,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`vcid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
