<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Psrphp;

use PsrPHP\Framework\Script as FrameworkScript;

class Script
{
    public static function onInstall()
    {
        $sql = <<<'str'
DROP TABLE IF EXISTS `prefix_psrphp_cms_dict`;
CREATE TABLE `prefix_psrphp_cms_dict` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(255) NOT NULL COMMENT '标题',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='数据源表';
DROP TABLE IF EXISTS `prefix_psrphp_cms_data`;
CREATE TABLE `prefix_psrphp_cms_data` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `dict_id` int(10) unsigned NOT NULL COMMENT '数据源ID',
    `value` int(10) unsigned NOT NULL COMMENT '',
    `parent` int(10) unsigned COMMENT '上级',
    `title` varchar(255) NOT NULL COMMENT '标题',
    `alias` varchar(255) COMMENT '别名',
    `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='数据源数据表';
DROP TABLE IF EXISTS `prefix_psrphp_cms_model`;
CREATE TABLE `prefix_psrphp_cms_model` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(255) NOT NULL COMMENT '标题',
    `name` varchar(255) NOT NULL COMMENT '名称',
    `type` varchar(255) COMMENT '类型',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='内容模型表';
DROP TABLE IF EXISTS `prefix_psrphp_cms_field`;
CREATE TABLE `prefix_psrphp_cms_field` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `model_id` int(10) unsigned NOT NULL COMMENT '模型ID',
    `system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '系统字段',
    `group` varchar(255) NOT NULL COMMENT '分组',
    `title` varchar(255) NOT NULL COMMENT '标题',
    `name` varchar(255) NOT NULL COMMENT '字段',
    `type` varchar(255) NOT NULL COMMENT '类型',
    `show` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许后台列表显示',
    `tpl` text COMMENT '后台列表显示模板',
    `extra` text COMMENT '其他数据',
    `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='模型字段表';
str;
        FrameworkScript::execSql($sql);
    }

    public static function onUnInstall()
    {
        $sql = <<<'str'
DROP TABLE IF EXISTS `prefix_psrphp_cms_dict`;
DROP TABLE IF EXISTS `prefix_psrphp_cms_data`;
DROP TABLE IF EXISTS `prefix_psrphp_cms_model`;
DROP TABLE IF EXISTS `prefix_psrphp_cms_field`;
str;
        FrameworkScript::execSql($sql);
    }
}
