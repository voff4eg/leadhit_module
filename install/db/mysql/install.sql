CREATE TABLE IF NOT EXISTS `leadhit_request` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `PARAMS` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `TYPE` char(4) DEFAULT NULL,
  `URL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DATE_INSERT` datetime,
  `DATE_EXEC` datetime,
  `DATE_LAST_TRY` datetime,
  `SUCCESS_EXEC` char(1) not null default 'N',
  PRIMARY KEY (`ID`),
  INDEX ix_success (`SUCCESS_EXEC`),
  INDEX ix_b_event_date_exec (`DATE_EXEC`)
);

CREATE TABLE IF NOT EXISTS `leadhit_basket_user_hash` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FUSER_ID` int(18) NOT NULL,
  `HASH` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
);