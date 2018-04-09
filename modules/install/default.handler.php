<?php
self::deleteSession("currentUser");

$dbConfig = $this->getConfig("dbConfig");
$link = mysql_connect($dbConfig["server"], $dbConfig["user"], $dbConfig["password"]);
if(!isset($this->post["install-submit"]))
{
    if($link && mysql_select_db($dbConfig["name"]))
    {
        $dbConnectionInfo = array("status" => true, "info" => 'Your database configuration is correct.');
    }
    else
        $dbConnectionInfo = array("status" => false, "info" => 'Your database configuration is incorrect');
    $this->setData("dbConnectionInfo", $dbConnectionInfo);
}
else
{
    $installConfig = $this->getConfig("install");
    $this->setData(array(
        "login" => $installConfig["admin-login"],
        "password" => $installConfig["admin-password"],
    ));

    mysql_select_db($dbConfig["name"]);
    mysql_query("
SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
    ");
    mysql_query("
DROP TABLE IF EXISTS `settings`;
    ");
    mysql_query("
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ");
    mysql_query("
DROP TABLE IF EXISTS `users`;
    ");
    mysql_query("
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(1023) NOT NULL,
  `capabilities` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ");
    $now = date("Y-m-d H:i:s");
    $login = addslashes($installConfig["admin-login"]);
    $password = addslashes(md5($installConfig["admin-password"]));
    $email = addslashes($installConfig["admin-email"]);
    $capabilities = addslashes(serialize($installConfig["admin-capabilities"]));
    mysql_query("
INSERT INTO `users` (
    `login`, `password`, `email`, `capabilities`, `created`, `modified`
)
VALUES(
    '$login', '$password', '$email', '$capabilities', '$now', '$now'
    );
    ");
    mysql_query("
DROP TABLE IF EXISTS `user_metadata`;
    ");
    mysql_query("
CREATE TABLE IF NOT EXISTS `user_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ");
}
mysql_close($link);
