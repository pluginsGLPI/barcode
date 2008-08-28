DROP TABLE IF EXISTS `glpi_plugin_barscode_config`;
CREATE TABLE `glpi_plugin_barscode_config` (
	`ID` int(11) NOT NULL auto_increment,
	`margeL` int(11) NOT NULL default '0',
	`margeT` varchar(255) NOT NULL default '',
	`margeH` varchar(255) NOT NULL default '',
	`margeW` varchar(255) NOT NULL default '',
	`etiquetteW` varchar(255) NOT NULL default '',
	`etiquetteH` varchar(255) NOT NULL default '',
	`etiquetteR` varchar(255) NOT NULL default '',
	`etiquetteC` varchar(255) NOT NULL default '',
	`etiquetteRL` varchar(255) NOT NULL default '',
	`etiquetteCL` varchar(255) NOT NULL default '',    
	`print_field` varchar(255) NOT NULL default 'otherserial',    
	PRIMARY KEY  (`ID`)
) TYPE=MyISAM;
	
INSERT INTO `glpi_plugin_barscode_config` ( `ID` , `margeL` , `margeT` , `margeH` , `margeW`, `etiquetteW`, `etiquetteH`, `etiquetteR`, `etiquetteC`, `etiquetteRL`, `etiquetteCL` )VALUES ('1', '2', '8.6', '4', '0', '66.5', '35.9', '3', '8','4','5' );

DROP TABLE IF EXISTS `glpi_plugin_barscode_profiles`;
CREATE TABLE `glpi_plugin_barscode_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) default NULL,
	`interface` varchar(50) NOT NULL default 'barscode',
	`is_default` smallint(6) NOT NULL default '0',
	`barscode` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `glpi_plugin_barscode_profiles` (
  `ID` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `page_format` varchar(2) NOT NULL default 'A4',
  `pdf_type` varchar(1) NOT NULL default 'L',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;