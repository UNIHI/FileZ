SET NAMES 'utf8';
CREATE TABLE IF NOT EXISTS `fz_file` (
  `id`              BIGINT UNSIGNED NOT NULL,
  `del_notif_sent`  BOOLEAN         DEFAULT 0,
  `file_name`       varchar(100)    NOT NULL,
  `file_size`       INTEGER         DEFAULT 0,
  `available_from`  DATE            NOT NULL,
  `available_until` DATE            NOT NULL,
  `comment`         varchar(200),
  `download_count`  INTEGER         DEFAULT 0,
  `notify_uploader` BOOLEAN         DEFAULT 0,
  `created_by`      BIGINT          NOT NULL,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `extends_count`   INTEGER         DEFAULT '0',
  `password`        varchar(40)     DEFAULT NULL,
  `require_login`   BOOLEAN         DEFAULT 0,
  `downloadLimit`   INTEGER         DEFAULT NULL,
  `intervalCount`   INTEGER         DEFAULT NULL,
  `intervalType`    varchar(15)     DEFAULT NULL,
  `reported`        BOOLEAN         DEFAULT NULL,
  `prevent_reporting` BOOLEAN       DEFAULT NULL,
  `folder`          varchar(200)    DEFAULT NULL,
  
  INDEX       (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES fz_user(`id`),
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `fz_fileLog` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id`         BIGINT UNSIGNED NOT NULL,
  `ip`              varchar(15)     NOT NULL,
  `username`        varchar(150),
  `timestamp`       INTEGER         NOT NULL,
  
  PRIMARY KEY (  `id`  ),
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE  `fz_info` (
 `key`   VARCHAR( 30 ) NOT NULL ,
 `value` VARCHAR( 50 ) NOT NULL ,
  PRIMARY KEY (  `key`  )
) DEFAULT CHARSET=utf8;

CREATE TABLE `fz_user` (
  `id`          SERIAL      NOT NULL,
  `username`    VARCHAR(30) NOT NULL,
  `password`    VARCHAR(40) NOT NULL,
  `salt`        VARCHAR(40),
  `firstname`   VARCHAR(50) NOT NULL,
  `lastname`    VARCHAR(50) NOT NULL,
  `email`       VARCHAR(50) NOT NULL,
  `is_admin`    BOOLEAN     DEFAULT 0,
  `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = MYISAM ;

INSERT INTO `fz_info` (`key`, `value`) VALUES ('db_version', '2.2.0-1');
