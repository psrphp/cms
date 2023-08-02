<?php

use PsrPHP\Framework\Script;

return [
    'install' => function () {
        $sql = <<<'str'
DROP TABLE IF EXISTS `prefix_psrphp_cms_model`;
CREATE TABLE `prefix_psrphp_cms_model` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(255) NOT NULL COMMENT '名称',
    `tpl_category` varchar(255) NOT NULL DEFAULT '' COMMENT '栏目默认模板',
    `tpl_content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容默认模板',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='内容模型表';
DROP TABLE IF EXISTS `prefix_psrphp_cms_field`;
CREATE TABLE `prefix_psrphp_cms_field` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `model_id` int(10) unsigned NOT NULL COMMENT '模型ID',
    `is_system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '系统字段',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(255) NOT NULL COMMENT '字段',
    `type` varchar(255) NOT NULL COMMENT '类型',
    `editable` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许通过表单编辑',
    `listable` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许后台列表显示',
    `searchable` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许被搜索',
    `sortable` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许排序',
    `filterable` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许筛选',
    `extra` text COMMENT '其他数据',
    `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='模型字段表';
DROP TABLE IF EXISTS `prefix_psrphp_cms_dict`;
CREATE TABLE `prefix_psrphp_cms_dict` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='数据源表';
DROP TABLE IF EXISTS `prefix_psrphp_cms_data`;
CREATE TABLE `prefix_psrphp_cms_data` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `dict_id` int(10) unsigned NOT NULL COMMENT '数据源ID',
    `pid` int(10) unsigned NOT NULL COMMENT '上级ID',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `value` varchar(255) NOT NULL COMMENT '值',
    `sn` int(10) unsigned NOT NULL COMMENT '',
    `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='数据源数据表';
str;
        Script::execSql($sql);
    },
    'unInstall' => function () {
        $sql = <<<'str'
DROP TABLE IF EXISTS `prefix_psrphp_cms_model`;
DROP TABLE IF EXISTS `prefix_psrphp_cms_field`;
DROP TABLE IF EXISTS `prefix_psrphp_cms_dict`;
DROP TABLE IF EXISTS `prefix_psrphp_cms_data`;
str;
        Script::execSql($sql);
    },
    'update' => function () {
    },
];
