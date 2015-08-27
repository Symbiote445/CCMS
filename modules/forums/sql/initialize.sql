CREATE TABLE IF NOT EXISTS `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `home` varchar(5) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  `title` varchar(50) NOT NULL,
  `tag` text NOT NULL,
  `post` mediumtext,
  `category` text NOT NULL,
  `postlink` text NOT NULL,
  `reported` varchar(1) NOT NULL DEFAULT '0',
  `locked` varchar(1) NOT NULL,
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `reply` (
  `reply_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` text NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `reply` text NOT NULL,
  `hidden` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`reply_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `desc` text NOT NULL,
  `cg` int(11) NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `category_groups` (
  `cg_id` int(11) NOT NULL AUTO_INCREMENT,
  `cg_name` text NOT NULL,
  `perm` text NOT NULL,
  PRIMARY KEY (`cg_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `polls` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `post` text NOT NULL,
  `date` datetime NOT NULL,
  `choices` text NOT NULL,
  `postlink` text NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `votes` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `choice` text NOT NULL,
  `user` int(11) NOT NULL,
  `poll` int(11) NOT NULL,
  PRIMARY KEY (`vid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `fconf` (
  `fsID` int(11) NOT NULL AUTO_INCREMENT,
  `homeDisp` text NOT NULL,
  `homeNum` int(11) NOT NULL,
  PRIMARY KEY (`fsID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `fconf` (`fsID`, `homeDisp`, `homeNum`) VALUES
(1, 'posts', 5);
