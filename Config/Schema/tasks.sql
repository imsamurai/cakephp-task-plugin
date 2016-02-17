delimiter $$

CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `process_id` bigint(20) unsigned DEFAULT '0',
  `server_id` int(10) DEFAULT '0',
  `command` varchar(333) DEFAULT NULL,
  `path` varchar(500) DEFAULT NULL,
  `arguments` longtext,
  `hash` varchar(100) DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '0 - new\n1 - deffered\n2 - runned\n3 - finished',
  `code` int(10) DEFAULT '0' COMMENT '0 - ok\nother - error code',
  `code_string` varchar(500) DEFAULT NULL,
  `stdout` longtext,
  `stderr` longtext,
  `details` longtext,
  `timeout` int(10) unsigned DEFAULT '0',
  `scheduled` datetime DEFAULT NULL,
  `started` datetime DEFAULT NULL,
  `stopped` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `created` (`created`),
  KEY `status-command` (`status`,`command`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8$$

CREATE TABLE `dependent_tasks` (
  `task_id` bigint(20) unsigned NOT NULL,
  `depends_on_task_id` bigint(20) unsigned NOT NULL,
  KEY `task_id_depends_on_task_id` (`task_id`,`depends_on_task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8$$

CREATE TABLE IF NOT EXISTS `task_statistics` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` BIGINT(20) UNSIGNED NOT NULL,
  `memory` DECIMAL(4,1) NULL DEFAULT 0.0,
  `cpu` DECIMAL(4,1) NULL DEFAULT 0.0,
  `status` VARCHAR(10) NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `task_id` (`task_id` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8$$
