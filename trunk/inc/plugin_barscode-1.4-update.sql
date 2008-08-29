CREATE TABLE IF NOT EXISTS `glpi_plugin_barscode_preference` (
  `ID` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `page_format` varchar(2) NOT NULL default 'A4',
  `pdf_type` varchar(1) NOT NULL default 'L',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;